<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | VERAPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden text-center p-10 animate-fade-in-up">

        <div class="mb-8 flex justify-center">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center relative">
                <i class="fas fa-check text-5xl text-green-600"></i>
                <!-- Pulse Effect -->
                <div class="absolute top-0 left-0 w-full h-full bg-green-400 rounded-full opacity-20 animate-ping">
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">You're All Set!</h1>
        <p class="text-gray-500 mb-8">
            Your account has been verified and your security PIN is set. You are ready to start using VeraPOS.
        </p>

        <a href="{{ Auth::user()->role === 'cashier' ? route('cashier.pos') : route('admin.dashboard') }}"
            class="inline-flex items-center justify-center w-full px-6 py-4 border border-transparent text-base font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg transition-transform transform hover:-translate-y-1">
            Go to {{ Auth::user()->role === 'cashier' ? 'POS System' : 'Dashboard' }}
            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
        </a>

    </div>

</body>

</html>