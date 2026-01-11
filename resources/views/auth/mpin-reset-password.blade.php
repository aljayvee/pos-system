@extends('layouts.auth')

@section('content')
    <div class="text-center mb-8">
        {{-- Success Icon --}}
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
            <i class="fas fa-check text-2xl text-green-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Enter Your New MPIN</h2>
        <p class="mt-2 text-sm text-gray-600">Verification successful. Please set your new secure MPIN.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-times-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error</h3>
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

    <form action="{{ route('auth.mpin.reset.perform') }}" method="POST" class="space-y-6">
        @csrf

        {{-- 1. New MPIN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New MPIN (7-16 digits)</label>
            <input name="mpin" type="password" inputmode="numeric" pattern="[0-9]*" minlength="7" maxlength="16" required
                autofocus
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-center tracking-[0.5em] font-bold focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                placeholder="•••••••">
        </div>

        {{-- 2. Confirm MPIN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New MPIN</label>
            <input name="mpin_confirmation" type="password" inputmode="numeric" required
                class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-center tracking-[0.5em] font-bold focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                placeholder="•••••••">
        </div>

        <button type="submit"
            class="w-full py-3 px-4 border border-transparent rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 font-medium shadow-sm transition-all hover:shadow-md flex items-center justify-center gap-2">
            <i class="fas fa-save"></i>
            Change MPIN
        </button>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 text-center text-sm">
        <p class="text-gray-500 mb-2">Want to cancel?</p>
        <a href="{{ route('auth.mpin.login') }}" class="font-medium text-gray-600 hover:text-red-600 transition-colors">
            Cancel & Return to Login
        </a>
    </div>
@endsection