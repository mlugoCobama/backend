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
        Schema::create('com_gastos_venta', function (Blueprint $table) {
            $table->id();

            $table->decimal('otros', 12, 2)->default(0);
            $table->decimal('gasolina', 12, 2)->default(0);
            $table->decimal('previa', 12, 2)->default(0);
            $table->decimal('descuentos', 12, 2)->default(0);
            $table->decimal('traslados', 12, 2)->default(0);
            $table->decimal('descuento_impulso', 12, 2)->default(0);
            $table->decimal('total_subsidios', 12, 2)->default(0);
            $table->decimal('descuento_gastos', 12, 2)->default(0);
            $table->decimal('cortesia', 12, 2)->default(0);
            $table->decimal('accesorios', 12, 2)->default(0);
            $table->decimal('placas', 12, 2)->default(0);

            $table->unsignedBigInteger('id_datos_venta');

            $table->decimal('comision_apv_pesos', 12, 2)->default(0);
            $table->decimal('comision_bdc_pesos', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('id_datos_venta')->references('id')->on('com_datos_venta')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_gastos_venta');
    }
};
