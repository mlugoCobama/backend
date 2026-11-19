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
        Schema::table('com_movimientos_almacen', function (Blueprint $table) {
            $table->unsignedBigInteger('com_almacen_producto_id')->nullable()->after('tipo');
            // $table->text('empresa')->nullable()->after('id');
            $table->foreign('com_almacen_producto_id')
                  ->references('id')
                  ->on('com_almacen_productos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('com_movimientos_almacen', function (Blueprint $table) {

        });
    }
};
