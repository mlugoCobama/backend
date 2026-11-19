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
        Schema::create('com_almacen_productos', function (Blueprint $table) {
            $table->id();

            $table->decimal('stock', 10, 2);
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->decimal('stock_maximo', 10, 2)->nullable();

            $table->unsignedBigInteger('almacen_empresa');

            $table->unsignedBigInteger('com_productos_id');

            $table->timestamps();

            $table->foreign('com_productos_id')
                  ->references('id')
                  ->on('com_productos');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_almacen_productos');
    }
};
