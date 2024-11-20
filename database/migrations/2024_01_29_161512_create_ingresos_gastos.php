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
        Schema::create('ingresos_gastos', function (Blueprint $table) {
            $table->id();
            $table->decimal('contable', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('ajuste_ingresos', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('conciliable', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('planeacion_favor', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('ajuste_auditoria', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('rt', $precision = 12, $scale = 2)->default(0.00);
            $table->decimal('neto', $precision = 12, $scale = 2)->default(0.00);
            $table->integer('anio');
            $table->integer('mes');
            $table->bigInteger('cat_empresas_id')->unsigned();
            $table->bigInteger('cat_ingresos_gastos_id')->unsigned();
            $table->foreign('cat_empresas_id')->references('id')->on('cat_empresas');
            $table->foreign('cat_ingresos_gastos_id')->references('id')->on('cat_ingresos_gastos');
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
        Schema::dropIfExists('ingresos_gastos');
    }
};
