<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="force-theme" content="light">
    <title>System Setup | VERAPOS</title>

    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">

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

        /* Step Wizard Styling */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            /* w-10 */
            height: 2.5rem;
            /* h-10 */
            border-radius: 9999px;
            /* rounded-full */
            border-width: 2px;
            border-style: solid;
            font-size: 0.875rem;
            /* text-sm */
            font-weight: 600;
            /* font-semibold */
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            border-color: #4f46e5;
            /* border-indigo-600 */
            color: #4f46e5;
            /* text-indigo-600 */
            background-color: white;
        }

        .step-indicator.completed {
            border-color: #4f46e5;
            background-color: #4f46e5;
            color: white;
        }

        .step-indicator.pending {
            border-color: #e5e7eb;
            /* border-gray-200 */
            color: #9ca3af;
            /* text-gray-400 */
            background-color: white;
        }

        .step-line {
            flex: 1;
            height: 0.125rem;
            /* h-0.5 */
            background-color: #e5e7eb;
            /* bg-gray-200 */
            transition: all 0.3s ease;
        }

        .step-line.completed {
            background-color: #4f46e5;
            /* bg-indigo-600 */
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800">

    <div class="min-h-screen flex w-full relative">



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
                    <!-- Assuming logo usage similar to login -->
                    <img src="{{ asset('images/verapos_logo.jpg') }}" alt="VeraPOS Logo"
                        class="h-14 w-auto mix-blend-multiply rounded-lg">
                </div>
            </div>

            <div class="relative z-10 max-w-lg">
                <h1 class="text-5xl font-bold mb-6 leading-tight">Setup your business.</h1>
                <p class="text-indigo-100 text-lg leading-relaxed mb-8">
                    Configure your admin account and settings to get started with VeraPOS.
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
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">Account Setup</h2>
                    <p class="mt-2 text-sm text-gray-500">Follow the steps to initialize the system.</p>
                </div>

                <!-- Wizard Progress -->
                <div class="flex items-center justify-between mt-6 mb-8">
                    <div class="step-indicator active" id="step-indicator-1">1</div>
                    <div class="step-line" id="step-line-1"></div>
                    <div class="step-indicator pending" id="step-indicator-2">2</div>
                    <div class="step-line" id="step-line-2"></div>
                    <div class="step-indicator pending" id="step-indicator-3">3</div>
                </div>

                <!-- STEP 1: PERSONAL DETAILS -->
                <div id="step-1">
                    <form id="form-step-1" class="space-y-5">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Helper for common input style -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" name="first_name" required
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Middle Name <span
                                        class="text-gray-400 font-normal">(Optional)</span></label>
                                <input type="text" name="middle_name"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" name="last_name" required
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input type="text" name="username" required placeholder="For login"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" required
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" name="birthdate" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" required minlength="8"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Confirm</label>
                                <input type="password" name="password_confirmation" required minlength="8"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <ul class="text-xs text-gray-500 space-y-1 mt-2" id="password-requirements">
                                <li id="req-length"><i class="far fa-circle mr-1"></i> At least 8 characters</li>
                                <li id="req-lower"><i class="far fa-circle mr-1"></i> Lowercase letter</li>
                                <li id="req-upper"><i class="far fa-circle mr-1"></i> Uppercase letter</li>
                                <li id="req-number"><i class="far fa-circle mr-1"></i> Number</li>
                                <li id="req-special"><i class="far fa-circle mr-1"></i> Special character (e.g.
                                    !@#$%^&*()~?)</li>
                                <li id="req-match"><i class="far fa-circle mr-1"></i> Passwords match</li>
                            </ul>
                        </div>

                        <div class="pt-4">
                            <button type="submit" id="step1-submit-btn" disabled
                                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                Next Step <i class="fas fa-arrow-right ml-2 mt-0.5"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- STEP 2: GMAIL VERIFICATION -->
                <div id="step-2" style="display: none;">
                    <div class="mb-6">
                        <h5 class="text-lg font-bold text-gray-900">Email Verification</h5>
                        <p class="text-sm text-gray-500">Verify your Gmail for account security.</p>
                    </div>

                    <form id="form-step-2" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gmail Address</label>
                            <div class="flex gap-2">
                                <input type="email" id="email-input" placeholder="example@gmail.com"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <button type="button" id="btn-send-otp" onclick="sendOtp()"
                                    class="btn-loading-target px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-800 hover:bg-gray-900 focus:outline-none whitespace-nowrap">
                                    Send Code
                                </button>
                            </div>
                            <div id="otp-timer" class="text-xs text-red-500 mt-2 font-bold" style="display: none;">
                            </div>
                        </div>

                        <div id="otp-section" style="display: none;"
                            class="text-center bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Enter 6-Digit Code</label>
                            <input type="text" id="otp-input" maxlength="6"
                                class="block w-48 mx-auto px-4 py-3 text-2xl text-center tracking-[0.5em] font-bold border-2 border-indigo-500 rounded-lg text-indigo-700 focus:ring-indigo-500 focus:border-indigo-600 outline-none"
                                placeholder="000000" oninput="checkAutoSubmit(this)">
                            <p class="text-xs text-gray-400 mt-2">Auto-submits when completed</p>
                        </div>
                    </form>

                    <div class="mt-6 flex justify-start">
                        <button type="button" class="text-sm text-gray-500 hover:text-gray-900 underline"
                            onclick="goToStep(1)">Back to details</button>
                    </div>
                </div>

                <!-- STEP 3: SUCCESS -->
                <div id="step-3" style="display: none;" class="text-center py-10">
                    <div class="mb-6 flex justify-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-4xl text-green-600"></i>
                        </div>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-900 mb-2">Account Created!</h4>
                    <p class="text-gray-500 mb-8">Your account has been successfully verified.</p>

                    <a href="{{ route('auth.mpin.setup') }}"
                        class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
                        Setup MPIN <i class="fas fa-lock ml-2 mt-0.5"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // --- API ROUTES ---
        const ROUTES = {
            step1: "{{ route('admin.setup.step1') }}",
            sendOtp: "{{ route('admin.setup.sendOtp') }}",
            verify: "{{ route('admin.setup.verify') }}"
        };

        // --- STEP 1 LOGIC ---
        document.getElementById('form-step-1').addEventListener('submit', async function (e) {
            e.preventDefault();
            setLoading(true);

            try {
                const formData = new FormData(this);
                const response = await axios.post(ROUTES.step1, formData);

                if (response.data.success) {
                    goToStep(2);
                }
            } catch (error) {
                showError(error);
            } finally {
                setLoading(false);
            }
        });

        // --- STEP 2 LOGIC ---
        let otpTimerInterval;
        let timeLeft = 0;

        async function sendOtp() {
            const email = document.getElementById('email-input').value;
            if (!email || !email.includes('@')) {
                Swal.fire('Error', 'Please enter a valid email address', 'error');
                return;
            }

            setLoading(true);
            try {
                const response = await axios.post(ROUTES.sendOtp, { email: email });
                if (response.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent',
                        text: 'Please check your inbox (and spam folder)',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    document.getElementById('otp-section').style.display = 'block';
                    document.getElementById('email-input').readOnly = true;
                    document.getElementById('btn-send-otp').disabled = true;
                    document.getElementById('btn-send-otp').classList.add('opacity-50', 'cursor-not-allowed');
                    startTimer(60);
                } else {
                    Swal.fire('Error', response.data.message, 'error');
                }
            } catch (error) {
                showError(error);
            } finally {
                setLoading(false);
            }
        }

        function startTimer(seconds) {
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
                    const btn = document.getElementById('btn-send-otp');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.textContent = 'Resend Code';
                }
            }, 1000);
        }

        async function checkAutoSubmit(input) {
            if (input.value.length === 6) {
                const email = document.getElementById('email-input').value;
                const code = input.value;

                setLoading(true);
                try {
                    const response = await axios.post(ROUTES.verify, { email: email, code: code });
                    if (response.data.success) {
                        goToStep(3);
                    } else {
                        Swal.fire('Invalid OTP', 'The code you entered is incorrect.', 'error');
                        input.value = '';
                    }
                } catch (error) {
                    showError(error);
                    input.value = '';
                } finally {
                    setLoading(false);
                }
            }
        }

        // --- NAVIGATION ---
        function goToStep(step) {
            // Hide all contents
            // document.querySelectorAll('[id^="step-content-"]').forEach(el => el.style.display = 'none'); // Logic fix: ensure IDs match
            // Actually my HTML used id="step-1", "step-2". Previous code used querySelectorAll('[id^="step-"]'). match step-\d.

            // Hide all
            document.querySelectorAll('[id^="step-"]').forEach(el => {
                if (el.id.match(/^step-\d$/)) el.style.display = 'none';
            });
            // Show target
            document.getElementById(`step-${step}`).style.display = 'block';

            // Update Indicators
            const updateState = (id, state, number) => {
                const el = document.getElementById(id);
                // Reset basic classes
                el.className = 'step-indicator transition-all duration-300';

                if (state === 'completed') {
                    el.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white');
                    el.innerHTML = '<i class="fas fa-check"></i>';
                } else if (state === 'active') {
                    el.classList.add('border-indigo-600', 'text-indigo-600', 'bg-white');
                    el.innerText = number;
                } else {
                    // pending
                    el.classList.add('border-gray-200', 'text-gray-400', 'bg-white');
                    el.innerText = number;
                }
            };

            // Logic
            updateState('step-indicator-1', step > 1 ? 'completed' : (step === 1 ? 'active' : 'pending'), '1');

            const line1 = document.getElementById('step-line-1');
            if (step > 1) {
                line1.classList.remove('bg-gray-200');
                line1.classList.add('bg-indigo-600');
            } else {
                line1.classList.add('bg-gray-200');
                line1.classList.remove('bg-indigo-600');
            }

            updateState('step-indicator-2', step > 2 ? 'completed' : (step === 2 ? 'active' : 'pending'), '2');

            const line2 = document.getElementById('step-line-2');
            if (step > 2) {
                line2.classList.remove('bg-gray-200');
                line2.classList.add('bg-indigo-600');
            } else {
                line2.classList.add('bg-gray-200');
                line2.classList.remove('bg-indigo-600');
            }

            updateState('step-indicator-3', step === 3 ? 'active' : 'pending', '3'); // Step 3 never 'completed' in this wizard view context (it's the end)
        }

        // --- HELPER: Get Active Step ---
        function getActiveStepNumber() {
            if (document.getElementById('step-1').style.display !== 'none') return 1;
            if (document.getElementById('step-2').style.display !== 'none') return 2;
            if (document.getElementById('step-3').style.display !== 'none') return 3;
            return 1;
        }

        function setLoading(isLoading) {
            const stepNum = getActiveStepNumber();
            const indicator = document.getElementById(`step-indicator-${stepNum}`);
            const btn = document.querySelector(`#step-${stepNum} button[type="submit"], #step-${stepNum} button[type="button"].btn-loading-target`);

            if (isLoading) {
                // Store original text
                if (!indicator.dataset.originalText) {
                    indicator.dataset.originalText = indicator.innerText;
                }
                // Show Spinner
                indicator.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                indicator.classList.add('border-indigo-600', 'text-indigo-600');

                // Disable button
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                }
            } else {
                // Restore text
                if (indicator.dataset.originalText) {
                    indicator.innerText = indicator.dataset.originalText;
                }
                // Button restore handled by logic or page transition usually, but safe to revert
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            }
        }

        function showError(error) {
            const msg = error.response?.data?.message || 'An unexpected error occurred.';
            Swal.fire('Error', msg, 'error');

            // Validation errors
            if (error.response?.data?.errors) {
                const errors = Object.values(error.response.data.errors).flat().join('\n');
                Swal.fire('Validation Error', errors, 'warning');
            }
        }
        // --- PASSWORD VALIDATION ---
        const passwordInput = document.querySelector('input[name="password"]');
        const confirmInput = document.querySelector('input[name="password_confirmation"]');
        const submitBtn = document.getElementById('step1-submit-btn');

        function validatePassword() {
            const val = passwordInput.value;
            const confirmVal = confirmInput.value;

            const checks = {
                length: val.length >= 8,
                lower: /[a-z]/.test(val),
                upper: /[A-Z]/.test(val),
                number: /[0-9]/.test(val),
                special: /[^A-Za-z0-9]/.test(val),
                match: val.length > 0 && val === confirmVal
            };

            let allValid = true;

            for (const key in checks) {
                const el = document.getElementById(`req-${key}`);
                const icon = el.querySelector('i');
                const isValid = checks[key];

                if (isValid) {
                    el.classList.remove('text-gray-500');
                    el.classList.add('text-green-600', 'font-medium');
                    icon.classList.remove('far', 'fa-circle');
                    icon.classList.add('fas', 'fa-check-circle');
                } else {
                    el.classList.add('text-gray-500');
                    el.classList.remove('text-green-600', 'font-medium');
                    icon.classList.add('far', 'fa-circle');
                    icon.classList.remove('fas', 'fa-check-circle');
                    allValid = false;
                }
            }

            submitBtn.disabled = !allValid;
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);
    </script>
</body>

</html>