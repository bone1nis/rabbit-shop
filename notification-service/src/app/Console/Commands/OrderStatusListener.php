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
    protected $description = 'Listen for order status updates';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password')
        );

        $channel = $connection->channel();

        $exchangeName = 'orders_exchange';
        $statusQueueName = 'order_status_queue';
        $statusRoutingKey = 'order_status_updated';

        $channel->exchange_declare($exchangeName, 'direct', false, true, false);
        $channel->queue_declare($statusQueueName, false, true, false, false);
        $channel->queue_bind($statusQueueName, $exchangeName, $statusRoutingKey);

        $this->info("Listening for order status updates...");

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            $orderData = $data['order_data'];
            var_dump($orderData);

            if (!$data) {
                $this->warn('Invalid order status message received');
                $msg->ack();
                return;
            }

            if (isset($orderData['email'])) {
                Mail::to($orderData['email'])->send(new OrderNotificationMail($orderData));
                $this->info("Email sent to {$orderData['email']} for order {$orderData['id']}");
            } else {
                $this->warn('Customer email not found in order data');
            }

            $msg->ack();
        };

        $channel->basic_consume($statusQueueName, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
