<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisher
{
    protected $connection;
    protected $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );

        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('ride_status', false, true, false, false);
    }

    public function publishRideStatusUpdate($rideId, $status, $driverId = null)
    {
        $data = json_encode([
            'ride_id' => $rideId,
            'status' => $status,
            'driver_id' => $driverId
        ]);

        $msg = new AMQPMessage($data, ['delivery_mode' => 2]); // Mensagem persistente
        $this->channel->basic_publish($msg, '', 'ride_status');
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
