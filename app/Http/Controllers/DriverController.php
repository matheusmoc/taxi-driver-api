<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function store(Request $request) {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'carro' => 'required|string|max:50',
            'telefone' => 'required|string|max:15',
        ]);
    
        $driver = Driver::create($validatedData);
        return response()->json($driver, 201);
    }
    
}
