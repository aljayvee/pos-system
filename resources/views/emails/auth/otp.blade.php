@component('mail::message')
# Hello, {{ $name }}

You requested to **{{ $action }}**.

Please use the following code to proceed. This code will expire in 10 minutes.

@component('mail::panel')
<div style="text-align: center; font-size: 32px; letter-spacing: 5px; font-weight: bold; color: #2d3748;">
    {{ $code }}
</div>
@endcomponent

If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name') }} Security
@endcomponent