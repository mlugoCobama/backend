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
            $table->dateTime('fecha_entrga')->nullable()->after('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('com_orden_compra', function (Blueprint $table) {
            $table->dropColumn('fecha_entrga');
        });

    }
};
