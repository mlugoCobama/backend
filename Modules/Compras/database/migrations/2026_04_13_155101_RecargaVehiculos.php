<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('com_recargas_vehiculos', function (Blueprint $table) {
            $table->id();

            // Llave foránea hacia com_datos_vehiculo
            $table->Integer('vehiculo_id');
            $table->foreign('vehiculo_id')
                  ->references('id')
                  ->on('com_datos_vehiculos');

            // Campos de la recarga
            $table->date('fecha'); // fecha de la recarga
            $table->decimal('monto', 10, 2); // monto recargado
            $table->decimal('ventas_litros', 10, 2)->default(0); // litros vendidos

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('com_recargas_vehiculos');
    }
};

