<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Models\Ride;

class RideStatusConsumer extends Command
{
    protected $signature = 'ride:status-consumer';
    protected $description = 'Consumer que processa as atualizações de status de corridas';

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );

        $channel = $connection->channel();
        $channel->queue_declare('ride_status', false, true, false, false);

        echo "Aguardando mensagens da fila ride_status...\n";

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);

            $ride = Ride::find($data['ride_id']);
            if (!$ride) {
                echo "Corrida não encontrada.\n";
                return;
            }

            // Atualiza o status da corrida e salva no banco de dados
            $ride->status = $data['status'];
            if ($data['status'] == 'Em Andamento') {
                $ride->driver_id = $data['driver_id'];
                $ride->data_hora_inicio = now();
            } elseif ($data['status'] == 'Finalizada') {
                $ride->data_hora_fim = now();
                $ride->valor = $this->calculateFare($ride); 
            }

            $ride->save();
            echo "Corrida {$ride->id} atualizada para status: {$data['status']}\n";
        };

        $channel->basic_consume('ride_status', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    private function calculateFare($ride)
    {
        // Implementação do cálculo do valor da corrida
        return rand(10, 100);
    }
}
