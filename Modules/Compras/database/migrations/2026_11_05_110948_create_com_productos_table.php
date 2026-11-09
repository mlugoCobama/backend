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
        Schema::create('com_productos', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('com_subcategoria_id');
            $table->string('clave_interna');
            $table->decimal('cant_default');
            $table->integer('com_cat_unidad_medida_id');
            $table->text('descripcion_planta');
            $table->text('descripcion_proveedor');
            $table->text('observaciones');
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->foreign('com_subcategoria_id')
                  ->references('id')
                  ->on('com_subcategoria');

            $table->foreign('com_cat_unidad_medida_id')
                  ->references('id')
                  ->on('com_cat_unidades_medida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_productos');
    }
};
