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
         Schema::table('com_proveedores', function (Blueprint $table) {
             $table->string('rfc')->nullable()->unique()->after('nombre');
         });

         Schema::create('com_proveedor_zonas', function (Blueprint $table) {
             $table->id();
             $table->integer('proveedor_id');
             $table->string('nombre_zona');
             $table->text('estados')->nullable();
             $table->boolean('activo')->default(true);
             $table->timestamps();

             // Foreign key constraint
             $table->foreign('proveedor_id')
                   ->references('id')
                   ->on('com_proveedores');

             $table->unique(['proveedor_id', 'nombre_zona']);
         });

         Schema::create('com_proveedor_contactos', function (Blueprint $table) {
             $table->id();
             $table->unsignedBigInteger('proveedor_zona_id');
             $table->string('nombre');
             $table->string('correo')->nullable();
             $table->string('telefono')->nullable();
             $table->text('notas')->nullable();
             $table->timestamps();

             $table->foreign('proveedor_zona_id')
                 ->references('id')
                 ->on('com_proveedor_zonas');
         });

         Schema::create('com_categorias', function (Blueprint $table) {
             $table->id();
             $table->string('nombre')->unique();
             $table->text('descripcion')->nullable();
             $table->timestamps();
         });

        Schema::create('com_proveedor_productos', function (Blueprint $table) {
            $table->id();
            $table->integer('proveedor_id');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad')->nullable(); // Ej: kg, pieza, caja
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('proveedor_id')
                ->references('id')
                ->on('com_proveedores');

            $table->foreign('categoria_id')
                ->references('id')
                ->on('com_categorias');

            $table->unique(['proveedor_id', 'nombre']);
        });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('com_proveedor_productos');
    Schema::dropIfExists('com_proveedor_contactos');
    Schema::dropIfExists('com_proveedor_zonas');
    Schema::dropIfExists('com_categorias');

    // Luego, en la tabla que alteraste, quitar la columna añadida
    Schema::table('com_proveedores', function (Blueprint $table) {
        $table->dropUnique(['rfc']);        // quitar el índice único
        $table->dropColumn('rfc');          // eliminar la columna
    });

    }
};
