<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - POS System</title>
    
    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        }
        
        body {
            background: #f1f5f9;
            background-image: radial-gradient(#e0e7ff 1px, transparent 1px);
            background-size: 24px 24px;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
        }

        .login-header {
            background: var(--primary-gradient);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        /* Decorative circle in header */
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            transform: scaleX(1.5);
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .form-floating > .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding-left: 15px;
        }
        
        .form-floating > .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            body { background: white; align-items: flex-start; }
            .login-card { box-shadow: none; border-radius: 0; border: none; max-width: 100%; height: 100vh; }
            .login-header { padding: 60px 30px 50px; border-radius: 0 0 30px 30px; }
            .login-header::after { display: none; }
        }
    </style>
</head>
<body>

    <div class="login-card">
        {{-- Header Section --}}
        <div class="login-header">
            <div class="brand-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <h4 class="fw-bold mb-1">Welcome Back!</h4>
            <p class="mb-0 opacity-75 small">Sign in to your POS Terminal</p>
        </div>

        {{-- Form Section --}}
        <div class="p-4 pt-5">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                
                {{-- Email Input with Floating Label --}}
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="emailInput" required autofocus>
                    <label for="emailInput" class="text-muted"><i class="fas fa-envelope me-2"></i>Email Address</label>
                </div>

                {{-- Password Input with Floating Label --}}
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control" id="passwordInput" required>
                    <label for="passwordInput" class="text-muted"><i class="fas fa-lock me-2"></i>Password</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-login text-white mb-3">
                    SIGN IN
                </button>

                <div class="text-center">
                    <a href="#" class="text-decoration-none small text-secondary fw-medium">Forgot Password?</a>
                </div>
            </form>
        </div>

        {{-- Footer Section --}}
        <div class="bg-light p-3 text-center border-top">
            <small class="text-muted fw-bold" style="font-size: 0.7rem;">POWERED BY SARIPOS</small>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Error Handling Script --}}
    @if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                html: `
                    <div class="text-start bg-light p-3 rounded border text-danger small">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                `,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Try Again',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold'
                }
            });
        });
    </script>
    @endif

    {{-- Service Worker --}}
    <script>
        if('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
    </script>

</body>
</html>