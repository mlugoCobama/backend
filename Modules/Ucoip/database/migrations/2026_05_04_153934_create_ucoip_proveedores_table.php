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
        Schema::create('ucoip_proveedores', function (Blueprint $table) {
            $table->id();

            // Relación con tipo de servicio (opcional pero útil)
            // $table->foreignId('tipo_servicio_id')
            //       ->nullable()
            //       ->constrained('tipos_servicio')
            //       ->nullOnDelete();

            // Información básica
            $table->string('nombre')->unique();

            // Contacto
            $table->string('contacto')->nullable(); 
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();

            // Soporte
            $table->string('soporte_email')->nullable();
            $table->string('soporte_telefono', 20)->nullable();
            $table->string('sitio_web')->nullable();

            // Datos administrativos
            $table->string('rfc', 20)->nullable(); 
            $table->string('cuenta')->nullable();  

            // Control
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices
            // $table->index('tipo_servicio_id');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ucoip_proveedores');
    }
};
