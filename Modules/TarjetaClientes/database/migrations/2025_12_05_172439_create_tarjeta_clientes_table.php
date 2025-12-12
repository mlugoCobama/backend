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
    Schema::create('tarjeta_clientes', function (Blueprint $table) {
        $table->id();

        // Step 1: Agencia
        $table->string('agencia');
        $table->string('asesor_ventas');
        $table->string('no_sicop');

        // Step 2: Datos personales
        $table->string('nombre_cliente');
        $table->string('direccion');
        $table->string('ciudad');
        $table->string('estado');

        // Step 3: Datos de contacto
        $table->string('email_personal');
        $table->string('email_trabajo')->nullable();
        $table->string('telefono_principal');
        $table->string('telefono_secundario')->nullable();
        $table->string('telefono_adicional')->nullable();

        // Step 4: Tipo de cliente
        $table->string('tiene_cita');
        $table->string('tipo_contacto');
        $table->string('cual_publicidad')->nullable();

        // Step 5: Atención al cliente
        $table->string('servicio');
        $table->text('notas_apv');
        $table->text('notas_gv');

        // Step 6: Intencion de compra
        $table->string('cliente_quiere');
        $table->string('anio')->nullable();
        $table->string('modelo')->nullable();
        $table->string('estilo')->nullable();
        $table->string('color')->nullable();
        $table->string('stock_vin')->nullable();
        $table->string('equipo_particular')->nullable();

        // Step 7: Vehículo del cliente
        $table->string('anio_vehiculo')->nullable();
        $table->string('modelo_vehiculo')->nullable();
        $table->string('estilo_vehiculo')->nullable();
        $table->string('color_vehiculo')->nullable();

        // Campos booleanos -> tinyInt
        $table->tinyInteger('ac')->default(0);
        $table->tinyInteger('pw')->default(0);
        $table->tinyInteger('pl')->default(0);
        $table->tinyInteger('cruise')->default(0);
        $table->tinyInteger('tilt')->default(0);
        $table->tinyInteger('auto')->default(0);
        $table->tinyInteger('x4x4')->default(0);
        $table->tinyInteger('cd')->default(0);
        $table->tinyInteger('sat')->default(0);
        $table->tinyInteger('navi')->default(0);

        $table->string('kilometraje')->nullable();
        $table->string('vin')->nullable();
        $table->string('costo_pagar')->nullable();
        $table->string('acv')->nullable();
        $table->string('telefono_banco')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('tarjeta_clientes');
}


};
