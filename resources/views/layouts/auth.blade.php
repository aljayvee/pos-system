<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'VeraPOS Security' }}</title>

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
            </div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-cube text-2xl"></i>
                    </div>
                    <span class="text-2xl font-bold tracking-tight">VeraPOS</span>
                </div>
                <h1 class="text-5xl font-bold leading-tight mb-4">
                    Secure Access <br>
                    <span class="text-indigo-200">Simplified.</span>
                </h1>
                <p class="text-indigo-100 text-lg max-w-md leading-relaxed">
                    Enter your security credentials to access the system.
                </p>
            </div>

            <div class="relative z-10 text-xs text-indigo-300">
                &copy; {{ date('Y') }} VERAPOS System. All rights reserved.
            </div>
        </div>

        <!-- RIGHT SIDE: Content Form -->
        <div class="w-full lg:w-1/2 xl:w-5/12 flex items-center justify-center p-6 bg-white sm:bg-gray-50 relative">
            <div class="w-full max-w-[420px] bg-white sm:shadow-xl sm:rounded-2xl p-8 sm:p-10 relative z-10">

                @yield('content')

            </div>
        </div>

    </div>

</body>

</html>