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


    public function show($id)
    {
        $ride = Ride::find($id);

        return $ride
            ? response()->json($ride, 200)
            : response()->json(['error' => 'Corrida não encontrada'], 404);
    }


    public function store(Request $request)
    {
        $request->validate([
            'passenger_id' => 'required|exists:passengers,id',
            'driver_id' => 'required|exists:drivers,id',
            'origem' => 'required|string|max:255',
            'destino' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
        ]);
    
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
    
        // Validação para garantir que a corrida existe
        if (!$ride) {
            return response()->json(['message' => 'Corrida não encontrada'], 404);
        }

        $status = $request->input('status', $ride->status);  
        $driverId = $request->input('driver_id', $ride->driver_id);  
        $valor = $request->input('valor', $ride->valor);  
    

        UpdateRideStatusJob::dispatch($ride, $status, $driverId, $valor);
    
        return response()->json(['message' => 'Dados atualizados com sucesso', 'ride' => $ride], 200);
    }
    
    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
