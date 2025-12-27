<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - POS System</title>
    
    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#e0f2fe">

    <meta name="app-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts & Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Premium UI & Scripts --}}
    @vite(['resources/css/premium-ui.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Bubbles */
        .bubble {
            position: absolute;
            background: linear-gradient(135deg, #a5b4fc 0%, #c4b5fd 100%);
            border-radius: 50%;
            opacity: 0.6;
            filter: blur(60px);
            z-index: -1;
            animation: float 8s ease-in-out infinite;
        }
        .bubble-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
        .bubble-2 { width: 250px; height: 250px; bottom: 50px; right: -50px; animation-delay: -4s; background: linear-gradient(135deg, #67e8f9 0%, #93c5fd 100%); }

        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.7);
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4f46e5 0%, #818cf8 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.5);
            transform: rotate(-10deg);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #9ca3af;
            margin: 1.5rem 0;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }
        
        .passkey-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: white;
            border: 2px dashed #cbd5e1;
            color: #475569;
            width: 100%;
            padding: 12px;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .passkey-btn:hover {
            border-color: #4f46e5;
            background: #f8fafc;
            color: #4f46e5;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Ambient Background -->
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>

        <div class="glass-panel login-card float-card">
            <div class="text-center mb-4">
                <div class="brand-logo">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="fw-bold mb-1">Welcome Back</h3>
                <p class="text-muted small">Access your dashboard</p>
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                {{-- Email Input --}}
                <div class="form-floating-custom">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" id="emailInput" placeholder=" " required autofocus>
                    <label for="emailInput">Email Address</label>
                </div>

                {{-- Password Input --}}
                <div class="form-floating-custom">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" id="passwordInput" placeholder=" " required>
                    <label for="passwordInput">Password</label>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" id="loginBtn" class="btn-premium">
                    <span id="btnText">Sign In</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none ms-2"></span>
                </button>
            </form>

            <div class="divider">OR USE PASSKEY</div>

            <button type="button" onclick="WebAuthn.login()" class="passkey-btn">
                <i class="fas fa-fingerprint fa-lg"></i>
                <span>Use FaceID / Fingerprint / PIN</span>
            </button>

        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Script Logic --}}
    <script>
        // Use standard JS or import from main app.js if preferable
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function() {
                let message = `
                    <ul class="mb-0 text-start" style="list-style: none; padding: 0;">
                        @foreach($errors->all() as $error)
                            <li class="mb-1"><i class="fas fa-circle-exclamation me-2"></i>{{ $error }}</li>
                        @endforeach
                    </ul>
                `;
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    html: message,
                    confirmButtonColor: '#4f46e5'
                });
            });
        @endif

        // Login Button Loading State
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            const spinner = document.getElementById('btnSpinner');
            const text = document.getElementById('btnText');
            
            if(btn.disabled) return;
            
            btn.disabled = true;
            btn.style.opacity = '0.8';
            text.innerText = 'Verifying...';
            spinner.classList.remove('d-none');
        });
        
        // Auto-lowercase email
        document.getElementById('emailInput')?.addEventListener('input', function() {
            this.value = this.value.toLowerCase();
        });
    </script>

    <script>
        if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
    </script>
</body>
</html>