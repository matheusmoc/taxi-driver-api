<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('passengers');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->enum('status', ['Aguardando Motorista', 'Em Andamento', 'Finalizada']);
            $table->string('origem');
            $table->string('destino');
            $table->timestamp('data_hora_solicitacao')->nullable();
            $table->timestamp('data_hora_inicio')->nullable();
            $table->timestamp('data_hora_fim')->nullable();
            $table->decimal('valor', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
