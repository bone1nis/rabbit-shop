<?php

namespace app\Services\Notifications;

use App\Mail\OrderNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Services\Notifications\NotificationServiceInterface;
class EmailNotificationService implements NotificationServiceInterface
{
    public function send(array $recipient, array $orderData): bool
    {
        try {
            Mail::to($recipient['email'])->send(new OrderNotificationMail($orderData));
            return true;
        } catch (\Exception $e) {
            Log::error('Email send failed', ['error' => $e->getMessage(), 'recipient' => $recipient]);
            return false;
        }
    }
}
