<?php

namespace App\Listeners;

use App\Events\DriverAvailable;
use App\Jobs\UpdateRideStatusJob;
use App\Models\Ride;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignNewRideToDriver implements ShouldQueue
{
    public function handle(DriverAvailable $event)
    {
        // Verificar se o motorista está disponível para uma nova corrida
        $ride = Ride::where('status', 'Aguardando Motorista')
            ->whereNull('driver_id')
            ->first();

        // Atribuir ao motorista e iniciar a corrida
        if ($ride) {
            $ride->update(['driver_id' => $event->driverId]);
            UpdateRideStatusJob::dispatch($ride, 'Em Andamento', $event->driverId);
        }
    }
}
