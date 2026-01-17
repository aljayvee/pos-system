@extends('layouts.auth')

@section('content')
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Enter MPIN</h2>
        <p class="mt-2 text-sm text-gray-600">Please enter your 7-16 digit PIN to unlock.</p>
    </div>

    <form class="space-y-6" action="{{ route('auth.mpin.verify') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="animate-shake rounded-xl bg-red-50 border border-red-100 p-4 mb-6 relative overflow-hidden">
                <!-- Decorative accent -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500"></div>

                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 rounded-full p-2">
                            <i class="fas fa-shield-alt text-red-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-sm font-bold text-red-900">Access Denied</h3>
                        <div class="mt-1 text-sm text-red-700">
                            @if($errors->count() == 1)
                                <p>{{ $errors->first() }}</p>
                            @else
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        
                        @if(session('retry_after'))
                            <div class="mt-3 bg-red-100 rounded-lg p-2 text-center">
                                <p class="text-xs font-semibold text-red-800 uppercase tracking-wider">Try again in</p>
                                <div id="countdown-timer" class="text-xl font-mono font-bold text-red-900">
                                    {{ gmdate("i:s", session('retry_after')) }}
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    let seconds = {{ session('retry_after') }};
                                    const timerDisplay = document.getElementById('countdown-timer');
                                    const mpinInput = document.getElementById('mpin');
                                    const submitBtn = document.querySelector('button[type="submit"]');

                                    // Disable input immediately
                                    if(mpinInput) {
                                        mpinInput.disabled = true;
                                        mpinInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                                    }
                                    if(submitBtn) {
                                        submitBtn.disabled = true;
                                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                    }

                                    const interval = setInterval(function() {
                                        seconds--;
                                        
                                        // Format mm:ss
                                        let m = Math.floor(seconds / 60);
                                        let s = seconds % 60;
                                        timerDisplay.textContent = 
                                            (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);

                                        if (seconds <= 0) {
                                            clearInterval(interval);
                                            window.location.reload();
                                        }
                                    }, 1000);
                                });
                            </script>
                        @endif
                    </div>
                </div>
            </div>

            <style>
                @keyframes shake {

                    0%,
                    100% {
                        transform: translateX(0);
                    }

                    10%,
                    30%,
                    50%,
                    70%,
                    90% {
                        transform: translateX(-4px);
                    }

                    20%,
                    40%,
                    60%,
                    80% {
                        transform: translateX(4px);
                    }
                }

                .animate-shake {
                    animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
                }
            </style>
        @endif

        <div class="rounded-md shadow-sm -space-y-px">
            <div>
                <label for="mpin" class="sr-only">MPIN</label>
                <input id="mpin" name="mpin" type="password" inputmode="numeric" pattern="[0-9]*" minlength="7"
                    maxlength="16" required
                    class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg text-center tracking-[0.5em] font-bold transition-colors"
                    placeholder="•••••••" autofocus>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="text-sm">
                <a href="{{ route('auth.mpin.forgot') }}"
                    class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                    Forgot MPIN?
                </a>
            </div>
        </div>

        <div>
            <button type="submit"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-md hover:shadow-lg">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <i class="fas fa-lock text-indigo-500 group-hover:text-indigo-400"></i>
                </span>
                Unlock
            </button>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm">
        <p class="text-gray-500 mb-2">Not your account?</p>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="font-medium text-gray-600 hover:text-red-600 transition-colors">
                Logout
            </button>
        </form>
    </div>
@endsection