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
        Schema::create('datos_acumulados', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->integer('mes');
            $table->decimal('valor', $precision = 9, $scale = 2);
            $table->tinyInteger('estatus')->unsigned()->default(1);
            $table->bigInteger('cat_empresas_id')->unsigned();
            $table->bigInteger('cat_reportes_id')->unsigned();
            $table->timestamps();
            $table->foreign('cat_empresas_id')->references('id')->on('cat_empresas');
            $table->foreign('cat_reportes_id')->references('id')->on('cat_reportes');
            $table->index(['anio', 'mes']);
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
        Schema::dropIfExists('datos_acumulados');
    }
};
