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
        Schema::create('ucoip_servicios', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->integer('intercompania');
            $table->foreignId('proveedor_id');
            $table->foreignId('tipo_servicio_id');

            $table->string('nombre');
            $table->text('descripcion')->nullable();

            // Identificador externo (dominio, número, ID cloud, etc.)
            $table->string('identificador_externo')->nullable();

            // Costos
            $table->decimal('costo_base', 12, 2);
            $table->string('moneda', 10)->default('MXN');

            // Periodicidad
            $table->integer('periodicidad');

            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            // Día de pago (ej: 5 de cada mes)
            $table->unsignedTinyInteger('dia_pago')->nullable();

            
            $table->unsignedTinyInteger('dia_corte')->nullable();

            // Control
            $table->boolean('renovable')->default(true);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices
            $table->index('tipo_servicio_id');
            $table->index('proveedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucoip_servicios');
    }
};
