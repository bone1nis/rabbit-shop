<?php

namespace app\Mail;

use Illuminate\Mail\Mailable;

class OrderNotificationMail extends Mailable
{
    public array $orderData;

    public function __construct(array $orderData)
    {
        $this->orderData = $orderData;
    }

    public function build(): OrderNotificationMail
    {
        return $this->subject('Обновление вашего заказа')
            ->view('emails.order_notification');
    }
}
