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
        DB::unprepared("DROP PROCEDURE IF EXISTS SP_GetDataAutotanques;");
        
        DB::unprepared("
        CREATE PROCEDURE SP_GetDataAutotanques(IN intercompania INT, IN limite INT)
        BEGIN
            SELECT 
                dv.id,
                dv.id_cre,
                dv.nro_economico,
                dv.marca AS marca_vehiculo,
                dv.submarca,
                dv.modelo,
                dv.no_serie,
                dv.placas,
                dv.tipo,
                dv.estatus,
                dv.tipo_combustible,
                dt.id AS id_tanque,
                dt.marca AS marca_tanque,
                dt.anio_fabricacion,
                dt.capacidad,
                dt.serie,
                dt.tipo_medidor,
                dv.id_sucursal,
                dSuc.nombre AS sucursal,
                dSuc.num_intercompania,
                dEnt.nombre AS entidad,
                -- sv.id as idSeguro,
                -- sv.aseguradora,
                -- sv.inciso_vehiculo,
                -- sv.cobertura,
                -- sv.inicio_vigencia,
                -- sv.fin_vigencia,
                -- sv.flotilla,
                -- sv.inciso_foltilla,
                -- sv.id_com_datos_vehiculo,
                dv.activo
            FROM
                com_datos_vehiculos AS dv
                    LEFT JOIN
                com_datos_tanque AS dt ON dv.id = dt.com_datos_vehiculo_id
                    -- LEFT JOIN
                -- mcr_seguro_vehiculos AS sv ON dv.id = sv.id_com_datos_vehiculo
                    INNER JOIN
                Dashboard.sucursales AS dSuc ON dSuc.id = dv.id_sucursal
                    INNER JOIN
                Dashboard.entidades AS dEnt ON dSuc.entidad_id = dEnt.id
            WHERE
                dSuc.num_intercompania = intercompania
			AND dv.activo > 0 AND dv.activo <= limite;
                    END;
    ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS SP_GetDataAutotanques;");
        DB::unprepared("
        CREATE PROCEDURE SP_GetDataAutotanques(IN intercompania INT)
        BEGIN
            SELECT 
                dv.id,
                dv.nro_economico,
                dv.marca AS marca_vehiculo,
                dv.submarca,
                dv.modelo,
                dv.no_serie,
                dv.placas,
                dv.tipo,
                dv.estatus,
                dv.tipo_combustible,
                dt.id AS id_tanque,
                dt.marca AS marca_tanque,
                dt.anio_fabricacion,
                dt.capacidad,
                dt.serie,
                dt.tipo_medidor,
                dv.id_sucursal,
                dSuc.nombre AS sucursal,
                dSuc.num_intercompania,
                dEnt.nombre AS entidad
            FROM
                com_datos_vehiculos AS dv
                    LEFT JOIN
                com_datos_tanque AS dt ON dv.id = dt.com_datos_vehiculo_id
                    INNER JOIN
                Dashboard.sucursales AS dSuc ON dSuc.id = dv.id_sucursal
                    INNER JOIN
                Dashboard.entidades AS dEnt ON dSuc.entidad_id = dEnt.id
            WHERE
                dSuc.num_intercompania = intercompania
                    AND dv.activo = 1;
            END;
        ");

    }
};
