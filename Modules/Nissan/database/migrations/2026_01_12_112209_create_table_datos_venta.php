<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'autos';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('com_datos_venta', function (Blueprint $table) {
            $table->id();
            $table->string('fecha_as_salida');
            $table->string('no_factura')->unique();
            $table->string('razon_social');
            $table->string('descripcion');
            $table->string('no_inventario')->nullable();
            $table->unsignedBigInteger('id_vendedor');
            $table->string('serie')->nullable();

            $table->decimal('total_venta', 12, 2);
            $table->decimal('costos', 12, 2)->nullable();
            $table->decimal('bonificaciones', 12, 2)->default(0);
            $table->decimal('utilidad_inicial', 12, 2)->nullable();

            $table->unsignedBigInteger('tipo_venta_id');
            $table->string('estatus')->default('0');
            $table->boolean('entregado')->default(0);
            $table->string('bdc')->default(0);
            $table->string('agencia');
            $table->string('validado')->default(0);
            $table->string('clave_producto');
            $table->string('modelo_producto');
            $table->string('anio_vehiculo');
            $table->string('tipo_venta');
            $table->string('fecha_factura');
            $table->string('pagado')->default(0);
            $table->string('observacion');

            $table->timestamps();

            
            $table->foreign('id_vendedor')->references('id')->on('com_vendedores');
            $table->foreign('tipo_venta_id')->references('id')->on('com_tipos_venta');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_datos_venta');
    }
};
