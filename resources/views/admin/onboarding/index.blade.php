<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email | VERAPOS</title>

    {{-- Fonts & Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Scripts & Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800">

    <div class="min-h-screen flex w-full relative">

        <!-- LARGE SCREEN: Left Split -->
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-7/12 bg-indigo-600 relative overflow-hidden flex-col justify-between p-12 text-white">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
                <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-indigo-500 rounded-full blur-3xl opacity-50">
                </div>
                <div
                    class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-800 rounded-full blur-3xl opacity-40">
                </div>
            </div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ asset('images/verapos_logo.jpg') }}" alt="VeraPOS Logo"
                        class="h-14 w-auto mix-blend-multiply rounded-lg">
                </div>
            </div>

            <div class="relative z-10 max-w-lg">
                <h1 class="text-5xl font-bold mb-6 leading-tight">Welcome, {{ $user->first_name }}!</h1>
                <p class="text-indigo-100 text-lg leading-relaxed mb-8">
                    To keep your account secure, please verify your email address before continuing.
                </p>
            </div>

            <div class="relative z-10 text-xs text-indigo-300">
                &copy; {{ date('Y') }} VERAPOS System. All rights reserved.
            </div>
        </div>

        <!-- RIGHT SPLIT (Form) -->
        <div class="w-full lg:w-1/2 xl:w-5/12 flex items-center justify-center p-6 sm:p-12 relative">
            <div class="w-full max-w-lg space-y-8">

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">Email Verification</h2>
                    <p class="mt-2 text-sm text-gray-500">We will send a code to your email.</p>
                </div>

                <!-- Form -->
                <div>
                    <form id="form-verify" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="flex gap-2">
                                <input type="email" id="email-input" name="email" placeholder="example@gmail.com"
                                    value="{{ $user->email ?? '' }}"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <button type="button" id="btn-send-otp" onclick="sendOtp()"
                                    class="min-w-[120px] px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-800 hover:bg-gray-900 focus:outline-none whitespace-nowrap transition-all">
                                    Send Code
                                </button>
                            </div>
                            <div id="otp-timer" class="text-xs text-indigo-600 mt-2 font-bold" style="display: none;">
                            </div>
                        </div>

                        <div id="otp-section" style="display: none;"
                            class="text-center bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300 animate-fade-in-up">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Enter 6-Digit Code</label>
                            <input type="text" id="otp-input" maxlength="6"
                                class="block w-48 mx-auto px-4 py-3 text-2xl text-center tracking-[0.5em] font-bold border-2 border-indigo-500 rounded-lg text-indigo-700 focus:ring-indigo-500 focus:border-indigo-600 outline-none"
                                placeholder="000000" oninput="checkAutoSubmit(this)">
                            <p class="text-xs text-gray-400 mt-2">Auto-submits when completed</p>
                        </div>
                    </form>

                    <div class="mt-6 border-t pt-4">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Log
                                Out</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        const ROUTES = {
            sendOtp: "{{ route('onboarding.sendOtp') }}",
            verify: "{{ route('onboarding.verify') }}"
        };

        let otpTimerInterval;
        let timeLeft = 0;

        async function sendOtp() {
            const email = document.getElementById('email-input').value;
            if (!email || !email.includes('@')) {
                Swal.fire('Error', 'Please enter a valid email address', 'error');
                return;
            }

            const btn = document.getElementById('btn-send-otp');
            setLoading(btn, true);

            try {
                const response = await axios.post(ROUTES.sendOtp, { email: email });
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent',
                        text: 'Please check your inbox.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    document.getElementById('otp-section').style.display = 'block';
                    document.getElementById('email-input').readOnly = true;
                    startTimer(60);
                } else {
                    Swal.fire('Error', response.data.message, 'error');
                    setLoading(btn, false);
                }
            } catch (error) {
                showError(error);
                setLoading(btn, false);
            }
        }

        function startTimer(seconds) {
            const btn = document.getElementById('btn-send-otp');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            timeLeft = seconds;
            const timerDisplay = document.getElementById('otp-timer');
            timerDisplay.style.display = 'block';

            clearInterval(otpTimerInterval);
            otpTimerInterval = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = `Resend in ${timeLeft}s`;
                if (timeLeft <= 0) {
                    clearInterval(otpTimerInterval);
                    timerDisplay.style.display = 'none';
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.textContent = 'Resend Code';
                    setLoading(btn, false); // Reset visual state
                }
            }, 1000);
        }

        async function checkAutoSubmit(input) {
            if (input.value.length === 6) {
                const email = document.getElementById('email-input').value;
                const code = input.value;

                // Lock input
                input.disabled = true;

                try {
                    const response = await axios.post(ROUTES.verify, { email: email, code: code });
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Verified!',
                            text: 'Redirecting to MPIN setup...',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = response.data.redirect;
                        });
                    } else {
                        Swal.fire('Invalid OTP', 'The code you entered is incorrect.', 'error');
                        input.value = '';
                        input.disabled = false;
                        input.focus();
                    }
                } catch (error) {
                    showError(error);
                    input.value = '';
                    input.disabled = false;
                }
            }
        }

        function setLoading(btn, isLoading) {
            if (isLoading) {
                btn.dataset.originalText = btn.innerText;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                if (btn.dataset.originalText) btn.innerText = btn.dataset.originalText;
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        function showError(error) {
            const msg = error.response?.data?.message || 'An unexpected error occurred.';
            Swal.fire('Error', msg, 'error');
        }
    </script>
</body>

</html>