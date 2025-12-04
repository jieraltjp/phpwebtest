# 万方商事 B2B 采购门户 Windows 部署脚本
# PowerShell 自动化部署和回滚机制

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("staging", "production")]
    [string]$Environment,
    
    [switch]$Rollback,
    [string]$BackupName = "latest"
)

# ================================
# 配置变量
# ================================
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$BackupDir = "C:\backups\banho-b2b"
$LogFile = "C:\logs\banho-b2b-deploy.log"

# 确保日志目录存在
New-Item -ItemType Directory -Force -Path (Split-Path -Parent $LogFile) | Out-Null

# ================================
# 日志函数
# ================================
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $color = switch ($Level) {
        "ERROR" { "Red" }
        "WARNING" { "Yellow" }
        "SUCCESS" { "Green" }
        default { "White" }
    }
    
    Write-Host "[$timestamp] [$Level] $Message" -ForegroundColor $color
    Add-Content -Path $LogFile -Value "[$timestamp] [$Level] $Message"
}

function Write-Error-Log {
    param([string]$Message)
    Write-Log $Message "ERROR"
    throw $Message
}

function Write-Warning-Log {
    param([string]$Message)
    Write-Log $Message "WARNING"
}

function Write-Success-Log {
    param([string]$Message)
    Write-Log $Message "SUCCESS"
}

# ================================
# 检查前置条件
# ================================
function Test-Prerequisites {
    Write-Log "检查部署前置条件..."
    
    # 检查 Docker
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        Write-Error-Log "Docker 未安装或未在 PATH 中"
    }
    
    # 检查 Docker Compose
    if (-not (Get-Command docker-compose -ErrorAction SilentlyContinue)) {
        Write-Error-Log "Docker Compose 未安装或未在 PATH 中"
    }
    
    # 检查环境配置文件
    $envFile = "$ProjectRoot\.env.$Environment"
    if (-not (Test-Path $envFile)) {
        Write-Error-Log "环境配置文件不存在: $envFile"
    }
    
    # 检查磁盘空间
    $systemDrive = Get-WmiObject -Class Win32_LogicalDisk -Filter "DeviceID='C:'"
    $freeSpaceGB = [math]::Round($systemDrive.FreeSpace / 1GB, 2)
    if ($freeSpaceGB -lt 2) {
        Write-Warning-Log "C 盘可用空间不足 2GB，当前: $freeSpaceGB GB"
    }
    
    # 检查内存
    $os = Get-WmiObject -Class Win32_OperatingSystem
    $freeMemoryGB = [math]::Round($os.FreePhysicalMemory / 1MB, 2)
    if ($freeMemoryGB -lt 1) {
        Write-Warning-Log "可用内存不足 1GB，当前: $freeMemoryGB GB"
    }
    
    Write-Success-Log "前置条件检查完成"
}

# ================================
# 备份当前版本
# ================================
function Backup-CurrentVersion {
    Write-Log "备份当前版本..."
    
    $backupName = "banho-b2b-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    $backupPath = Join-Path $BackupDir $backupName
    
    New-Item -ItemType Directory -Force -Path $backupPath | Out-Null
    
    # 备份数据库
    $mysqlStatus = docker-compose ps mysql | Select-String "Up"
    if ($mysqlStatus) {
        Write-Log "备份数据库..."
        $env:MYSQL_ROOT_PASSWORD = (Get-Content "$ProjectRoot\.env.$Environment" | Where-Object { $_ -match "MYSQL_ROOT_PASSWORD=" }).Split("=")[1]
        $env:DB_DATABASE = (Get-Content "$ProjectRoot\.env.$Environment" | Where-Object { $_ -match "DB_DATABASE=" }).Split("=")[1]
        
        docker-compose exec -T mysql mysqldump -u root -p$env:MYSQL_ROOT_PASSWORD --single-transaction --routines --triggers $env:DB_DATABASE | Out-File -FilePath "$backupPath\database.sql" -Encoding UTF8
        
        Compress-Archive -Path "$backupPath\database.sql" -DestinationPath "$backupPath\database.sql.gz" -Force
        Remove-Item "$backupPath\database.sql"
        Write-Success-Log "数据库备份完成"
    }
    
    # 备份应用数据
    $storageAppPath = "$ProjectRoot\storage\app"
    if (Test-Path $storageAppPath) {
        Write-Log "备份应用数据..."
        Copy-Item -Path $storageAppPath -Destination "$backupPath\app" -Recurse -Force
        Write-Success-Log "应用数据备份完成"
    }
    
    # 备份配置文件
    $envFile = "$ProjectRoot\.env.$Environment"
    if (Test-Path $envFile) {
        Copy-Item $envFile "$backupPath\"
    }
    
    # 备份当前镜像信息
    $images = docker images banho/b2b-portal --format "table {{.Repository}}:{{.Tag}}`t{{.CreatedAt}}"
    if ($images) {
        $images | Out-File -FilePath "$backupPath\images.txt" -Encoding UTF8
    }
    
    # 创建符号链接指向最新备份
    $latestLink = Join-Path $BackupDir "latest"
    if (Test-Path $latestLink) {
        Remove-Item $latestLink -Force
    }
    New-Item -ItemType SymbolicLink -Path $latestLink -Target $backupName | Out-Null
    
    Write-Success-Log "备份完成: $backupPath"
}

