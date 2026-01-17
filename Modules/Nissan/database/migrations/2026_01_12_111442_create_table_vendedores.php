<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    protected $connection = 'autos';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('com_vendedores', function (Blueprint $table) {
            $table->id(); 
            $table->string('tipo'); 
            $table->decimal('porcentaje_apv', 5, 2)->nullable(); 
            $table->string('nro_vendedor_as'); 
            $table->string('agencia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_vendedores');
    }
};
