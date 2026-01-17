@extends('layouts.auth')

@section('content')
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Reset MPIN</h2>
        <p class="mt-2 text-sm text-gray-600">Verify your identity to proceed.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Verification Failed</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('auth.mpin.reset.verify') }}" method="POST" class="space-y-6">
        @csrf

        {{-- 1. Email (Readonly & Obfuscated) --}}
        @php
            $email = Auth::user()->email;
            $parts = explode('@', $email);
            $username = $parts[0];
            $domain = $parts[1];
            
            // Show first 3 chars, mask the rest until last char if long enough
            $visible = substr($username, 0, 3);
            $masked = $visible . str_repeat('*', max(0, strlen($username) - 3));
            $displayEmail = $masked . '@' . $domain;
        @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="text" value="{{ $displayEmail }}" readonly
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed focus:ring-0">
            <input type="hidden" name="email" value="{{ $email }}">
        </div>

        {{-- 2. Password --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
            <div class="relative">
                <input type="password" name="password" required
                    class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                    placeholder="Enter your login password">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-key"></i>
                </div>
            </div>
        </div>

        {{-- 3. OTP Verification --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
            <div class="flex gap-2">
                <div class="relative flex-grow">
                    <input type="text" name="otp" maxlength="6" inputmode="numeric" required
                        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-center tracking-[0.5em] font-bold text-lg focus:ring-indigo-500 focus:border-indigo-500 type-otp"
                        placeholder="000000">
                </div>
                <button type="button" onclick="sendOtp(this)"
                    class="flex-shrink-0 px-4 py-3 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 font-medium transition-colors text-sm whitespace-nowrap">
                    Send Code
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Check your email for the 6-digit code.</p>
        </div>

        <button type="submit"
            class="w-full py-3 px-4 border border-transparent rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-medium shadow-sm transition-all hover:shadow-md">
            Verify & Proceed
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('auth.mpin.login') }}" class="text-indigo-600 hover:underline text-sm font-medium">Back to MPIN Login</a>
    </div>

    <script>
        function sendOtp(btn) {
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Sending...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            fetch('{{ route("auth.mpin.reset.send.otp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Success State
                    btn.innerText = 'Sent!';
                    btn.classList.remove('border-indigo-600', 'text-indigo-600', 'hover:bg-indigo-50');
                    btn.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                    
                    // Focus the OTP input
                    document.querySelector('.type-otp').focus();

                    // Re-enable after 60s
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerText = 'Resend Code';
                        btn.classList.remove('opacity-75', 'cursor-not-allowed', 'bg-green-50', 'text-green-700', 'border-green-200');
                        btn.classList.add('border-indigo-600', 'text-indigo-600', 'hover:bg-indigo-50');
                    }, 60000);
                } else {
                    alert('Error sending OTP');
                    resetBtn(btn, originalText);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network Error');
                resetBtn(btn, originalText);
            });
        }

        function resetBtn(btn, text) {
            btn.disabled = false;
            btn.innerText = text;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    </script>
@endsection