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
        Schema::connection('autos')->create('ren_detalle_trabajo_solicitado', function (Blueprint $table) {
            $table->id(); // id auto incremental, llave primaria
            $table->string('descripcion'); // campo descripción
            $table->string('partes'); // campo partes

            $table->integer('ren_entrada_vehiculo_id');
            $table->foreign('ren_entrada_vehiculo_id')
                  ->references('id')
                  ->on('ren_entrada_vehiculo');
            $table->timestamps(); // created_at y updated_at
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('autos')->dropIfExists('ren_detalle_trabajo_solicitado');

    }
};
