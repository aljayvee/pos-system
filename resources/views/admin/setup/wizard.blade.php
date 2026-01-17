<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - VeraPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Custom Glass Shadow from premium-ui.css */
        .glass-shadow { box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    {{-- Background Decoration (Abstract Shapes) --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-3xl opacity-40"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">
        
        {{-- Brand --}}
        <div class="mb-6 text-center">
            <h1 class="text-4xl font-bold tracking-tight text-indigo-700 drop-shadow-sm">
                <span class="text-slate-700">Vera</span>POS
            </h1>
        </div>

        {{-- Main Card (Facebook Structure / Premium Skin) --}}
        <div class="w-full max-w-[580px] bg-white/90 backdrop-blur-xl border border-white/60 rounded-[12px] glass-shadow overflow-hidden relative">
            
            {{-- FB Style Header --}}
            <div class="px-5 pt-4 pb-0">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-[32px] font-bold text-slate-800 leading-tight">Sign Up</h2>
                        <p class="text-slate-500 text-[15px] mt-0.5">It's quick and secure.</p>
                    </div>
                    {{-- Close Icon Placeholder (Visual Only) --}}
                    <div class="text-slate-400">
                         <div class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-bold border border-indigo-100 flex items-center gap-1">
                           <span id="step-count">1</span><span class="opacity-50">/</span><span>3</span>
                        </div>
                    </div>
                </div>
                {{-- Divider --}}
                <div class="h-[1px] bg-slate-200 mt-4 w-full"></div>
                
                {{-- Progress Bar (Subtle) --}}
                <div class="h-1 w-full bg-slate-100 mt-0">
                    <div id="progress-bar" class="h-full bg-indigo-600 transition-all duration-500 ease-out shadow-[0_0_10px_rgba(79,70,229,0.4)]" style="width: 33%"></div>
                </div>
            </div>

            <div class="p-5">
                
                {{-- STEP 1: Personal Info --}}
                <div id="step-1" class="step-content transition-all duration-300">
                    <form id="form-step-1" onsubmit="submitStep1(event)" class="space-y-3">
                        
                        {{-- Name Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="first_name" required 
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="First name">
                            
                            <input type="text" name="last_name" required 
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="Last name">
                        </div>
                        
                        <input type="text" name="middle_name" 
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="Middle name (Optional)">

                        {{-- Email --}}
                        <input type="email" name="email" required 
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="Email address">

                        {{-- Password Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <input type="password" name="password" required minlength="6" 
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="New password">
                            <input type="password" name="password_confirmation" required minlength="6" 
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 transition-all" placeholder="Confirm password">
                        </div>

                        {{-- Birthdate / Age Label --}}
                        <div class="pt-1">
                            <label class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                                Date of birth <i class="fas fa-question-circle text-slate-400 cursor-help" title="To verify you are 18+"></i>
                            </label>
                            <div class="grid grid-cols-3 gap-3 mt-1">
                                <div class="col-span-2">
                                     <input type="date" name="birthdate" required 
                                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] shadow-sm">
                                </div>
                                <div>
                                     <input type="number" name="age" required min="18" 
                                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-700 text-[15px] placeholder-slate-400 shadow-sm" placeholder="Age">
                                </div>
                            </div>
                        </div>

                        {{-- Terms Text --}}
                        <p class="text-[11px] text-slate-500 leading-tight pt-2">
                            By clicking Sign Up, you agree to our Terms, Privacy Policy and Cookies Policy. You may receive SMS notifications from us and can opt out at any time.
                        </p>

                        {{-- Button --}}
                        <div class="pt-2 flex justify-center">
                            <button type="submit" class="w-[200px] bg-gradient-to-br from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-[17px] font-bold py-2.5 px-8 rounded-[6px] shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                                Sign Up
                            </button>
                        </div>
                    </form>
                </div>


                {{-- STEP 2: Verify Gmail Logic --}}
                <div id="step-2" class="step-content hidden transition-all duration-300">
                    <div class="text-center py-6">
                        <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm border border-indigo-100">
                            <i class="fas fa-shield-alt text-3xl text-indigo-600"></i>
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Verify Identity</h3>
                        <p class="text-slate-500 text-sm mb-6 leading-relaxed px-4">
                            We need to verify your email <br>
                            <span id="display-email" class="font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded mt-1 inline-block"></span>
                        </p>

                        <div class="flex flex-col items-center space-y-3">
                            <button onclick="sendCode()" class="w-[300px] bg-gradient-to-br from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-[17px] font-bold py-2.5 px-8 rounded-[6px] shadow-md shadow-indigo-500/20 transition-all">
                                Send Verification Code
                            </button>
                            
                            <button onclick="goToStep(1)" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition-colors">
                                Edit Email Address
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: OTP Input --}}
                <div id="step-3" class="step-content hidden transition-all duration-300">
                    <div class="text-center py-4">
                        <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100/50 rounded-2xl mb-6 text-green-600 border border-green-200 shadow-sm">
                            <i class="fas fa-envelope-open-text text-2xl"></i>
                        </div>

                        <h3 class="text-xl font-bold text-slate-800 mb-2">Check your Email</h3>
                        <p class="text-slate-500 text-sm mb-6 px-4">We've sent a 6-digit code to your inbox.</p>

                        <form onsubmit="verifyAndSubmit(event)" class="max-w-[300px] mx-auto">
                            <div class="mb-6 group">
                                <input type="text" id="otp-code" maxlength="6" inputmode="numeric" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-[6px] focus:bg-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-3xl font-bold tracking-[0.4em] text-center outline-none text-slate-800 placeholder-slate-300 transition-all shadow-sm group-hover:border-slate-300" 
                                    placeholder="000000" required>
                            </div>
                            
                            <div class="flex flex-col items-center space-y-3">
                                <button type="submit" class="w-full bg-gradient-to-br from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-[17px] font-bold py-2.5 px-8 rounded-[6px] shadow-md shadow-indigo-500/20 transition-all">
                                    Complete Setup
                                </button>
                                <button type="button" onclick="sendCode()" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                    Resend Code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="mt-8 text-center text-[10px] font-semibold text-slate-400 tracking-wider uppercase">
            &copy; 2026 VeraPOS System
        </div>
    </div>
    
    <script>
        let setupData = {};
        let currentStep = 1;

        function goToStep(step) {
            currentStep = step;
            
            // Hide all
            document.querySelectorAll('.step-content').forEach(el => {
                el.classList.add('hidden');
            });

            // Show current
            const active = document.getElementById('step-' + step);
            active.classList.remove('hidden');

            // Update Header
            document.getElementById('step-count').innerText = step;
            const progress = (step === 1) ? '33%' : (step === 2 ? '66%' : '100%');
            document.getElementById('progress-bar').style.width = progress;
        }

        function submitStep1(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            if(fd.get('password') !== fd.get('password_confirmation')) {
                alert("Passwords do not match"); return;
            }

            const btn = e.target.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';

            fetch('{{ route("setup.step1") }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if(data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                } else {
                    setupData.email = fd.get('email');
                    document.getElementById('display-email').innerText = setupData.email;
                    goToStep(2);
                }
            })
            .catch(err => {
                console.error(err); alert("An error occurred");
                btn.disabled = false; btn.innerHTML = originalContent;
            });
        }

        function sendCode() {
            const btn = document.querySelector('#step-2 button, #step-3 button[onclick="sendCode()"]');
            if(!btn) return;
            
            const originalText = btn.innerText;
            btn.innerText = "Sending...";
            btn.disabled = true;

            fetch('{{ route("setup.send_otp") }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({ email: setupData.email })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = originalText;
                
                if(data.success) {
                    goToStep(3);
                } else {
                    alert(data.message || "Error sending code");
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = originalText;
                alert("Connection error");
            });
        }

        function verifyAndSubmit(e) {
            e.preventDefault();
            const code = document.getElementById('otp-code').value;
            const btn = e.target.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true; 
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Verifying...';

            fetch('{{ route("setup.verify") }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({ code: code })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || "Verification failed");
                    btn.disabled = false; 
                    btn.innerHTML = originalContent;
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                alert("Connection error");
            });
        }

        // Initialize
        goToStep(1);
    </script>
</body>
</html>
