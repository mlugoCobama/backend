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
        Schema::create('balanzas', function (Blueprint $table) {
            $table->id();
            $table->integer('cta');
            $table->integer('scta');
            $table->integer('sscta');
            $table->integer('ssscta');
            $table->string('descripcion');
            $table->decimal('saldo_inicial', $precision = 9, $scale = 2);
            $table->decimal('debe', $precision = 9, $scale = 2);
            $table->decimal('haber', $precision = 9, $scale = 2);
            $table->decimal('saldo_actual', $precision = 9, $scale = 2);
            $table->integer('anio');
            $table->integer('mes');
            $table->bigInteger('cat_empresas_id')->unsigned();
            $table->foreign('cat_empresas_id')->references('id')->on('cat_empresas');
            $table->index(['cta','scta','sscta','ssscta','anio','mes']);
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
        Schema::dropIfExists('balanzas');
    }
};
