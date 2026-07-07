<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase_creditionals.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($token, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(['title' => $title, 'body' => $body]);

        return $this->messaging->send($message);
    }

    /**
     * Send the same notification to a batch of device tokens, skipping any
     * that FCM rejects (e.g. uninstalled app / stale token) instead of failing the whole batch.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            return null;
        }

        $message = CloudMessage::new()
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        try {
            $report = $this->messaging->sendMulticast($message, $tokens);

            \Log::info('FCM sendMulticast result', [
                'tokens_sent_to'   => $tokens,
                'success_count'    => $report->successes()->count(),
                'failure_count'    => $report->failures()->count(),
                'failures'         => collect($report->failures()->getItems())->map(function ($failure) {
                    return [
                        'target' => $failure->target()->value(),
                        'error'  => $failure->error() ? $failure->error()->getMessage() : null,
                    ];
                })->all(),
            ]);

            return $report;
        } catch (\Throwable $e) {
            \Log::error('FCM sendMulticast threw an exception', [
                'tokens' => $tokens,
                'error'  => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Notify every user assigned the "Admin" role on all of their registered devices.
     */
    public function notifyAdmins(string $title, string $body, array $data = [])
    {
        $tokens = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'Admin');
            })
            ->with('deviceToken')
            ->get()
            ->pluck('deviceToken')
            ->flatten()
            ->pluck('device_token')
            ->all();

        return $this->sendToTokens($tokens, $title, $body, $data);
    }
}
