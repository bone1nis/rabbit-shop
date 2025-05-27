<?php

namespace app\Services\Notifications;

interface NotificationServiceInterface
{
    public function send(array $recipient, array $orderData): bool;
}
