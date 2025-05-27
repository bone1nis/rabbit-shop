<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-email', function () {
    $service = app(\App\Services\Notifications\EmailNotificationService::class);
    $recipient = ['email' => config('mail.from.address')];
    $orderData = [
        'address' => 'ул. Пушкина, дом 10',
        'phone' => '999888777',
        'status' => 'pending',
        'products' => [
            ['id' => 1, 'name' => 'Товар 1', 'price' => 100, 'quantity' => 1],
            ['id' => 2, 'name' => 'Товар 2', 'price' => 200, 'quantity' => 2],
        ],
    ];

    $sent = $service->send($recipient, $orderData);
    return $sent ? 'Email sent' : 'Failed to send email';
});