# ================================
# 拉取最新代码
# ================================
function Pull-LatestCode {
    Write-Log "拉取最新代码..."
    
    Set-Location $ProjectRoot
    
    # 检查是否有未提交的更改
    $status = git status --porcelain
    if ($status) {
        Write-Warning-Log "检测到未提交的更改，将被覆盖"
    }
    
    # 拉取最新代码
    git fetch origin
    git reset --hard "origin/$Environment"
    
    Write-Success-Log "代码拉取完成"
}

# ================================
# 构建和部署
# ================================
function Build-And-Deploy {
    Write-Log "构建和部署应用..."
    
    Set-Location $ProjectRoot
    
    # 复制环境配置
    Copy-Item ".env.$Environment" ".env" -Force
    
    # 停止旧服务
    Write-Log "停止旧服务..."
    docker-compose down
    
    # 拉取新镜像
    Write-Log "拉取新镜像..."
    docker-compose pull
    
    # 构建自定义镜像
    Write-Log "构建自定义镜像..."
    docker-compose build --no-cache
    
    # 启动服务
    Write-Log "启动新服务..."
    docker-compose up -d
    
    # 等待服务启动
    Write-Log "等待服务启动..."
    Start-Sleep -Seconds 30
    
    Write-Success-Log "应用部署完成"
}

# ================================
# 健康检查
# ================================
function Test-Health {
    Write-Log "执行健康检查..."
    
    $maxAttempts = 30
    $attempt = 1
    $appPort = if ($env:APP_PORT) { $env:APP_PORT } else { "8000" }
    $healthUrl = "http://localhost:$appPort/api/health"
    
    while ($attempt -le $maxAttempts) {
        try {
            $response = Invoke-WebRequest -Uri $healthUrl -UseBasicParsing -TimeoutSec 10
            if ($response.StatusCode -eq 200) {
                Write-Success-Log "健康检查通过"
                return $true
            }
        }
        catch {
            # 继续重试
        }
        
        Write-Log "健康检查失败，重试 $attempt/$maxAttempts..."
        Start-Sleep -Seconds 10
        $attempt++
    }
    
    Write-Error-Log "健康检查失败，部署可能存在问题"
}

# ================================
# 数据库迁移
# ================================
function Invoke-Migration {
    Write-Log "执行数据库迁移..."
    
    Set-Location $ProjectRoot
    
    # 加载环境变量
    $envContent = Get-Content "$ProjectRoot\.env"
    foreach ($line in $envContent) {
        if ($line -match "^([^=]+)=(.*)$") {
            [System.Environment]::SetEnvironmentVariable($matches[1], $matches[2], "Process")
        }
    }
    
    # 检查数据库连接
    try {
        docker-compose exec -T mysql mysql -u root -p$env:MYSQL_ROOT_PASSWORD -e "SELECT 1" $env:DB_DATABASE | Out-Null
    }
    catch {
        Write-Error-Log "数据库连接失败"
    }
    
    # 执行迁移
    try {
        docker-compose exec -T app php artisan migrate --force
    }
    catch {
        Write-Error-Log "数据库迁移失败"
    }
    
    # 清理缓存
    docker-compose exec -T app php artisan config:clear
    docker-compose exec -T app php artisan cache:clear
    docker-compose exec -T app php artisan route:clear
    docker-compose exec -T app php artisan view:clear
    
    Write-Success-Log "数据库迁移完成"
}

