<?php

namespace App\Console\Commands;

use App\Jobs\UpdateRideStatusJob;
use App\Models\Ride;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RideRequestConsumer
{
    protected $connection;
    protected $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();

        // Declarando a fila de corridas
        $this->channel->queue_declare('ride_requests', false, true, false, false, false, [
            'x-max-priority' => ['I', 10]
        ]);
    }

    public function consume()
    {
        // Consumindo a fila de corrida
        $callback = function($msg) {
            $rideData = json_decode($msg->body, true);

            $ride = Ride::find($rideData['id']);
            $driverId = $rideData['driver_id'];

            $motoristaOcupado = Ride::where('driver_id', $driverId)
                ->where('status', 'Em Andamento')
                ->exists();

            // O motorista está ocupado, a corrida vai permanecer na fila
            if ($motoristaOcupado) {
                $this->channel->basic_publish($msg, '', 'ride_requests');
                return;
            }

            $ride = Ride::find($rideData['id']);
            $ride->update([
                'status' => 'Em Andamento',
                'driver_id' => $rideData['driver_id'],
                'valor' => isset($rideData['valor']),
                'data_hora_inicio' => now(),
            ]);

            UpdateRideStatusJob::dispatch($ride);
        };

        // Consumindo a fila
        $this->channel->basic_consume('ride_requests', '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
