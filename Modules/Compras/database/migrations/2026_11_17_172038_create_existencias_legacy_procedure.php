<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS existencias_legacy;");

        DB::unprepared("
            CREATE DEFINER=`mlugo`@`%` PROCEDURE `existencias_legacy`(
                IN p_tipo VARCHAR(255),
                IN p_intercompania VARCHAR(255),
                IN p_categoria INT
            )
            BEGIN
                SELECT
                    ca.id,
                    ca.existencia AS stock,
                    0 AS stock_minimo,
                    csc.empresa AS almacen_empresa,
                    NULL AS com_productos_id,
                    '0000' AS clave_interna,
                    cds.descripcion AS descripcion_planta,
                    'No aplica' AS descripcion_proveedor,
                    ca.fecha_actualizacion,
                    ccum.id AS unidad_medida_id,
                    ccum.nombre AS unidad_medida,
                    ccsa.sistema AS subcategoria
                FROM com_detalle_entrega AS ca
                JOIN com_detalle_solicitud AS cds ON ca.com_detalle_solicitud_id = cds.id
                JOIN com_cat_unidades_medida AS ccum ON ccum.id = cds.cat_unidades_medida_id
                JOIN com_solicitudes_compra AS csc ON csc.id = cds.solicitudes_compra_id
                JOIN com_catalogo_sistemas_auto AS ccsa ON ccsa.id = csc.com_cat_sistemas_auto_id
                WHERE cds.confirmado = 1
                AND cds.com_productos_id IS NULL
                AND csc.activo = 1
                AND csc.estatus > 8
                AND ca.existencia > 0
                AND (p_tipo IS NULL OR csc.tipo = p_tipo)
                AND (p_categoria IS NULL OR csc.com_cat_sistemas_auto_id = p_categoria)
                AND (p_intercompania IS NULL OR csc.empresa = p_intercompania);
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       DB::unprepared("DROP PROCEDURE IF EXISTS existencias_legacy;");
    }
};
