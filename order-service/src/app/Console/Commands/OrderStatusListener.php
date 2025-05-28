<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
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
    protected $description = 'Listen to order status updates and update order status in DB';

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
        $queueName = 'order_status-update_queue';
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
            logger()->warning('Invalid message format in order status listener', ['body' => $msg->body]);
            $msg->ack();
            return;
        }

        $orderId = $data['order_id'];
        $order = Order::find($orderId);

        if (!$order) {
            logger()->warning("Order not found with ID: {$orderId}");
            $msg->ack();
            return;
        }

        try {
            $this->updateOrderStatus($order, $data);
            logger()->info("Order status updated", [
                'order_id' => $orderId,
                'new_status' => $data['status'],
            ]);
        } catch (\Throwable $e) {
            $order->error_message = $e->getMessage();
            $order->save();

            logger()->error('Error updating order status', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }

        $msg->ack();
    }

    protected function validateMessageData(?array $data): bool
    {
        return is_array($data) && isset($data['order_id'], $data['status']);
    }

    protected function updateOrderStatus(Order $order, array $data): void
    {
        $order->status = $data['status'];
        if (!empty($data['error'])) {
            $order->error_message = $data['error'];
        }
        $order->save();
    }
}
