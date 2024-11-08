<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'carro',
        'telefone',
    ];

    /**
     * Relacionamento com corridas.
     */
    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
