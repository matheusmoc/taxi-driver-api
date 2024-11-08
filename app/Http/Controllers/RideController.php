<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateRideStatusJob;
use App\Models\Passenger;
use App\Models\Ride;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function store(Request $request)
    {
        $passenger = Passenger::findOrFail($request->passenger_id);
        
        $ride = Ride::create([
            'passenger_id' => $passenger->id,
            'status' => 'Aguardando Motorista',
            'origem' => $request->origem,
            'destino' => $request->destino,
            'data_hora_solicitacao' => now(),
        ]);

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

        if ($status === 'Em Andamento' && $ride->status === 'Aguardando Motorista') {
            $ride->update([
                'status' => 'Em Andamento',
                'driver_id' => $driverId,
                'valor' => $valor,
                'data_hora_inicio' => now(),
            ]);
        } elseif ($status === 'Finalizada' && $ride->status === 'Em Andamento') {
            $ride->update([
                'status' => 'Finalizada',
                'data_hora_fim' => now(),
                'valor' => $ride->valor,  
            ]);
        }

        UpdateRideStatusJob::dispatch($ride, $status, $driverId);

        return response()->json(['message' => 'Status da corrida em atualização.'], 202);
    }
}
