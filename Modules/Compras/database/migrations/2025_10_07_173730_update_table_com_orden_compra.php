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
        Schema::table('com_orden_compra', function (Blueprint $table) {
            $table->mediumText('modo_pago')->nullable();
            $table->integer('surtido_solcitado')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('com_orden_compra', function (Blueprint $table) {
            $table->dropColumn('modo_pago');
            $table->dropColumn('surtido_solcitado');
        });
    }
};
