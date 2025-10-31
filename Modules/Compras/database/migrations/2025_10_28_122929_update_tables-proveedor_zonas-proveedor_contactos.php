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

        Schema::dropIfExists('com_proveedor_contactos');
        Schema::dropIfExists('com_proveedor_zonas');
        
        Schema::create('com_proveedor_contactos', function (Blueprint $table) {
             $table->id();
             $table->integer('proveedor_id');
             $table->string('nombre');
             $table->string('correo')->nullable();
             $table->string('telefono')->nullable();
             $table->text('notas')->nullable();
             $table->timestamps();

             $table->foreign('proveedor_id')
                 ->references('id')
                 ->on('com_proveedores');
         });

        Schema::create('com_proveedor_zonas', function (Blueprint $table) {
             $table->id();
             $table->unsignedBigInteger('contacto_id');
             $table->string('nombre_zona');
             $table->text('estados')->nullable();
             $table->boolean('activo')->default(true);
             $table->timestamps();

             // Foreign key constraint
             $table->foreign('contacto_id')
                   ->references('id')
                   ->on('com_proveedor_contactos');

            //  $table->unique(['proveedor_id', 'nombre_zona']);
         }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_proveedor_contactos');
        Schema::dropIfExists('com_proveedor_zonas');
    }
};
