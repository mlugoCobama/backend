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
        Schema::create('com_datos_pago_proveedor', function (Blueprint $table) {
            $table->increments('id'); // INT autoincremental

            $table->string('banco', 100);
            $table->string('no_cuenta', 50);
            $table->string('clave_interbancaria', 50);
            $table->string('beneficiario', 150);

            // Relación con la tabla com_proveedores (INT normal)
            $table->integer('proveedor_id');
            $table->foreign('proveedor_id')
                  ->references('id')
                  ->on('com_proveedores');

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_datos_pago_proveedor');
    }


};
