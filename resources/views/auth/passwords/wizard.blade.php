<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reset Password - POS System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .step-transition {
            transition: all 0.3s ease-in-out;
        }

        /* Step Wizard Styling */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            border-width: 2px;
            border-style: solid;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            border-color: #4f46e5;
            color: #4f46e5;
            background-color: white;
        }

        .step-indicator.completed {
            border-color: #4f46e5;
            background-color: #4f46e5;
            color: white;
        }

        .step-indicator.pending {
            border-color: #e5e7eb;
            color: #9ca3af;
            background-color: white;
        }

        .step-line {
            flex: 1;
            height: 0.125rem;
            background-color: #e5e7eb;
            transition: all 0.3s ease;
        }

        .step-line.completed {
            background-color: #4f46e5;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800" x-data="passwordWizard()">

    <div class="min-h-screen flex w-full">

        <!-- LEFT SIDE: Branding (Same as Login) -->
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
                    <img src="{{ asset('images/verapos_logo.jpg') }}" alt="Logo"
                        class="h-14 w-auto mix-blend-multiply rounded-lg">
                </div>
            </div>
            <div class="relative z-10 max-w-lg">
                <h1 class="text-5xl font-bold mb-6 leading-tight">Secure Account Recovery</h1>
                <p class="text-indigo-100 text-lg leading-relaxed mb-8">
                    Follow the steps to verify your identity and restore access to your account.
                </p>
            </div>
            <div class="relative z-10 text-xs text-indigo-300">
                &copy; {{ date('Y') }} VERAPOS System.
            </div>
        </div>

        <!-- RIGHT SIDE: Wizard Content -->
        <div class="w-full lg:w-1/2 xl:w-5/12 flex items-center justify-center p-6 sm:p-12 relative">

            <div class="w-full max-w-md">
                <!-- Back Button -->
                <a href="{{ route('login') }}"
                    class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mb-8 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Login
                </a>

                <!-- Wizard Progress -->
                <div class="flex items-center justify-between mt-6 mb-8">
                    <!-- Step 1: Find -->
                    <div class="step-indicator" :class="{
                            'active': step === 1, 
                            'completed': step > 1, 
                            'pending': step < 1
                        }">
                        <i x-show="step > 1" class="fas fa-check"></i>
                        <span x-show="step <= 1">1</span>
                    </div>

                    <div class="step-line" :class="{'completed': step > 1}"></div>

                    <!-- Step 2: Verify -->
                    <div class="step-indicator" :class="{
                            'active': step >= 2 && step <= 4, 
                            'completed': step > 4, 
                            'pending': step < 2
                        }">
                        <i x-show="step > 4" class="fas fa-check"></i>
                        <span x-show="step <= 4">2</span>
                    </div>

                    <div class="step-line" :class="{'completed': step > 4}"></div>

                    <!-- Step 3: Reset -->
                    <div class="step-indicator" :class="{
                            'active': step === 5, 
                            'completed': step > 5, 
                            'pending': step < 5
                        }">
                        <i x-show="step > 5" class="fas fa-check"></i>
                        <span x-show="step <= 5">3</span>
                    </div>
                </div>

                <!-- STEP 1: Username Search -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Find your account</h2>
                    <p class="text-gray-500 mb-6">Enter your username to begin the recovery process.</p>

                    <form @submit.prevent="findAccount">
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" x-model="form.username" required autofocus
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="Enter username">
                        </div>
                        <button type="submit" :disabled="loading"
                            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all disabled:opacity-50 flex justify-center items-center">
                            <span x-show="!loading">Search Account</span>
                            <i x-show="loading" class="fas fa-circle-notch fa-spin ml-2"></i>
                        </button>
                    </form>
                </div>

                <!-- STEP 2: Identity Verification Modal (Actually in-flow for smoother UX) -->
                <div x-show="step === 2" x-transition.opacity.duration.300ms x-cloak>
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6 text-center mb-6">
                        <div
                            class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-check text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-indigo-900">Is this you?</h3>
                        <p class="text-sm text-indigo-600 mb-4">Please confirm your identity.</p>

                        <div class="bg-white rounded-lg p-4 shadow-sm text-left flex items-center justify-center">
                            <label class="flex items-center space-x-2 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="radio" x-model="verification.fullName" value="1"
                                    class="form-radio h-5 w-5 text-indigo-600">
                                <span class="text-gray-900 font-bold text-lg"
                                    x-text="user.first_name + ' ' + user.last_name"></span>
                            </label>
                        </div>
                    </div>

                    <button @click="verifyIdentity" :disabled="verification.fullName != '1'"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        Yes, this is me
                    </button>
                    <button @click="step = 1"
                        class="w-full mt-3 py-3 px-4 text-gray-500 hover:text-gray-700 font-medium">
                        Not me, go back
                    </button>
                </div>

                <!-- STEP 3: Confirm Email Sending -->
                <div x-show="step === 3" x-transition.opacity.duration.300ms x-cloak>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Verification Mode</h2>
                    <p class="text-gray-500 mb-6">We will send a security code to the email address linked to this
                        account.</p>

                    <div class="bg-gray-100 rounded-lg p-4 mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-600 shadow-sm">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Email Address</p>
                                <p class="text-gray-900 font-medium font-mono" x-text="user.masked_email"></p>
                            </div>
                        </div>
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>

                    <button @click="sendOtp" :disabled="loading"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all flex justify-center items-center">
                        <span x-show="!loading">Continue</span>
                        <i x-show="loading" class="fas fa-circle-notch fa-spin ml-2"></i>
                    </button>
                </div>

                <!-- STEP 4: OTP Entry -->
                <div x-show="step === 4" x-transition.opacity.duration.300ms x-cloak>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Enter Security Code</h2>
                    <p class="text-gray-500 mb-6">We sent a 6-digit code to <span class="font-bold text-gray-800"
                            x-text="user.masked_email"></span></p>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">One-Time Password</label>
                        <input type="text" x-model="form.code" maxlength="6"
                            @input="if(form.code.length === 6) verifyOtp()"
                            class="block w-full px-4 py-3 text-center text-2xl tracking-[0.5em] font-mono border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 uppercase transition-all"
                            placeholder="------">
                        <p class="text-xs text-center mt-2 text-gray-500">Auto-submits when 6 digits are entered</p>
                    </div>

                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-600 mb-2">Didn't receive code?</p>
                        <button @click="resendOtp" :disabled="timer > 0 || loading"
                            class="text-indigo-600 font-semibold hover:text-indigo-800 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="timer > 0">Resend in <span
                                    x-text="Math.floor(timer / 60) + ':' + (timer % 60).toString().padStart(2, '0')"></span></span>
                            <span x-show="timer === 0">Resend Code</span>
                        </button>
                    </div>

                    <button @click="verifyOtp" :disabled="loading || form.code.length < 6"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all flex justify-center items-center">
                        <span x-show="!loading">Verify Code</span>
                        <i x-show="loading" class="fas fa-circle-notch fa-spin ml-2"></i>
                    </button>
                </div>

                <!-- STEP 5: New Password -->
                <div x-show="step === 5" x-transition.opacity.duration.300ms x-cloak>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset Password</h2>
                    <p class="text-gray-500 mb-6">Create a new, strong password for your account.</p>

                    <form @submit.prevent="resetPassword">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" x-model="form.password" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="••••••••">
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" x-model="form.password_confirmation" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                placeholder="••••••••">
                        </div>

                        <button type="submit" :disabled="loading"
                            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all flex justify-center items-center">
                            <span x-show="!loading">Reset Password</span>
                            <i x-show="loading" class="fas fa-circle-notch fa-spin ml-2"></i>
                        </button>
                    </form>
                </div>

                <!-- STEP 6: Success -->
                <div x-show="step === 6" x-transition.opacity.duration.300ms class="text-center" x-cloak>
                    <div
                        class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Password Reset!</h2>
                    <p class="text-gray-500 mb-8">Your password has been successfully updated. You can now login with
                        your new credentials.</p>

                    <a href="{{ route('login') }}"
                        class="block w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow transition-all">
                        Back to Login
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        function passwordWizard() {
            return {
                step: 1,
                loading: false,
                timer: 0,
                timerInterval: null,
                user: {
                    first_name: '',
                    last_name: '',
                    masked_email: ''
                },
                verification: {
                    fullName: null
                },
                form: {
                    username: '',
                    code: '',
                    token: '',
                    password: '',
                    password_confirmation: ''
                },

                // STEP 1: Find Account
                findAccount() {
                    this.loading = true;
                    axios.post('{{ route('password.search') }}', { username: this.form.username })
                        .then(response => {
                            if (response.data.found) {
                                this.user = response.data.user;
                                this.step = 2; // Move to Verification Modal
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'User not found', 'error');
                        })
                        .finally(() => this.loading = false);
                },

                // STEP 2: Verify Identity (Client-side Checkbox Check)
                verifyIdentity() {
                    if (this.verification.fullName) {
                        this.step = 3; // Move to Email Confirmation
                    }
                },

                // STEP 3: Send OTP
                sendOtp() {
                    this.loading = true;
                    axios.post('{{ route('password.sendOtp') }}', { username: this.form.username })
                        .then(response => {
                            this.step = 4;
                            this.startTimer(120); // 2 minutes
                            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                            Toast.fire({ icon: 'success', title: 'Code sent successfully' });
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Failed to send OTP. Please try again.', 'error');
                        })
                        .finally(() => this.loading = false);
                },

                // STEP 4: Verify OTP
                verifyOtp() {
                    this.loading = true;
                    axios.post('{{ route('password.verifyOtp') }}', {
                        username: this.form.username,
                        code: this.form.code
                    })
                        .then(response => {
                            this.form.token = response.data.token;
                            this.step = 5; // Move to Password Reset
                        })
                        .catch(error => {
                            Swal.fire('Invalid Code', error.response?.data?.message || 'The code entered is incorrect.', 'error');
                            this.form.code = ''; // Clear code on error
                        })
                        .finally(() => this.loading = false);
                },

                // Utility: Timer
                startTimer(seconds) {
                    this.timer = seconds;
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        if (this.timer > 0) this.timer--;
                        else clearInterval(this.timerInterval);
                    }, 1000);
                },

                resendOtp() {
                    this.sendOtp();
                },

                // STEP 5: Reset Password
                resetPassword() {
                    if (this.form.password !== this.form.password_confirmation) {
                        Swal.fire('Error', 'Passwords do not match', 'error');
                        return;
                    }

                    this.loading = true;
                    axios.post('{{ route('password.wizard.reset') }}', {
                        username: this.form.username,
                        token: this.form.token,
                        password: this.form.password,
                        password_confirmation: this.form.password_confirmation
                    })
                        .then(response => {
                            this.step = 6; // Success
                        })
                        .catch(error => {
                            Swal.fire('Error', error.response?.data?.message || 'Failed to reset password.', 'error');
                        })
                        .finally(() => this.loading = false);
                }
            }
        }
    </script>
</body>

</html>