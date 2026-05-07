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
        Schema::create('ucoip_pagos_programados', function (Blueprint $table) {
            $table->id();
            // Relación con servicio
            $table->foreignId('servicio_id');
            // Fechas clave
            $table->date('fecha_programada'); 
            $table->date('fecha_limite')->nullable(); 
            // Monto esperado
            $table->decimal('importe', 12, 2);
            // Estado del pago
            $table->integer('estado')->default(1);
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamps();

            $table->index(['servicio_id', 'fecha_programada']);
            $table->index('estado');
            $table->index('fecha_programada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucoip_pagos_programados');
    }
};
