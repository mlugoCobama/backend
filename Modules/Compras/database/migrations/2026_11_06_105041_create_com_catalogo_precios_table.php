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

        Schema::create('com_catalogo_precios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('com_productos_id');
            $table->integer('com_proveedores_id');
            $table->decimal('precio', 10, 2);
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->unique(['com_productos_id', 'com_proveedores_id'], 'prod_prov_unique');

            $table->foreign('com_productos_id')->references('id')->on('com_productos')->onDelete('cascade');
            $table->foreign('com_proveedores_id')->references('id')->on('com_proveedores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_catalogo_precios');
    }
};
