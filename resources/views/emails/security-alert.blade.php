@component('mail::message')
# Security Alert

Hello {{ $user->name }},

We've detected {{ $alertType }} on your account.

@if($alertType === 'suspicious_activity')
**Suspicious Activity Details:**
- IP Address: {{ request()->ip() }}
- Timestamp: {{ now() }}

For your safety, we recommend:
- Checking your recent account activity
- Changing your password
- Enabling two-factor authentication
@endif

If you did not recognize this activity, please contact our support team immediately.

@component('mail::button', ['url' => config('app.url').'/security'])
Review Account Security
@endcomponent

Best regards,  
{{ config('app.name') }} Security Team
@endcomponent