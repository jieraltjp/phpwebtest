<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>雅虎 B2B 采购门户 - 登录</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Noto+Serif+JP:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* 和风登录注册页面样式 */
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            font-family: 'Noto Sans JP', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* 樱花背景动画 */
        .sakura-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .sakura {
            position: absolute;
            background: linear-gradient(120deg, #ffb7c5 0%, #ffc0cb 100%);
            border-radius: 150% 0 150% 0;
            animation: sakura-fall 10s linear infinite;
            opacity: 0.8;
        }

        @keyframes sakura-fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 0.8;
            }
            100% {
                transform: translateY(calc(100vh + 100px)) rotate(360deg);
                opacity: 0;
            }
        }

        /* 主容器 */
        .auth-main {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }

        /* 登录区域 */
        .login-section {
            flex: 1;
            padding: 60px 50px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%);
            position: relative;
        }

        .login-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37 0%, #ffb7c5 50%, #d4af37 100%);
        }

        /* 注册区域（隐藏，通过模态框显示） */
        .register-section {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            padding: 20px;
            overflow-y: auto;
        }

        .register-modal {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border-radius: 20px;
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            color: white;
            position: relative;
            border: 1px solid rgba(212, 175, 55, 0.3);
            min-height: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .register-modal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37 0%, #ffb7c5 50%, #d4af37 100%);
            border-radius: 20px 20px 0 0;
        }

        .close-register {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: #ffb7c5;
            font-size: 24px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .close-register:hover {
            background: rgba(255, 183, 197, 0.2);
            transform: rotate(90deg);
        }

        /* 滚动条样式 */
        .register-modal::-webkit-scrollbar {
            width: 8px;
        }

        .register-modal::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .register-modal::-webkit-scrollbar-thumb {
            background: rgba(212, 175, 55, 0.5);
            border-radius: 10px;
        }

        .register-modal::-webkit-scrollbar-thumb:hover {
            background: rgba(212, 175, 55, 0.7);
        }

        /* 表单样式 */
        .auth-title {
            font-family: 'Noto Serif JP', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #d4af37, #ffb7c5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-subtitle {
            font-size: 16px;
            margin-bottom: 40px;
            opacity: 0.8;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: #333;
        }

        .register-section .form-label {
            color: #ffffff;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .register-section .form-control {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .register-section .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .register-section .form-control:focus {
            border-color: #ffb7c5;
            box-shadow: 0 0 0 3px rgba(255, 183, 197, 0.2);
        }

        /* 按钮样式 */
        .btn-auth {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login {
            background: linear-gradient(45deg, #d4af37 0%, #f4e4bc 100%);
            color: #1a1a1a;
        }

        .btn-register {
            background: linear-gradient(45deg, #ffb7c5 0%, #ffc0cb 100%);
            color: #1a1a1a;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        /* 切换按钮 */
        .switch-btn {
            background: transparent;
            border: 2px solid #d4af37;
            color: #d4af37;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .register-section .switch-btn {
            border-color: #ffb7c5;
            color: #ffb7c5;
        }

        .switch-btn:hover {
            background: #d4af37;
            color: white;
        }

        .register-section .switch-btn:hover {
            background: #ffb7c5;
            color: #1a1a1a;
        }

        /* 错误信息 */
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            color: #d32f2f;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .register-section .error-message {
            background: rgba(255, 255, 255, 0.1);
            color: #ffb7c5;
        }

        /* 成功信息 */
        .success-message {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .register-section .success-message {
            background: rgba(255, 255, 255, 0.1);
            color: #81c784;
        }

        /* 输入验证状态 */
        .form-control.is-valid {
            border-color: #4caf50;
        }

        .form-control.is-invalid {
            border-color: #d32f2f;
        }

        .validation-feedback {
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .validation-feedback.valid {
            color: #4caf50;
        }

        .validation-feedback.invalid {
            color: #d32f2f;
        }

        /* 加载动画 */
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #d4af37;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .auth-card {
                max-width: 400px;
                margin: 20px;
            }

            .login-section {
                padding: 40px 30px;
            }

            .register-modal {
                margin: 20px;
                padding: 30px 20px;
                min-height: auto;
                max-height: calc(100vh - 40px);
            }

            .auth-title {
                font-size: 24px;
            }

            .register-section {
                padding: 10px;
            }
        }

        @media (max-height: 700px) {
            .register-modal {
                margin: 20px auto;
                padding: 30px 20px;
                min-height: auto;
                max-height: calc(100vh - 40px);
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- 樱花背景 -->
        <div class="sakura-bg" id="sakuraBg"></div>

        <!-- 主内容区 -->
        <div class="auth-main">
            <div class="auth-card">
                <!-- 登录区域 -->
                <div class="login-section" id="loginSection">
                    <h2 class="auth-title">欢迎回来</h2>
                    <p class="auth-subtitle">登录您的雅虎 B2B 采购账户</p>

                    <div class="error-message" id="loginError"></div>
                    <div class="success-message" id="loginSuccess"></div>

                    <form id="loginForm">
                        <div class="form-group">
                            <label class="form-label">用户名</label>
                            <input type="text" class="form-control" id="loginUsername" placeholder="请输入用户名" required>
                            <div class="validation-feedback" id="loginUsernameFeedback"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">密码</label>
                            <input type="password" class="form-control" id="loginPassword" placeholder="请输入密码" required>
                            <div class="validation-feedback" id="loginPasswordFeedback"></div>
                        </div>

                        <button type="submit" class="btn-auth btn-login">
                            <span class="btn-text">登录</span>
                            <div class="loading">
                                <div class="spinner"></div>
                            </div>
                        </button>
                    </form>

                    <div style="text-align: center; margin-top: 30px;">
                        <button type="button" class="switch-btn" onclick="showRegister()">还没有账户？立即注册</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 注册模态框 -->
        <div class="register-section" id="registerSection">
            <div class="register-modal">
                <button class="close-register" onclick="hideRegister()">×</button>
                <h2 class="auth-title">创建账户</h2>
                <p class="auth-subtitle">加入雅虎 B2B 采购平台</p>

                <div class="error-message" id="registerError"></div>
                <div class="success-message" id="registerSuccess"></div>

                <form id="registerForm">
                    <div class="form-group">
                        <label class="form-label">姓名</label>
                        <input type="text" class="form-control" id="registerName" placeholder="请输入您的姓名" required>
                        <div class="validation-feedback" id="registerNameFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">用户名</label>
                        <input type="text" class="form-control" id="registerUsername" placeholder="请输入用户名" required>
                        <div class="validation-feedback" id="registerUsernameFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">邮箱</label>
                        <input type="email" class="form-control" id="registerEmail" placeholder="请输入邮箱地址" required>
                        <div class="validation-feedback" id="registerEmailFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">密码</label>
                        <input type="password" class="form-control" id="registerPassword" placeholder="请输入密码" required>
                        <div class="validation-feedback" id="registerPasswordFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">确认密码</label>
                        <input type="password" class="form-control" id="registerPasswordConfirm" placeholder="请再次输入密码" required>
                        <div class="validation-feedback" id="registerPasswordConfirmFeedback"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">公司名称（可选）</label>
                        <input type="text" class="form-control" id="registerCompany" placeholder="请输入公司名称">
                    </div>

                    <div class="form-group">
                        <label class="form-label">电话号码（可选）</label>
                        <input type="tel" class="form-control" id="registerPhone" placeholder="请输入电话号码">
                    </div>

                    <button type="submit" class="btn-auth btn-register">
                        <span class="btn-text">注册</span>
                        <div class="loading">
                            <div class="spinner"></div>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 樱花飘落效果
        function createSakura() {
            const sakuraBg = document.getElementById('sakuraBg');
            const sakura = document.createElement('div');
            sakura.className = 'sakura';
            
            // 随机大小和位置
            const size = Math.random() * 15 + 10;
            sakura.style.width = size + 'px';
            sakura.style.height = size + 'px';
            sakura.style.left = Math.random() * 100 + '%';
            sakura.style.animationDelay = Math.random() * 5 + 's';
            sakura.style.animationDuration = (Math.random() * 5 + 10) + 's';
            
            sakuraBg.appendChild(sakura);
            
            // 动画结束后移除元素
            setTimeout(() => {
                sakura.remove();
            }, 15000);
        }

        // 定期创建樱花
        setInterval(createSakura, 300);

        // 初始创建一些樱花
        for (let i = 0; i < 10; i++) {
            setTimeout(createSakura, i * 200);
        }

        // 显示注册模态框
        function showRegister() {
            document.getElementById('registerSection').style.display = 'block';
            document.body.style.overflow = 'hidden'; // 防止背景滚动
        }

        // 隐藏注册模态框
        function hideRegister() {
            document.getElementById('registerSection').style.display = 'none';
            document.body.style.overflow = 'auto'; // 恢复滚动
            
            // 清空注册表单
            document.getElementById('registerForm').reset();
            document.querySelectorAll('#registerForm .form-control').forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
            });
            document.querySelectorAll('#registerForm .validation-feedback').forEach(feedback => {
                feedback.style.display = 'none';
            });
        }

        // 点击模态框背景关闭
        document.getElementById('registerSection').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRegister();
            }
        });

        // ESC键关闭模态框
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRegister();
            }
        });

        // API 基础配置
        const API_BASE = '/api';
        
        // 显示错误消息
        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            // 3秒后自动隐藏
            setTimeout(() => {
                errorElement.style.display = 'none';
            }, 3000);
        }

        // 显示成功消息
        function showSuccess(elementId, message) {
            const successElement = document.getElementById(elementId);
            successElement.textContent = message;
            successElement.style.display = 'block';
            
            // 3秒后自动隐藏
            setTimeout(() => {
                successElement.style.display = 'none';
            }, 3000);
        }

        // 隐藏所有消息
        function hideAllMessages() {
            document.querySelectorAll('.error-message, .success-message').forEach(el => {
                el.style.display = 'none';
            });
        }

        // 设置加载状态
        function setLoading(formId, loading) {
            const form = document.getElementById(formId);
            const btnText = form.querySelector('.btn-text');
            const spinner = form.querySelector('.loading');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (loading) {
                btnText.style.display = 'none';
                spinner.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                btnText.style.display = 'block';
                spinner.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        // 输入验证
        function validateInput(input, feedbackId, validationFn) {
            const feedback = document.getElementById(feedbackId);
            const isValid = validationFn(input.value);
            
            if (input.value.length > 0) {
                if (isValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    feedback.classList.remove('invalid');
                    feedback.classList.add('valid');
                    feedback.textContent = '✓ 格式正确';
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    feedback.classList.remove('valid');
                    feedback.classList.add('invalid');
                    feedback.textContent = '✗ 格式不正确';
                }
                feedback.style.display = 'block';
            } else {
                input.classList.remove('is-valid', 'is-invalid');
                feedback.style.display = 'none';
            }
            
            return isValid;
        }

        // 实时验证
        document.getElementById('registerUsername').addEventListener('blur', function() {
            validateInput(this, 'registerUsernameFeedback', (value) => {
                return value.length >= 3 && value.length <= 50 && /^[a-zA-Z0-9_]+$/.test(value);
            });
        });

        document.getElementById('registerEmail').addEventListener('blur', function() {
            validateInput(this, 'registerEmailFeedback', (value) => {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            });
        });

        document.getElementById('registerPassword').addEventListener('blur', function() {
            validateInput(this, 'registerPasswordFeedback', (value) => {
                return value.length >= 8 && /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(value);
            });
        });

        document.getElementById('registerPasswordConfirm').addEventListener('blur', function() {
            const password = document.getElementById('registerPassword').value;
            validateInput(this, 'registerPasswordConfirmFeedback', (value) => {
                return value === password && value.length > 0;
            });
        });

        // 登录表单提交
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAllMessages();
            setLoading('loginForm', true);
            
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            
            try {
                const response = await fetch(`${API_BASE}/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (response.ok && data.status === 'success') {
                    // 保存令牌
                    localStorage.setItem('access_token', data.data.access_token);
                    localStorage.setItem('user', JSON.stringify(data.data.user));
                    
                    showSuccess('loginSuccess', '登录成功！正在跳转...');
                    
                    // 跳转到仪表板
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    showError('loginError', data.message || '登录失败，请检查用户名和密码');
                }
            } catch (error) {
                showError('loginError', '网络错误，请稍后重试');
            } finally {
                setLoading('loginForm', false);
            }
        });

        // 注册表单提交
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAllMessages();
            setLoading('registerForm', true);
            
            const formData = {
                name: document.getElementById('registerName').value,
                username: document.getElementById('registerUsername').value,
                email: document.getElementById('registerEmail').value,
                password: document.getElementById('registerPassword').value,
                password_confirmation: document.getElementById('registerPasswordConfirm').value,
                company: document.getElementById('registerCompany').value,
                phone: document.getElementById('registerPhone').value,
            };
            
            try {
                const response = await fetch(`${API_BASE}/auth/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (response.ok && data.status === 'success') {
                    // 保存令牌
                    localStorage.setItem('access_token', data.data.access_token);
                    localStorage.setItem('user', JSON.stringify(data.data.user));
                    
                    showSuccess('registerSuccess', '注册成功！正在跳转...');
                    
                    // 跳转到仪表板
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1500);
                } else {
                    const errorMessage = data.errors ? Object.values(data.errors).flat().join(', ') : data.message;
                    showError('registerError', errorMessage || '注册失败，请检查输入信息');
                }
            } catch (error) {
                showError('registerError', '网络错误，请稍后重试');
            } finally {
                setLoading('registerForm', false);
            }
        });

        // 检查用户名可用性
        let usernameCheckTimeout;
        document.getElementById('registerUsername').addEventListener('input', function() {
            clearTimeout(usernameCheckTimeout);
            const username = this.value;
            
            if (username.length >= 3) {
                usernameCheckTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`${API_BASE}/auth/check-username`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ username })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.status === 'success') {
                            const feedback = document.getElementById('registerUsernameFeedback');
                            if (data.data.available) {
                                feedback.textContent = '✓ 用户名可用';
                                feedback.className = 'validation-feedback valid';
                            } else {
                                feedback.textContent = '✗ 用户名已被使用';
                                feedback.className = 'validation-feedback invalid';
                            }
                            feedback.style.display = 'block';
                        }
                    } catch (error) {
                        // 忽略检查错误
                    }
                }, 500);
            }
        });

        // 检查邮箱可用性
        let emailCheckTimeout;
        document.getElementById('registerEmail').addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value;
            
            if (email.includes('@')) {
                emailCheckTimeout = setTimeout(async () => {
                    try {
                        const response = await fetch(`${API_BASE}/auth/check-email`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ email })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.status === 'success') {
                            const feedback = document.getElementById('registerEmailFeedback');
                            if (data.data.available) {
                                feedback.textContent = '✓ 邮箱可用';
                                feedback.className = 'validation-feedback valid';
                            } else {
                                feedback.textContent = '✗ 邮箱已被注册';
                                feedback.className = 'validation-feedback invalid';
                            }
                            feedback.style.display = 'block';
                        }
                    } catch (error) {
                        // 忽略检查错误
                    }
                }, 500);
            }
        });
    </script>
</body>
</html>