<?php
namespace App\Jobs;

use App\Events\DriverAvailable;
use App\Models\Ride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRideStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ride;
    protected $status;
    protected $driverId;

    /**
     * Cria uma nova instância do Job.
     */
    public function __construct(Ride $ride, string $status, ?int $driverId = null)
    {
        $this->ride = $ride;
        $this->status = $status;
        $this->driverId = $driverId;
    }

    /**
     * Executa a atualização do status da corrida.
     */
    public function handle()
    {
        if ($this->status === 'Em Andamento' && $this->ride->status === 'Aguardando Motorista') {
            $this->ride->update([
                'status' => 'Em Andamento',
                'driver_id' => $this->driverId,
                'data_hora_inicio' => now(),
            ]);
        } elseif ($this->status === 'Finalizada' && $this->ride->status === 'Em Andamento') {
            $this->ride->update([
                'status' => 'Finalizada',
                'data_hora_fim' => now(),
                'valor' => $this->ride->valor,
            ]);
    
            // Libera o motorista para novas corridas
            event(new DriverAvailable($this->ride->driver_id));
        }
    }
}
