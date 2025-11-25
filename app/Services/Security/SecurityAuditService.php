<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\SecurityAuditLog;
use App\Models\FailedLogin;
use App\Mail\SecurityAlertMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityAuditService
{
    public function logEvent(
        User $user, 
        string $type, 
        array $metadata = []
    ): SecurityAuditLog {
        return SecurityAuditLog::log($user, $type, $metadata);
    }

    public function recordFailedLogin(string $email): FailedLogin
    {
        return FailedLogin::record($email);
    }

    public function detectUnusualActivity(User $user)
    {
        $recentEvents = SecurityAuditLog::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays(7))
            ->get();

        $eventTypes = $recentEvents->pluck('event_type')->unique();

        if ($eventTypes->count() > 5) {
            Log::warning('Unusual activity detected', [
                'user_id' => $user->id,
                'event_types' => $eventTypes->toArray()
            ]);

            $this->triggerSecurityAlert($user, $eventTypes->toArray());
        }
    }

    protected function triggerSecurityAlert(User $user, array $eventTypes)
    {
        // Блокировка аккаунта
        $user->update([
            'is_account_locked' => true
        ]);

        // Отправка email
        Mail::to($user->email)->send(
            new SecurityAlertMail($user, 'suspicious_activity', [
                'event_types' => $eventTypes
            ])
        );

        // Логирование события
        SecurityAuditLog::log(
            $user, 
            'account_locked', 
            ['reason' => 'Unusual activity detected']
        );
    }
}