<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <title>Login - POS System</title>

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">

    <meta name="app-url" content="{{ url('/') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts & Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800">

    <div class="min-h-screen flex w-full">

        <!-- LARGE SCREEN: Left Split (Branding) -->
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-7/12 bg-indigo-600 relative overflow-hidden flex-col justify-between p-12 text-white">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
                <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-indigo-500 rounded-full blur-3xl opacity-50">
                </div>
                <div
                    class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-800 rounded-full blur-3xl opacity-40">
                </div>
                <div
                    class="absolute top-[40%] left-[20%] w-64 h-64 bg-indigo-400 rounded-full blur-2xl opacity-20 animate-pulse">
                </div>
            </div>

            <!-- Content -->
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ asset('images/verapos_logo_v2.png') }}" alt="VeraPOS Logo"
                        class="h-14 w-auto rounded-lg">
                </div>
            </div>

            <div class="relative z-10 max-w-lg">
                <h1 class="text-5xl font-bold mb-6 leading-tight">Manage your business with confidence.</h1>
                <p class="text-indigo-100 text-lg leading-relaxed mb-8">
                    Streamline your point of sale operations, track inventory in real-time, and gain actionable insights
                    to grow your business.
                </p>


            </div>

            <div class="relative z-10 text-xs text-indigo-300">
                &copy; {{ date('Y') }} VERAPOS System. All rights reserved.
            </div>
        </div>

        <!-- RIGHT SPLIT (Form) -->
        <div class="w-full lg:w-1/2 xl:w-5/12 flex items-center justify-center p-6 sm:p-12 relative">

            <!-- Mobile decorative bubble -->
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-indigo-100 rounded-full blur-3xl opacity-60 lg:hidden -z-10 pointer-events-none">
            </div>

            <div class="w-full max-w-md space-y-8">

                <!-- Header (Mobile/Desktop consistent) -->
                <div class="text-center lg:text-left">
                    <div class="lg:hidden flex justify-center mb-6">
                        <div
                            class="w-14 h-14 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 transform rotate-3">
                            <i class="fas fa-layer-group text-2xl"></i>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">Welcome back</h2>
                    <p class="mt-2 text-sm text-gray-500">Please enter your details to sign in.</p>
                </div>

                <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-6">
                    @csrf

                    <div class="space-y-5">
                        <!-- Username Input -->
                        <div>
                            <label for="usernameInput"
                                class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-user"></i>
                                </div>
                                <input id="usernameInput" name="username" type="text" autocomplete="username" required
                                    autofocus class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all shadow-sm
                                           hover:border-gray-400" placeholder="Enter your username">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="passwordInput"
                                class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <input id="passwordInput" name="password" type="password"
                                    autocomplete="current-password" required class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all shadow-sm
                                           hover:border-gray-400" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        @if(config('safety_flag_features.remember_me'))
                            <div class="flex items-center">
                                <input id="remember" name="remember" type="checkbox"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer">

                                <label for="remember"
                                    class="ml-2 block text-sm text-gray-900 cursor-pointer select-none">Remember me</label>

                            </div>
                        @endif
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" id="loginBtn"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white 
                               bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 
                               transition-all duration-200 shadow-lg shadow-indigo-600/30 transform hover:-translate-y-0.5">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i
                                class="fas fa-arrow-right text-indigo-400 group-hover:text-indigo-300 transition-colors"></i>
                        </span>
                        <span id="btnText">Sign in to account</span>
                        <span id="btnSpinner" class="hidden ml-2"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>
                </form>

                @if(config('safety_flag_features.webauthn'))
                    <div id="passkey-container" class="hidden">
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-200"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-3 bg-gray-50 text-gray-500 font-medium">Or continue with</span>
                            </div>
                        </div>

                        <button type="button" onclick="WebAuthn.login()" class="w-full flex items-center justify-center gap-3 py-3 px-4 border-2 border-dashed border-gray-300 rounded-xl text-sm font-semibold text-gray-700 
                                                                            hover:bg-white hover:border-indigo-500 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 
                                                                            transition-all duration-200 bg-transparent">
                            <i class="fas fa-fingerprint text-xl"></i>
                            <span>Sign in with Passkey</span>
                        </button>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Error Handling
        @if($errors->any())
            document.addEventListener("DOMContentLoaded", function () {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    html: `<ul class="text-sm text-left m-0 pl-4 list-disc">
                                                                                                        @foreach($errors->all() as $error)
                                                                                                            <li>{{ $error }}</li>
                                                                                                        @endforeach
                                                                                                       </ul>`
                });
            });
        @endif

        // Button Loading State
        document.querySelector('form').addEventListener('submit', function (e) {
            const btn = document.getElementById('loginBtn');
            const spinner = document.getElementById('btnSpinner');
            const text = document.getElementById('btnText');

            if (btn.disabled) return;

            btn.disabled = true;
            btn.classList.add('cursor-not-allowed', 'opacity-80');
            text.textContent = 'Verifying...';
            spinner.classList.remove('hidden');
        });

        // Auto-lowercase username
        document.getElementById('usernameInput')?.addEventListener('input', function () {
            this.value = this.value.toLowerCase();
        });

        // WebAuthn Capability Check
        document.addEventListener('DOMContentLoaded', async () => {
            if (await WebAuthn.isAvailable()) {
                document.getElementById('passkey-container').classList.remove('hidden');
            }
        });
    </script>

    <script>
        if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
    </script>
</body>

</html>