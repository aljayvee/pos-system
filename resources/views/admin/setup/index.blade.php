<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup | VERAPOS</title>

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
            background-color: #f3f4f6;
        }

        /* Shopee-like Header */
        .shopee-header {
            background-color: white;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        /* Violet Theme Overrides */
        .btn-violet {
            background-color: #7c3aed;
            /* violet-600 */
            color: white;
        }

        .btn-violet:hover {
            background-color: #6d28d9;
            /* violet-700 */
        }

        .text-violet {
            color: #7c3aed;
        }

        .border-violet {
            border-color: #7c3aed;
        }

        .bg-violet-main {
            background-color: #7c3aed;
        }

        /* Step Indicators */
        .step-circle {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            background-color: #e5e7eb;
            /* gray-200 */
            color: #9ca3af;
            /* gray-400 */
        }

        .step-circle.active {
            background-color: #7c3aed;
            color: white;
        }

        .step-circle.completed {
            background-color: #10b981;
            /* green-500 */
            color: white;
        }

        .step-line {
            height: 2px;
            background-color: #e5e7eb;
            flex: 1;
        }

        .step-line.active {
            background-color: #7c3aed;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="shopee-header">
        <div class="container mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="#" class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-violet text-violet-600">VeraPOS</span>
                </a>
                <span class="text-xl text-gray-700">Setup an account</span>
            </div>

        </div>
    </header>

    <!-- MAIN CONTENT -->
    <div class="flex-1 bg-violet-600 flex items-center justify-center p-6">

        <!-- Large Background Icon/Graphic (Optional, mimicking Shopee) -->


        <!-- FORM CARD -->
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="p-8 flex-1">
                <h2 class="text-2xl font-medium text-gray-800 mb-6" id="step-title">Setup an account</h2>

                <!-- PROGRESS (Hidden visually to match cleaner look, or small dots) -->
                <!-- We can keep it internal logic-wise, but visual can be simple -->
                <div class="flex gap-2 mb-6 justify-center">
                    <div class="h-1 w-8 rounded bg-violet-600 transition-all" id="prog-1"></div>
                    <div class="h-1 w-8 rounded bg-gray-200 transition-all" id="prog-2"></div>
                    <div class="h-1 w-8 rounded bg-gray-200 transition-all" id="prog-3"></div>
                    <div class="h-1 w-8 rounded bg-gray-200 transition-all" id="prog-4"></div>
                    <div class="h-1 w-8 rounded bg-gray-200 transition-all" id="prog-5"></div>
                </div>

                <form id="signup-form" class="space-y-5">

                    <!-- STEP 1: GMAIL INPUT -->
                    <div id="step-1" class="step-content">
                        <div>
                            <input type="email" id="input-email"
                                class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 focus:ring-1 focus:ring-violet-600 outline-none transition"
                                placeholder="Valid Gmail Address" required>
                        </div>
                        <button type="button" onclick="nextStep(1)"
                            class="w-full btn-violet py-3 rounded font-medium mt-4 shadow-sm">NEXT</button>
                    </div>

                    <!-- STEP 2: NAME -->
                    <div id="step-2" class="step-content hidden">
                        <div class="space-y-4">
                            <input type="text" name="first_name"
                                class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                placeholder="First Name" required>
                            <input type="text" name="middle_name"
                                class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                placeholder="Middle Name (Optional)">
                            <input type="text" name="last_name"
                                class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                placeholder="Last Name" required>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" onclick="prevStep(2)"
                                class="w-1/3 py-3 rounded border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium">BACK</button>
                            <button type="button" onclick="nextStep(2)"
                                class="flex-1 btn-violet py-3 rounded font-medium shadow-sm">NEXT</button>
                        </div>
                    </div>

                    <!-- STEP 3: DEMOGRAPHICS -->
                    <div id="step-3" class="step-content hidden">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
                                <input type="date" name="birthdate"
                                    class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Gender</label>
                                <select id="gender-select" name="gender"
                                    class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                    onchange="toggleCustomGender(this.value)">
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Custom">Custom</option>
                                </select>
                                <input type="text" id="custom-gender-input"
                                    class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none mt-2 hidden"
                                    placeholder="Please specify gender">
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" onclick="prevStep(3)"
                                class="w-1/3 py-3 rounded border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium">BACK</button>
                            <button type="button" onclick="nextStep(3)"
                                class="flex-1 btn-violet py-3 rounded font-medium shadow-sm">NEXT</button>
                        </div>
                    </div>

                    <!-- STEP 4: ACCOUNT -->
                    <div id="step-4" class="step-content hidden">
                        <div class="space-y-4">
                            <input type="text" name="username"
                                class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                placeholder="Preferred Username" required>

                            <div class="relative">
                                <input type="password" name="password" id="password"
                                    class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                    placeholder="Password" required oninput="validatePassword()">
                                <i class="fas fa-eye absolute right-4 top-4 text-gray-400 cursor-pointer"
                                    onclick="togglePassword('password')"></i>
                            </div>

                            <div class="relative">
                                <input type="password" name="password_confirmation" id="chk_password"
                                    class="w-full px-4 py-3 rounded border border-gray-300 focus:border-violet-600 outline-none"
                                    placeholder="Confirm Password" required oninput="validatePassword()">
                            </div>

                            <!-- Password Rules -->
                            <div class="p-3 bg-gray-50 rounded text-xs space-y-1 text-gray-500">
                                <p id="rule-length"><i class="far fa-circle"></i> Min 8 chars</p>
                                <p id="rule-lower"><i class="far fa-circle"></i> Lowercase</p>
                                <p id="rule-upper"><i class="far fa-circle"></i> Uppercase</p>
                                <p id="rule-num"><i class="far fa-circle"></i> Number</p>
                                <p id="rule-special"><i class="far fa-circle"></i> Special (!@#$%^&*)</p>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" onclick="prevStep(4)"
                                class="w-1/3 py-3 rounded border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium">BACK</button>
                            <button type="button" onclick="nextStep(4)" id="btn-step-4"
                                class="flex-1 btn-violet py-3 rounded font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">NEXT</button>
                        </div>
                    </div>

                    <!-- STEP 5: VERIFICATION -->
                    <div id="step-5" class="step-content hidden text-center">
                        <div class="mb-6">
                            <i class="fas fa-mobile-alt text-4xl text-gray-400 mb-2"></i>
                            <h3 class="text-lg font-semibold text-gray-800">OTP Verification</h3>
                            <p class="text-sm text-gray-500">Enter the 6-digit code sent to <span id="display-email"
                                    class="font-medium text-gray-700"></span></p>
                        </div>

                        <div id="otp-initial-view">
                            <button type="button" id="btn-init-otp" onclick="initiateOtp()"
                                class="btn-violet px-8 py-3 rounded font-medium shadow-sm">
                                Send Code
                            </button>
                        </div>

                        <div id="otp-entry-view" class="hidden">
                            <div class="flex justify-center gap-2 mb-4">
                                <input type="text" id="otp-input" maxlength="6"
                                    class="w-48 text-center text-2xl tracking-[0.5em] font-bold border-b-2 border-violet-600 outline-none py-2"
                                    placeholder="••••••" oninput="checkOtp(this)">
                            </div>

                            <div class="text-sm">
                                <p id="timer-text" class="text-gray-500">Resend code in <span id="time-left"
                                        class="font-bold text-violet-600">120</span>s</p>
                                <button id="btn-resend" type="button" onclick="resendOtp()"
                                    class="text-violet-600 hover:underline hidden font-medium">Resend Code</button>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="button" onclick="prevStep(5)"
                                class="text-sm text-gray-400 hover:text-gray-600">Back</button>
                        </div>
                    </div>

                    <!-- STEP 6: SUCCESS (Hidden Step for Transition) -->
                    <div id="step-6" class="step-content hidden text-center py-10">
                        <div
                            class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Success!</h2>
                        <p class="text-gray-500 mb-8">Your account has been created.</p>
                        <a href="{{ route('auth.mpin.setup') }}"
                            class="block w-full btn-violet py-3 rounded font-medium shadow-sm">Setup MPIN</a>
                    </div>

                </form>

                <div class="mt-8 text-center" id="footer-links">
                    <p class="text-xs text-gray-400">By signing up, you agree to VeraPOS's <a href="#"
                            class="text-violet-600">Terms of Service</a> & <a href="#" class="text-violet-600">Privacy
                            Policy</a></p>

                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        const ROUTES = {
            step1: "{{ route('admin.setup.step1') }}",
            sendOtp: "{{ route('admin.setup.sendOtp') }}",
            verify: "{{ route('admin.setup.verify') }}"
        };

        const formData = {};
        let currentStep = 1;
        let timerInterval;

        // --- NAVIGATION ---
        async function nextStep(step) {
            // Validation Logic
            if (step === 1) {
                const email = document.getElementById('input-email').value;
                if (!email || !email.includes('@')) {
                    return Swal.fire('Error', 'Please enter a valid Gmail address.', 'error');
                }
                formData.email = email;
            }

            if (step === 2) {
                const fname = document.querySelector('input[name="first_name"]').value;
                const lname = document.querySelector('input[name="last_name"]').value;
                if (!fname.trim() || !lname.trim()) {
                    return Swal.fire('Missing Info', 'First and Last name are required.', 'warning');
                }
                formData.first_name = fname;
                formData.middle_name = document.querySelector('input[name="middle_name"]').value;
                formData.last_name = lname;
            }

            if (step === 3) {
                const dob = document.querySelector('input[name="birthdate"]').value;
                const genderSelect = document.getElementById('gender-select').value;
                let finalGender = genderSelect;

                if (genderSelect === 'Custom') {
                    finalGender = document.getElementById('custom-gender-input').value;
                }

                if (!dob || !finalGender) {
                    return Swal.fire('Missing Info', 'Date of birth and Gender are required.', 'warning');
                }
                formData.birthdate = dob;
                formData.gender = finalGender;
            }

            if (step === 4) {
                const username = document.querySelector('input[name="username"]').value;
                const pwd = document.querySelector('input[name="password"]').value;
                if (!username.trim()) return Swal.fire('Error', 'Username required', 'warning');

                // Password logic validated by oninput, but double check
                if (!validatePassword()) return Swal.fire('Weak Password', 'Please meet all password requirements.', 'warning');

                formData.username = username;
                formData.password = pwd;
                formData.password_confirmation = document.querySelector('input[name="password_confirmation"]').value;

                // SUBMIT DATA TO BACKEND
                // We use a modified call that manages UI
                if (!await submitPersonalData()) return;
            }

            // --- DELAY & UI FEEDBACK (1.5s) ---
            const btn = document.querySelector(`#step-${step} button[onclick*="nextStep"]`) || document.getElementById('btn-step-4');
            if (btn && step !== 4) { // Step 4 handled by submitPersonalData but we want to keep it disabled
                btn.dataset.originalText = btn.innerText;
                btn.innerText = 'Processing...';
                btn.disabled = true;
            }

            await new Promise(resolve => setTimeout(resolve, 1500));

            if (btn && step !== 4) {
                btn.innerText = btn.dataset.originalText;
                btn.disabled = false;
            }

            // Transition
            showStep(step + 1);

            if (step + 1 === 5) {
                // Determine logic for OTP
                document.getElementById('display-email').innerText = formData.email;
                document.getElementById('footer-links').style.display = 'none'; // hide footer for clean OTP view

                // Reset View
                document.getElementById('otp-initial-view').classList.remove('hidden');
                document.getElementById('otp-entry-view').classList.add('hidden');
            }
        }

        function prevStep(step) {
            showStep(step - 1);
            if (step - 1 < 5) {
                document.getElementById('footer-links').style.display = 'block';
            }
        }

        function showStep(stepNum) {
            currentStep = stepNum;
            // Hide all
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            // Show target
            document.getElementById(`step-${stepNum}`).classList.remove('hidden');

            // Update Progress Bar
            for (let i = 1; i <= 5; i++) {
                const bar = document.getElementById(`prog-${i}`);
                if (i <= stepNum) {
                    bar.classList.remove('bg-gray-200');
                    bar.classList.add('bg-violet-600');
                } else {
                    bar.classList.add('bg-gray-200');
                    bar.classList.remove('bg-violet-600');
                }
            }

            // Update Header Title
            const titles = {
                1: 'Setup an account',
                2: 'Personal Info',
                3: 'Demographics',
                4: 'Account Setup',
                5: 'Verification'
            };
            if (titles[stepNum]) document.getElementById('step-title').innerText = titles[stepNum];
        }

        function toggleCustomGender(val) {
            const customInput = document.getElementById('custom-gender-input');
            if (val === 'Custom') customInput.classList.remove('hidden');
            else customInput.classList.add('hidden');
        }

        function togglePassword(id) {
            const el = document.getElementById(id);
            el.type = el.type === 'password' ? 'text' : 'password';
        }

        // --- PASSWORD VALIDATION ---
        function validatePassword() {
            const val = document.getElementById('password').value;
            const confirm = document.getElementById('chk_password').value;

            const rules = {
                length: val.length >= 8,
                lower: /[a-z]/.test(val),
                upper: /[A-Z]/.test(val),
                num: /[0-9]/.test(val),
                special: /[!@#$%^&*]/.test(val)
            };

            let validCount = 0;
            for (const [key, passed] of Object.entries(rules)) {
                const el = document.getElementById(`rule-${key}`);
                const icon = el.querySelector('i');
                if (passed) {
                    el.classList.add('text-green-600', 'font-medium');
                    el.classList.remove('text-gray-500');
                    icon.className = 'fas fa-check-circle';
                    validCount++;
                } else {
                    el.classList.remove('text-green-600', 'font-medium');
                    el.classList.add('text-gray-500');
                    icon.className = 'far fa-circle';
                }
            }

            // Match check is implicit but let's ensure it blocks 'Next'
            const btn = document.getElementById('btn-step-4');
            const isMatch = val === confirm && val.length > 0;

            const allPassed = validCount === 5 && isMatch;
            btn.disabled = !allPassed;

            return allPassed;
        }

        // --- API CALLS ---
        async function submitPersonalData() {
            try {
                // Show loading on button
                const btn = document.getElementById('btn-step-4');
                const originalText = btn.innerText;
                btn.innerText = 'Creating...';
                btn.disabled = true;

                await axios.post(ROUTES.step1, formData);

                // Keep the loading state for the delay interaction in nextStep
                // btn.innerText = originalText; 
                // btn.disabled = false;
                return true;
            } catch (error) {
                const btn = document.getElementById('btn-step-4');
                btn.innerText = 'NEXT';
                btn.disabled = false;

                const msg = error.response?.data?.message || 'Error occurred';
                Swal.fire('Error', msg, 'error');
                return false;
            }
        }

        async function initiateOtp() {
            const btn = document.getElementById('btn-init-otp');
            const originalText = btn.innerText;
            btn.innerText = 'Sending...';
            btn.disabled = true;

            try {
                await triggerOtpSend(true);
            } catch (error) {
                // Restore button if error (triggerOtpSend catches its own errors but for safety)
                btn.innerText = originalText;
                btn.disabled = false;
            }
            // If success, view switches, so button hidden anyway. But we can restore state just in case.
            btn.innerText = originalText;
            btn.disabled = false;
        }

        async function triggerOtpSend(isInitial = false) {
            try {
                await axios.post(ROUTES.sendOtp, { email: formData.email });

                if (isInitial) {
                    await Swal.fire({
                        icon: 'info',
                        title: 'Check your Inbox',
                        text: 'Please check your Gmail Inbox for the code.',
                        confirmButtonText: 'Okay',
                        confirmButtonColor: '#7c3aed'
                    });

                    // Switch views
                    document.getElementById('otp-initial-view').classList.add('hidden');
                    document.getElementById('otp-entry-view').classList.remove('hidden');
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Code Sent',
                        text: 'Please check your email.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }

                startTimer(120);
            } catch (error) {
                Swal.fire('Error', 'Failed to send OTP', 'error');
            }
        }

        function resendOtp() {
            triggerOtpSend(false);
            document.getElementById('btn-resend').classList.add('hidden');
            document.getElementById('timer-text').classList.remove('hidden');
        }

        function startTimer(seconds) {
            let left = seconds;
            const display = document.getElementById('time-left');
            const timerContainer = document.getElementById('timer-text');
            const resendBtn = document.getElementById('btn-resend');

            timerContainer.classList.remove('hidden');
            resendBtn.classList.add('hidden');

            clearInterval(timerInterval);
            display.innerText = left;

            timerInterval = setInterval(() => {
                left--;
                display.innerText = left;
                if (left <= 0) {
                    clearInterval(timerInterval);
                    timerContainer.classList.add('hidden');
                    resendBtn.classList.remove('hidden');
                }
            }, 1000);
        }

        async function checkOtp(input) {
            if (input.value.length === 6) {
                try {
                    input.disabled = true;
                    const res = await axios.post(ROUTES.verify, {
                        email: formData.email,
                        code: input.value
                    });

                    if (res.data.success) {
                        showStep(6); // Success View
                    } else {
                        throw new Error('Invalid Code');
                    }
                } catch (error) {
                    Swal.fire('Invalid', 'The code is incorrect.', 'error');
                    input.value = '';
                    input.disabled = false;
                    input.focus();
                }
            }
        }

    </script>
</body>

</html>