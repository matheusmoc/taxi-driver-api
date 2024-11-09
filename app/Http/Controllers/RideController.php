<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use App\Models\Ride;
use Illuminate\Http\Request;
use App\Jobs\UpdateRideStatusJob;
use PhpAmqpLib\Connection\AMQPStreamConnection as ConnectionAMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage as MessageAMQPMessage;

class RideController extends Controller
{
    protected $connection;
    protected $channel;

    public function __construct()
    {
        $this->connection = new ConnectionAMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();

        $this->channel->queue_declare('ride_requests', false, true, false, false, false, [
            'x-max-priority' => ['I', 10]
        ]);
    }

    public function index(Request $request)
    {
        $rides = Ride::orderBy('id', 'desc')->get();
    
        return $rides->isEmpty()
            ? response()->json(['error' => 'Sem corridas no momento'], 404)
            : response()->json($rides, 200);
    }


    public function store(Request $request)
    {
        $passenger = Passenger::findOrFail($request->passenger_id);
    
        // Verificar se o motorista está com uma corrida ativa
        $motoristaOcupado = Ride::where('driver_id', $request->driver_id)
            ->where('status', 'Em Andamento')
            ->exists();
    
        // Se o motorista estiver ocupado, adicionar a corrida à fila
        if ($motoristaOcupado) {
            $ride = Ride::create([
                'passenger_id' => $passenger->id,
                'status' => 'Aguardando Motorista',
                'origem' => $request->origem,
                'destino' => $request->destino,
                'valor' => $request->valor,
                'data_hora_solicitacao' => now(),
            ]);
    
            // Adicionar a corrida na fila do RabbitMQ com prioridade
            $priority = $request->input('priority', 5);
            $msg = new MessageAMQPMessage(json_encode($ride), [
                'priority' => $priority
            ]);
            $this->channel->basic_publish($msg, '', 'ride_requests');
    
            return response()->json([
                'message' => 'Motorista ocupado, corrida colocada na fila para processamento posterior.',
                'ride' => $ride
            ], 202);
        }
    
        // Caso o motorista não esteja ocupado, cria a corrida normalmente
        $ride = Ride::create([
            'passenger_id' => $passenger->id,
            'status' => 'Em Andamento',
            'driver_id' => $request->driver_id,
            'origem' => $request->origem,
            'destino' => $request->destino,
            'valor' => $request->valor,
            'data_hora_solicitacao' => now(),
            'data_hora_inicio' => now(),
        ]);
        $valor = (float) $request->valor;
    
        UpdateRideStatusJob::dispatch($ride, 'Em Andamento', $request->driver_id, $valor);
    
        return response()->json($ride, 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $ride = Ride::findOrFail($id);
        $status = $request->input('status');
        $driverId = $request->input('driver_id');
        $valor = $request->input('valor');
    
        if ($status === 'Em Andamento' && !$valor) {
            return response()->json(['error' => 'O valor da corrida é obrigatório.'], 400);
        }
    
        $ride->update(['status' => $status, 'valor' => $valor]);
        UpdateRideStatusJob::dispatch($ride, $status, $driverId);
    
        return response()->json(['message' => 'Status da corrida em atualização.'], 202);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
