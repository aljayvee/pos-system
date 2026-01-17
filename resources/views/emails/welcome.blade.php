<x-mail::message>
    # Welcome to VeraPOS!

    Hi {{ $name }},

    Congratulations! Your account has been successfully set up.
    Your Store, **Master Store**, is ready for business.

    Your MPIN has been securely configured. You can now use it to authorize transactions and access sensitive features.

    <x-mail::button :url="route('admin.dashboard')">
        Go to Dashboard
    </x-mail::button>

    If you have any questions, feel free to contact our support team.

    Thanks,<br>
    {{ config('app.name') }} Team
</x-mail::message>