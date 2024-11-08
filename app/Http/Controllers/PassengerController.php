<?php

namespace App\Http\Controllers;

use App\Models\Passenger;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    public function store(Request $request) {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:15',
        ]);
    
        $passenger = Passenger::create($validatedData);
        return response()->json($passenger, 201);
    }
}
