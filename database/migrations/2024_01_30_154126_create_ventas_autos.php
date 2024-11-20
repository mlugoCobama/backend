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
        Schema::create('ventas_autos', function (Blueprint $table) {
            $table->id();
            $table->decimal('nuevas_menudeo',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('nuevas_otras_agencias',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('flotillas',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('usados',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('refacciones',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('servicio',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('activo_fijo',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('accesorios',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('bonos_inceitvos_marca',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('incentivos_nrf',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('otros',  $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('total_ventas',  $precision = 12, $scale = 2)->default(0.00);
            $table->integer('anio');
            $table->integer('mes');
            $table->bigInteger('cat_empresas_id')->unsigned();
            $table->foreign('cat_empresas_id')->references('id')->on('cat_empresas');
            $table->index(['anio','mes','cat_empresas_id']);
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_autos');
    }
};
