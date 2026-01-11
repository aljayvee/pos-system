@component('mail::message')
# System Integrity Report

**Date:** {{ now()->format('F d, Y h:i A') }}

@if($report['status'] === 'OK')

    @component('mail::panel')
    ## ✅ SYSTEM SECURE
    All activity logs are verified and intact.
    @endcomponent

    ### External Anchor
    Save this hash to verify future integrity:
    <code style="background: #eee; padding: 5px; border-radius: 4px; display: block; overflow-wrap: break-word;">
        {{ substr(md5(serialize($report)), 0, 32) }}... (Head Hash)
    </code>

@else

    @component('mail::panel')
    ## 🚨 TAMPERING DETECTED
    **CRITICAL ALERT:** The system database has been modified unauthorized.
    @endcomponent

    ### Forensic Details

    @component('mail::table')
    | Metric | Value |
    |:--- |:--- |
    | **Log ID** | {{ $report['log_id'] }} |
    | **Reason** | <span style="color: red;">{{ $report['reason'] }}</span> |
    | **User** | {{ $report['user'] ?? 'Unknown' }} |
    | **Date** | {{ $report['date'] ?? 'N/A' }} |
    @endcomponent

    @component('mail::button', ['url' => route('logs.index'), 'color' => 'error'])
    Investigate Breach
    @endcomponent

@endif

Thanks,<br>
{{ config('app.name') }} Security Bot
Don't reply to this email.
@endcomponent