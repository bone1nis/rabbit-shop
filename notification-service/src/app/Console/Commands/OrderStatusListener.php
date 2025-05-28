<?php

namespace App\Console\Commands;

use App\Mail\OrderNotificationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class OrderStatusListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:order-status-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for order status updates and send notification email';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        [$connection, $channel, $queueName] = $this->setupChannel();

        $channel->basic_consume($queueName, '', false, false, false, false, function ($msg) {
            $this->processMessage($msg);
        });

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    /**
     * @throws \Exception
     */
    protected function setupChannel(): array
    {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );

        $channel = $connection->channel();

        $exchangeName = 'orders_exchange';
        $queueName = 'order_notification_queue';
        $routingKey = 'order_status_updated';

        $channel->exchange_declare($exchangeName, 'direct', false, true, false);
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchangeName, $routingKey);

        return [$connection, $channel, $queueName];
    }

    protected function processMessage($msg): void
    {
        $data = json_decode($msg->body, true);

        if (!$this->validateMessageData($data)) {
            logger()->warning('Invalid order status message received', ['body' => $msg->body]);
            $msg->ack();
            return;
        }

        $orderData = $data['order_data'];

        if (!isset($orderData['email'])) {
            logger()->warning('Customer email not found in order data', ['order_data' => $orderData]);
            $msg->ack();
            return;
        }

        try {
            Mail::to($orderData['email'])->send(new OrderNotificationMail($orderData));
            logger()->info("Email sent", ['email' => $orderData['email'], 'order_id' => $orderData['id'] ?? null]);
        } catch (\Throwable $e) {
            logger()->error('Failed to send order notification email', [
                'email' => $orderData['email'],
                'order_id' => $orderData['id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $msg->ack();
    }

    protected function validateMessageData(?array $data): bool
    {
        return isset($data['order_data']['email']) && is_array($data) && is_array($data['order_data']);
    }
}
