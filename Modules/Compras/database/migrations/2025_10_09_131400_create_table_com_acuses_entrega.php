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
        Schema::create('com_acuses_entrega', function (Blueprint $table) {
            $table->id(); 
            $table->string('ruta', 250); 
            $table->mediumText('comentario'); 
            $table->integer('orden_compra_id');
            $table->date('fecha'); 
            $table->foreign('orden_compra_id')
                  ->references('id')
                  ->on('com_orden_compra');

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_acuses_entrega');
    }
};
