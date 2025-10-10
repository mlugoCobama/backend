<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Compras\Models\DatosVehiculo;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('pv_observaciones_vehiculos');
        Schema::create('pv_observaciones_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->mediumText('observaciones');
            $table->integer('datos_vehiculo_id');
            $table->foreign('datos_vehiculo_id')->references('id')->on('com_datos_vehiculos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pv_observaciones_vehiculos');
    }
};
