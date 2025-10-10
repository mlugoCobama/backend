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
            $table->id(); // Llave primaria auto-incrementable
            $table->string('ruta', 250); // Cadena de texto de hasta 250 caracteres
            $table->mediumText('comentario'); // Texto largo tipo mediumText
            $table->integer('orden_compra_id'); // Campo entero
            $table->date('fecha'); 
            // Llave foránea sin onDelete
            $table->foreign('orden_compra_id')
                  ->references('id')
                  ->on('com_orden_compra');

            $table->timestamps(); // created_at y updated_at
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
