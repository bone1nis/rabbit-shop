<?php

namespace App\Services;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected AMQPStreamConnection $connection;
    protected AMQPChannel $channel;
    protected string $exchangeName = 'orders_exchange';
    protected string $exchangeType = 'direct';

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            config("rabbitmq.host"),
            config("rabbitmq.port"),
            config("rabbitmq.user"),
            config("rabbitmq.password"),
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare(
            $this->exchangeName,
            $this->exchangeType,
            false,
            true,
            false
        );
    }

    public function publish($data, string $routingKey = 'order_created'): void
    {
        $msg = new AMQPMessage(json_encode($data), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        $this->channel->basic_publish($msg, $this->exchangeName, $routingKey);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