# ================================
# 回滚功能
# ================================
function Invoke-Rollback {
    param([string]$BackupName = "latest")
    
    $backupPath = Join-Path $BackupDir $BackupName
    
    Write-Log "开始回滚到备份: $BackupName"
    
    if (-not (Test-Path $backupPath)) {
        Write-Error-Log "备份不存在: $backupPath"
    }
    
    # 停止当前服务
    docker-compose down
    
    # 恢复数据库
    $databaseBackup = Join-Path $backupPath "database.sql.gz"
    if (Test-Path $databaseBackup) {
        Write-Log "恢复数据库..."
        
        # 加载环境变量
        $envContent = Get-Content "$ProjectRoot\.env.$Environment"
        foreach ($line in $envContent) {
            if ($line -match "^([^=]+)=(.*)$") {
                [System.Environment]::SetEnvironmentVariable($matches[1], $matches[2], "Process")
            }
        }
        
        Expand-Archive -Path $databaseBackup -DestinationPath $backupPath -Force
        $sqlContent = Get-Content (Join-Path $backupPath "database.sql") -Raw
        $sqlContent | docker-compose exec -T mysql mysql -u root -p$env:MYSQL_ROOT_PASSWORD $env:DB_DATABASE
        Remove-Item (Join-Path $backupPath "database.sql")
    }
    
    # 恢复应用数据
    $appBackup = Join-Path $backupPath "app"
    if (Test-Path $appBackup) {
        Write-Log "恢复应用数据..."
        Remove-Item "$ProjectRoot\storage\app" -Recurse -Force -ErrorAction SilentlyContinue
        Copy-Item -Path $appBackup -Destination "$ProjectRoot\storage\app" -Recurse -Force
    }
    
    # 恢复配置文件
    $envBackup = Join-Path $backupPath ".env.$Environment"
    if (Test-Path $envBackup) {
        Copy-Item $envBackup "$ProjectRoot\.env" -Force
    }
    
    # 启动服务
    docker-compose up -d
    
    # 健康检查
    Start-Sleep -Seconds 30
    Test-Health
    
    Write-Success-Log "回滚完成"
}

# ================================
# 清理旧备份
# ================================
function Clear-OldBackups {
    Write-Log "清理旧备份..."
    
    # 保留最近 10 个备份
    $backups = Get-ChildItem -Path $BackupDir -Directory -Name "banho-b2b-*" | Sort-Object -Descending
    if ($backups.Count -gt 10) {
        $backups | Select-Object -Skip 10 | ForEach-Object {
            Remove-Item -Path (Join-Path $BackupDir $_) -Recurse -Force
        }
    }
    
    # 清理旧镜像
    docker image prune -f
    
    Write-Success-Log "清理完成"
}

# ================================
# 发送通知
# ================================
function Send-Notification {
    param([string]$Status, [string]$Message)
    
    # Slack 通知
    if ($env:SLACK_WEBHOOK_URL) {
        try {
            $color = switch ($Status) {
                "error" { "danger" }
                "warning" { "warning" }
                default { "good" }
            }
            
            $payload = @{
                text = $Message
                color = $color
            } | ConvertTo-Json
            
            Invoke-RestMethod -Uri $env:SLACK_WEBHOOK_URL -Method Post -Body $payload -ContentType "application/json" | Out-Null
        }
        catch {
            Write-Warning-Log "Slack 通知发送失败"
        }
    }
    
    # 邮件通知
    if ($env:DEPLOY_EMAIL) {
        try {
            Send-MailMessage -From "deploy@manpou.jp" -To $env:DEPLOY_EMAIL -Subject "万方商事 B2B 采购门户部署通知" -Body $Message -SmtpServer "localhost" -ErrorAction SilentlyContinue
        }
        catch {
            Write-Warning-Log "邮件通知发送失败"
        }
    }
}

# ================================
# 主函数
# ================================
function Start-Deployment {
    $startTime = Get-Date
    
    Write-Log "开始部署到 $Environment 环境..."
    
    # 检查是否为回滚操作
    if ($Rollback) {
        Invoke-Rollback -BackupName $BackupName
        Send-Notification -Status "success" -Message "✅ 万方商事 B2B 采购门户回滚到 $Environment 环境成功"
        return
    }
    
    try {
        # 执行部署流程
        Test-Prerequisites
        Backup-CurrentVersion
        Pull-LatestCode
        Build-And-Deploy
        Invoke-Migration
        Test-Health
        Clear-OldBackups
        
        $endTime = Get-Date
        $duration = ($endTime - $startTime).TotalSeconds
        
        Write-Success-Log "部署完成，耗时: $([math]::Round($duration, 2)) 秒"
        Send-Notification -Status "success" -Message "✅ 万方商事 B2B 采购门户部署到 $Environment 环境成功，耗时: $([math]::Round($duration, 2)) 秒"
    }
    catch {
        Write-Error-Log "部署过程中发生错误: $($_.Exception.Message)"
        Send-Notification -Status "error" -Message "❌ 万方商事 B2B 采购门户部署到 $Environment 环境失败: $($_.Exception.Message)"
    }
}

# ================================
# 错误处理
# ================================
trap {
    Write-Error-Log "部署过程中发生未处理的错误: $($_.Exception.Message)"
    Send-Notification -Status "error" -Message "❌ 万方商事 B2B 采购门户部署到 $Environment 环境失败: $($_.Exception.Message)"
}

# ================================
# 执行主函数
# ================================
Start-Deployment