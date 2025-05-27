<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class StockQueueListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:stock-queue-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for stock queue messages';

    public function handle(): void
    {
        [$connection, $channel, $queueName, $exchangeName, $statusRoutingKey] = $this->setupChannel();

        $channel->basic_consume($queueName, '', false, false, false, false, function ($msg) use ($channel, $exchangeName, $statusRoutingKey) {
            $this->processMessage($msg, $channel, $exchangeName, $statusRoutingKey);
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
            config("rabbitmq.host"),
            config("rabbitmq.port"),
            config("rabbitmq.user"),
            config("rabbitmq.password")
        );

        $channel = $connection->channel();

        $exchangeName = 'orders_exchange';
        $queueName = 'stock_queue';
        $routingKey = 'order_created';

        $statusQueueName = 'order_status_queue';
        $statusRoutingKey = 'order_status_updated';

        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchangeName, $routingKey);

        $channel->queue_declare($statusQueueName, false, true, false, false);
        $channel->queue_bind($statusQueueName, $exchangeName, $statusRoutingKey);

        return [$connection, $channel, $queueName, $exchangeName, $statusRoutingKey];
    }

    protected function processMessage($msg, $channel, string $exchangeName, string $statusRoutingKey): void
    {
        $orderData = json_decode($msg->body, true);

        if (!is_array($orderData) || !isset($orderData['data']['products'])) {
            logger()->warning('Invalid message format', ['body' => $msg->body]);
            $msg->ack();
            return;
        }

        $this->publishOrderStatus($channel, $exchangeName, $statusRoutingKey, [
            'order_id' => $orderData['order_id'],
            'status' => 'processing',
            'timestamp' => date('c'),
            'order_data' => array_merge($orderData['data'], ['status' => 'processing']),
        ]);

        try {
            $this->processOrder($orderData['data']['products']);

            $this->publishOrderStatus($channel, $exchangeName, $statusRoutingKey, [
                'order_id' => $orderData['order_id'],
                'status' => 'completed',
                'timestamp' => date('c'),
                'order_data' => array_merge($orderData['data'], ['status' => 'completed']),
            ]);

            logger()->info("Order successfully processed", ['order_id' => $orderData['order_id']]);
        } catch (\Throwable $e) {
            logger()->error('Error processing order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderData['order_id'] ?? null,
            ]);

            $this->publishOrderStatus($channel, $exchangeName, $statusRoutingKey, [
                'order_id' => $orderData['order_id'] ?? null,
                'status' => 'cancelled',
                'error' => $e->getMessage(),
                'timestamp' => date('c'),
                'order_data' => isset($orderData['data']) ? array_merge($orderData['data'], ['status' => 'cancelled']) : null,
            ]);
        }

        $msg->ack();
    }

    protected function processOrder(array $products): void
    {
        DB::beginTransaction();

        try {
            $lockedItems = [];

            foreach ($products as $product) {
                $productId = $product['id'];
                $stockItem = Product::lockForUpdate()->find($productId);

                if (!$stockItem) {
                    throw new \Exception("Product not found: ID {$productId}");
                }

                if ($product["name"] !== $stockItem->name || $product["price"] !== $stockItem->price) {
                    throw new \Exception("Outdated product data: ID {$productId}");
                }

                if ($stockItem->quantity < $product['quantity']) {
                    throw new \Exception("Insufficient stock: ID {$productId}");
                }

                $lockedItems[$productId] = $stockItem;
            }

            foreach ($products as $product) {
                $stockItem = $lockedItems[$product['id']];
                $stockItem->quantity -= $product['quantity'];
                $stockItem->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function publishOrderStatus($channel, string $exchangeName, string $routingKey, array $statusMessage): void
    {
        $msgBody = json_encode($statusMessage);
        $msg = new AMQPMessage($msgBody, [
            'content_type' => 'application/json',
            'delivery_mode' => 2,
        ]);

        $channel->basic_publish($msg, $exchangeName, $routingKey);
    }
}
