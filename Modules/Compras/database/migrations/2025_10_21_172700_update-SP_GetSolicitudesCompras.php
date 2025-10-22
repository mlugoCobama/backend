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
        DB::unprepared("DROP PROCEDURE IF EXISTS SP_GetSolicitudesCompras;");

        DB::unprepared("
        CREATE PROCEDURE SP_GetSolicitudesCompras(IN empresa INT, IN A_GA INT, IN A_GG INT, IN TIPO INT, IN US INT)
            BEGIN
            SELECT 
                sc.id,
                sc.folio,
                sc.usuario_destino,
                sc.motivo,
                sc.fecha,
                sc.c_c,
                sc.usuario_solicita,
                sc.empresa,
                sc.estatus,
                sc.auto_admin,
                sc.auto_gg,
                sc.auto_macro,
                sc.tipo,
                sc.razon_cancelacion,
                oc.folio_oc,
                p.nombre  AS proveedor,
                SUM(dc.importe_unitario * ds.cantidad)  AS total_orden
            FROM com_solicitudes_compra sc 
            LEFT JOIN com_cotizaciones c 
                ON sc.id = c.solicitudes_compra_id 
            LEFT JOIN com_detalle_solicitud ds 
                ON ds.solicitudes_compra_id = sc.id AND ds.confirmado = 1
            LEFT JOIN com_orden_compra oc 
                ON c.id = oc.cotizaciones_id
            LEFT JOIN com_cotizaciones_proveedores cp 
                ON c.id = cp.cotizaciones_id AND cp.seleccionado = 1
            LEFT JOIN com_detalles_cotizacion dc 
                ON dc.detalle_solicitud_id = ds.id AND dc.cotizaciones_proveedores_proveedores_id = cp.id 
            LEFT JOIN com_proveedores p 
                ON cp.proveedores_id = p.id
            WHERE (empresa IS NULL OR sc.empresa = empresa)
            AND sc.auto_gg >=  A_GA
            AND sc.auto_admin >= A_GG
            AND sc.activo = 1 
            AND (
                TIPO IS NULL AND sc.tipo <> 2
                OR TIPO IS NOT NULL AND sc.tipo = TIPO
            )
            AND (US IS NULL OR sc.usuario_solicita = US)

            GROUP BY 
                sc.id, sc.fecha, sc.folio, sc.estatus, oc.folio_oc, p.nombre

            ORDER BY sc.fecha DESC;
            END;"
);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS SP_GetSolicitudesCompras;");
        DB::unprepared("
            CREATE PROCEDURE SP_GetSolicitudesCompras(IN empresa INT, IN A_GA INT, IN A_GG INT, IN TIPO INT)
            BEGIN
            SELECT 
                sc.id,
                sc.folio,
                sc.usuario_destino,
                sc.motivo,
                sc.fecha,
                sc.c_c,
                sc.usuario_solicita,
                sc.empresa,
                sc.estatus,
                sc.auto_admin,
                sc.auto_gg,
                sc.auto_macro,
                sc.tipo,
                sc.razon_cancelacion,
                oc.folio_oc,
                p.nombre  AS proveedor,
                SUM((dc.importe_unitario * ds.cantidad) * 1.16)  AS total_orden
            FROM com_solicitudes_compra sc 
            LEFT JOIN com_cotizaciones c 
                ON sc.id = c.solicitudes_compra_id 
            LEFT JOIN com_detalle_solicitud ds 
                ON ds.solicitudes_compra_id = sc.id AND ds.confirmado = 1
            LEFT JOIN com_orden_compra oc 
                ON c.id = oc.cotizaciones_id
            LEFT JOIN com_cotizaciones_proveedores cp 
                ON c.id = cp.cotizaciones_id AND cp.seleccionado = 1
            LEFT JOIN com_detalles_cotizacion dc 
                ON dc.detalle_solicitud_id = ds.id AND dc.cotizaciones_proveedores_proveedores_id = cp.id 
            LEFT JOIN com_proveedores p 
                ON cp.proveedores_id = p.id
            WHERE (empresa IS NULL OR sc.empresa = empresa)
            AND sc.auto_gg >=  A_GA
            AND sc.auto_admin >= A_GG
            AND sc.activo = 1 
            AND (
                TIPO IS NULL AND sc.tipo <> 2
                OR TIPO IS NOT NULL AND sc.tipo = TIPO
                -- OR TIPO IS NOT NULL AND sc.tipo != 2
            )
            GROUP BY 
                sc.id, sc.fecha, sc.folio, sc.estatus, oc.folio_oc, p.nombre

            ORDER BY sc.fecha DESC;
            END;"
        );
    }
};
