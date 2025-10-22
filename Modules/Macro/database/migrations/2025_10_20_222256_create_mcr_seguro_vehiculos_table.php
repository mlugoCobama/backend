<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */public function up(): void {
         Schema::create('mcr_seguro_vehiculos', function (Blueprint $table) {
             $table->unsignedBigInteger('id', true); 
             $table->string('aseguradora', 150); 
             $table->string('inciso_vehiculo', 100)->nullable(); 
             $table->string('cobertura', 150)->nullable(); 
             $table->string('inicio_vigencia', 50)->nullable(); 
             $table->string('fin_vigencia', 50)->nullable(); 
             $table->string('flotilla',50)->nullable(); 
             $table->string('inciso_flotilla', 100)->nullable(); 
             $table->string('fecha_renovacion', 50)->nullable(); 
             $table->integer('id_com_datos_vehiculo'); 
             $table->tinyInteger('activo')->default(1); 
             $table->foreign('id_com_datos_vehiculo')->references('id')->on('com_datos_vehiculos'); 
             $table->timestamps(); }); }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcr_seguro_vehiculos');
    }
};
