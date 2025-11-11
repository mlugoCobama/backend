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
         Schema::table('com_datos_vehiculos', function (Blueprint $table) {
             $table->tinyInteger('categoria')->nullable();
             $table->tinyInteger('gps')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('com_datos_vehiculos', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'gps']);
        });

    }
};
