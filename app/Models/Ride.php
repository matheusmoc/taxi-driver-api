<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_id',
        'driver_id',
        'status',
        'origem',
        'destino',
        'data_hora_solicitacao',
        'data_hora_inicio',
        'data_hora_fim',
        'valor',
    ];

    /**
     * Relacionamento com o passageiro.
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Relacionamento com o motorista.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
