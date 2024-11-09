<?php

namespace App\Jobs;

use App\Events\DriverAvailable;
use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRideStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ride;
    protected $status;
    protected $driverId;
    protected $valor;

    public function __construct(Ride $ride, string $status, ?int $driverId = null, float $valor = 0.0)
    {
        $this->ride = $ride;
        $this->status = $status;
        $this->driverId = $driverId;
        $this->valor = $valor;
    }

    /**
     * Executa a atualização do status da corrida.
     */
    public function handle()
    {
        switch ($this->status) {
            case 'Em Andamento':
                if ($this->ride->status === 'Aguardando Motorista') {
                    if (!$this->driverId) {
                        Log::warning("Driver ID is missing while changing ride status to 'Em Andamento'. Ride ID: {$this->ride->id}");
                        return;
                    }
    
                    $this->ride->update([
                        'status' => 'Em Andamento',
                        'driver_id' => $this->driverId,
                        'valor' => $this->valor > 0.0 ? $this->valor : $this->ride->valor,
                        'data_hora_inicio' => now(),
                    ]);
                }
                break;
    
            case 'Finalizada':
                if ($this->ride->status === 'Em Andamento') {
                    $this->ride->update([
                        'status' => 'Finalizada',
                        'data_hora_fim' => now(),
                        'valor' => $this->ride->valor,
                    ]);
                    
                    // Dispara o evento para liberar o motorista
                    event(new DriverAvailable($this->ride->driver_id));
                }
                break;
    
            default:
                break;
        }
    }    
}
