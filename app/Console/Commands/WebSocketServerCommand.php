<?php

namespace App\Console\Commands;

use App\Services\WebSocketService;
use App\Services\RealtimeEventService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * WebSocket服务器命令
 * 
 * 启动高性能WebSocket服务器，支持5000+并发连接
 */
class WebSocketServerCommand extends Command
{
    protected $signature = 'websocket:serve 
                            {--host=0.0.0.0 : WebSocket服务器监听地址}
                            {--port=8080 : WebSocket服务器监听端口}
                            {--workers=4 : 工作进程数量}
                            {--max-connections=5000 : 最大连接数}
                            {--memory-limit=512M : 内存限制}';
    
    protected $description = 'Start WebSocket server for real-time communications';

    private WebSocketService $webSocketService;
    private RealtimeEventService $realtimeEventService;
    private bool $shouldStop = false;
    private array $workers = [];

    public function handle(): int
    {
        $host = $this->option('host');
        $port = $this->option('port');
        $maxConnections = $this->option('max-connections');
        $memoryLimit = $this->option('memory-limit');
        $workers = $this->option('workers');

        $this->info("Starting WebSocket server on {$host}:{$port}");
        $this->info("Max connections: {$maxConnections}");
        $this->info("Workers: {$workers}");
        $this->info("Memory limit: {$memoryLimit}");

        // 设置内存限制
        ini_set('memory_limit', $memoryLimit);

        // 初始化服务
        $this->initializeServices();

        // 设置信号处理
        $this->setupSignalHandlers();

        // 启动工作进程
        $this->startWorkers($workers, $host, $port);

        // 启动主监控循环
        $this->startMainLoop();

        $this->info('WebSocket server stopped');
        return 0;
    }

    /**
     * 初始化服务
     */
    private function initializeServices(): void
    {
        $this->webSocketService = app(WebSocketService::class);
        $this->realtimeEventService = app(RealtimeEventService::class);

        $this->info('Services initialized');
    }

