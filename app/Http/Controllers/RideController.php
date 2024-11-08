<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RideController extends Controller
{
    public function store(Request $request) {
        $validatedData = $request->validate([
            'passenger_id' => 'required|exists:passengers,id',
            'origem' => 'required|string',
            'destino' => 'required|string'
        ]);
    
        $ride = Ride::create([
            'passenger_id' => $validatedData['passenger_id'],
            'status' => 'Aguardando Motorista',
            'origem' => $validatedData['origem'],
            'destino' => $validatedData['destino'],
            'data_hora_solicitacao' => now(),
        ]);
    
        return response()->json($ride, 201);
    }  
    
    public function show($id) {
        $ride = Ride::findOrFail($id);
        return response()->json($ride);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $ride = Ride::findOrFail($id);
        $status = $request->input('status');
    
        if ($status === 'Em Andamento') {
            if ($ride->status !== 'Aguardando Motorista') {
                return response()->json(['error' => 'A corrida já está em andamento ou finalizada'], 400);
            }
        } elseif ($status === 'Finalizada') {
            if ($ride->status !== 'Em Andamento') {
                return response()->json(['error' => 'A corrida não está em andamento'], 400);
            }
        } else {
            return response()->json(['error' => 'Status inválido'], 400);
        }
    
        $publisher = new RabbitMQPublisher();
        $publisher->publishRideStatusUpdate($ride->id, $status, $request->input('driver_id'));
    
        return response()->json(['message' => 'Solicitação de atualização enviada à fila.'], 202);
    }
}
