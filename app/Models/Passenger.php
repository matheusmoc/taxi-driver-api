<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
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