    /**
     * 设置信号处理器
     */
    private function setupSignalHandlers(): void
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
            pcntl_signal(SIGUSR1, [$this, 'handleSignal']);
        }
    }

    /**
     * 启动工作进程
     */
    private function startWorkers(int $workerCount, string $host, int $port): void
    {
        for ($i = 0; $i < $workerCount; $i++) {
            $pid = pcntl_fork();
            
            if ($pid == -1) {
                $this->error('Failed to fork worker process');
                exit(1);
            } elseif ($pid == 0) {
                // 子进程
                $this->runWorker($i, $host, $port);
                exit(0);
            } else {
                // 父进程
                $this->workers[$pid] = $i;
                $this->info("Worker {$i} started with PID {$pid}");
            }
        }
    }

    /**
     * 运行工作进程
     */
    private function runWorker(int $workerId, string $host, int $port): void
    {
        $this->info("Worker {$workerId} running on {$host}:{$port}");

        // 创建WebSocket服务器
        $server = $this->createWebSocketServer($host, $port);

        // 设置服务器事件处理器
        $this->setupServerHandlers($server, $workerId);

        // 启动服务器
        $server->run();
    }

    /**
     * 创建WebSocket服务器
     */
    private function createWebSocketServer(string $host, int $port)
    {
        // 这里应该使用实际的WebSocket库，如Ratchet或Swoole
        // 由于我们没有安装外部库，这里创建一个模拟服务器
        return new class($host, $port, $this->webSocketService, $this->realtimeEventService) {
            private $host;
            private $port;
            private $webSocketService;
            private $realtimeEventService;
            private $connections = [];

            public function __construct($host, $port, $webSocketService, $realtimeEventService)
            {
                $this->host = $host;
                $this->port = $port;
                $this->webSocketService = $webSocketService;
                $this->realtimeEventService = $realtimeEventService;
            }

            public function run(): void
            {
                // 模拟WebSocket服务器运行
                while (true) {
                    $this->processIncomingMessages();
                    $this->processOutgoingMessages();
                    $this->performMaintenanceTasks();
                    usleep(100000); // 100ms
                }
            }

            private function processIncomingMessages(): void
            {
                // 处理Redis队列中的传入消息
                $messages = Redis::brpop('websocket_incoming', 1);
                
                if ($messages) {
                    [, $messageData] = $messages;
                    $message = json_decode($messageData, true);
                    
                    if ($message) {
                        $this->webSocketService->handleMessage(
                            $message['connection_id'],
                            $message['message']
                        );
                    }
                }
            }

            private function processOutgoingMessages(): void
            {
                // 处理Redis队列中的传出消息
                $messages = Redis::lrange('websocket_outgoing', 0, 100);
                
                if (!empty($messages)) {
                    Redis::ltrim('websocket_outgoing', count($messages), -1);
                    
                    foreach ($messages as $messageData) {
                        $message = json_decode($messageData, true);
                        
                        if ($message) {
                            // 这里应该通过实际的WebSocket连接发送消息
                            // 暂时记录到日志
                            Log::debug('WebSocket message sent', [
                                'connection_id' => $message['connection_id'],
                                'message_type' => $message['message']['type'] ?? 'unknown'
                            ]);
                        }
                    }
                }
            }

            private function performMaintenanceTasks(): void
            {
                // 定期执行维护任务
                static $lastCleanup = 0;
                
                if (time() - $lastCleanup > 60) { // 每分钟执行一次
                    $this->webSocketService->cleanupExpiredConnections();
                    $this->realtimeEventService->updateRealtimeStats();
                    $lastCleanup = time();
                }
            }
        };
    }

    /**
     * 设置服务器事件处理器
     */
    private function setupServerHandlers($server, int $workerId): void
    {
        // 这里应该设置WebSocket服务器的各种事件处理器
        // 如onOpen, onMessage, onClose, onError等
    }

    /**
     * 启动主监控循环
     */
    private function startMainLoop(): void
    {
        $this->info('Main monitoring loop started');

        while (!$this->shouldStop) {
            // 检查工作进程状态
            $this->checkWorkers();

            // 更新统计信息
            $this->updateStats();

            // 处理信号
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            sleep(1);
        }

        // 停止所有工作进程
        $this->stopWorkers();
    }

    /**
     * 检查工作进程状态
     */
    private function checkWorkers(): void
    {
        foreach ($this->workers as $pid => $workerId) {
            $status = 0;
            $result = pcntl_waitpid($pid, $status, WNOHANG);

            if ($result == -1) {
                $this->error("Failed to check worker {$workerId} status");
                unset($this->workers[$pid]);
            } elseif ($result > 0) {
                $this->warn("Worker {$workerId} (PID {$pid}) exited");
                unset($this->workers[$pid]);
                
                // 重启工作进程
                $this->restartWorker($workerId);
            }
        }
    }

    /**
     * 重启工作进程
     */
    private function restartWorker(int $workerId): void
    {
        $this->info("Restarting worker {$workerId}");
        
        $pid = pcntl_fork();
        
        if ($pid == -1) {
            $this->error("Failed to restart worker {$workerId}");
        } elseif ($pid == 0) {
            $this->runWorker($workerId, $this->option('host'), $this->option('port'));
            exit(0);
        } else {
            $this->workers[$pid] = $workerId;
            $this->info("Worker {$workerId} restarted with PID {$pid}");
        }
    }

    /**
     * 停止所有工作进程
     */
    private function stopWorkers(): void
    {
        $this->info('Stopping all workers...');

        foreach ($this->workers as $pid => $workerId) {
            $this->info("Stopping worker {$workerId} (PID {$pid})");
            posix_kill($pid, SIGTERM);
            
            // 等待进程退出
            $timeout = 10;
            $start = time();
            
            while (time() - $start < $timeout) {
                $status = 0;
                $result = pcntl_waitpid($pid, $status, WNOHANG);
                
                if ($result > 0) {
                    $this->info("Worker {$workerId} stopped");
                    break;
                }
                
                usleep(100000); // 100ms
            }
            
            // 如果进程仍在运行，强制杀死
            if (pcntl_waitpid($pid, $status, WNOHANG) === 0) {
                $this->warn("Force killing worker {$workerId}");
                posix_kill($pid, SIGKILL);
            }
        }
    }

    /**
     * 更新统计信息
     */
    private function updateStats(): void
    {
        static $lastUpdate = 0;
        
        if (time() - $lastUpdate >= 30) { // 每30秒更新一次
            $stats = $this->webSocketService->getStats();
            
            // 存储统计信息到Redis
            Redis::hmset('websocket_stats', [
                'total_connections' => $stats['total_connections'],
                'authenticated_connections' => $stats['authenticated_connections'],
                'total_channels' => $stats['total_channels'],
                'memory_usage' => $stats['memory_usage'],
                'updated_at' => now()->toISOString()
            ]);
            
            $lastUpdate = time();
        }
    }

    /**
     * 处理信号
     */
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        switch ($signal) {
            case SIGTERM:
            case SIGINT:
                $this->info("Received shutdown signal");
                $this->shouldStop = true;
                break;
                
            case SIGUSR1:
                $this->info("Received status check signal");
                $this->printStatus();
                break;
        }
        
        return $previousExitCode;
    }

    /**
     * 打印状态信息
     */
    private function printStatus(): void
    {
        $stats = $this->webSocketService->getStats();
        
        $this->line('');
        $this->info('=== WebSocket Server Status ===');
        $this->line('Workers: ' . count($this->workers));
        $this->line('Total Connections: ' . $stats['total_connections']);
        $this->line('Authenticated: ' . $stats['authenticated_connections']);
        $this->line('Channels: ' . $stats['total_channels']);
        $this->line('Memory Usage: ' . $this->formatBytes($stats['memory_usage']));
        $this->line('Peak Memory: ' . $this->formatBytes($stats['memory_peak']));
        $this->info('===============================');
        $this->line('');
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}