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
        Schema::create('com_almacen', function (Blueprint $table) {
            $table->increments('id'); // INT auto incremental (no big int)

            $table->string('fecha_actualizacion', 45)->nullable();
            $table->string('existencia', 45)->nullable();

            // FK hacia com_detalle_solicitud (INT normal)
            $table->integer('com_detalle_solicitud_id');

            $table->string('codigo_producto', 45)->nullable();
            $table->string('id_usuario', 45)->nullable();

            // DECIMAL(9,2)
            $table->decimal('cantidad', 9, 2)->nullable();

            // Relación con INT normal
            $table->foreign('com_detalle_solicitud_id')
                  ->references('id')
                  ->on('com_detalle_solicitud');
        });

        Schema::create('com_movimientos_almacen', function (Blueprint $table) {
            $table->increments('id'); // INT auto incremental

            $table->string('cantidad', 45)->nullable();
            $table->string('tipo', 45)->nullable();
            $table->string('observaciones', 45)->nullable();
            $table->string('fecha', 45)->nullable();

            // FK hacia com_almacen (INT)
            $table->integer('com_almacen_id')->unsigned();

            $table->string('id_usuario', 45)->nullable();

            // Llave foránea
            $table->foreign('com_almacen_id')
                  ->references('id')
                  ->on('com_almacen');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_almacen');
        Schema::dropIfExists('com_movimientos_almacen');

    }
};
