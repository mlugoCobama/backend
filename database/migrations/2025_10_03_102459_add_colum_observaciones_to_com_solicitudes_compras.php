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
        Schema::table('com_solicitudes_compra', function (Blueprint $table) {
             $table->mediumText('observaciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('com_solicitudes_compra', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
