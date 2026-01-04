@extends('layouts.auth')

@section('content')
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Reset MPIN</h2>
        <p class="mt-2 text-sm text-gray-600">Enter your account password to verify your identity.</p>
    </div>

    <form class="space-y-6" action="{{ route('auth.mpin.reset') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="animate-shake rounded-xl bg-red-50 border border-red-100 p-4 mb-6 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-red-500"></div>
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 rounded-full p-2">
                            <i class="fas fa-lock-open text-red-500 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-sm font-bold text-red-900">Verification Failed</h3>
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
                    </div>
                </div>
            </div>
            <style>
                .animate-shake {
                    animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both
                }

                @keyframes shake {

                    0%,
                    100% {
                        transform: translateX(0)
                    }

                    10%,
                    30%,
                    50%,
                    70%,
                    90% {
                        transform: translateX(-4px)
                    }

                    20%,
                    40%,
                    60%,
                    80% {
                        transform: translateX(4px)
                    }
                }
            </style>
        @endif

        <div class="space-y-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Account Password</label>
                <input id="password" name="password" type="password" required
                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-base transition-colors"
                    autofocus>
            </div>

            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400 text-xs">SET NEW MPIN</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            <div>
                <label for="mpin" class="block text-sm font-medium text-gray-700 mb-1">New MPIN (7-16 digits)</label>
                <input id="mpin" name="mpin" type="password" inputmode="numeric" pattern="[0-9]*" minlength="7"
                    maxlength="16" required
                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg text-center tracking-[0.5em] font-bold transition-colors"
                    placeholder="•••••••">
            </div>
            <div>
                <label for="mpin_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New MPIN</label>
                <input id="mpin_confirmation" name="mpin_confirmation" type="password" inputmode="numeric" pattern="[0-9]*"
                    minlength="7" maxlength="16" required
                    class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-lg text-center tracking-[0.5em] font-bold transition-colors"
                    placeholder="•••••••">
            </div>
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all hover:shadow-lg">
                Reset MPIN
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('auth.mpin.login') }}"
                class="font-medium text-indigo-600 hover:text-indigo-500 text-sm transition-colors">
                Back to MPIN Login
            </a>
        </div>
    </form>
@endsection