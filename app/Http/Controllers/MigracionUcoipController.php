<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MigracionUcoipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sql = "SELECT 
                    glpi_users.id,
                    glpi_users.firstname, 
                    glpi_users.realname,
                    glpi_users.name, 
                    glpi_users.phone2,
                    glpi_users.mobile, 
                    glpi_directorio_puestos.nombre as puesto,
                    glpi_entities.Telefono, 
                    glpi_directorio_area.nombre as area,
                    glpi_entities.direccion,
                    glpi_entities.intercompania,
                    glpi_entities.name as empresa
                FROM   
                    glpi_directorio_area, 
                    glpi_directorio_puestos,
                    glpi_users,
                    glpi_entities
                WHERE  
                    glpi_directorio_area.id_glpi_directorio_area = glpi_users.id_areas_directorio
                AND glpi_users.is_active='1'
                AND glpi_users.intercompania = glpi_entities.intercompania 
                AND glpi_users.intercompania = 333
                AND glpi_directorio_puestos.id_glpi_directorio_puestos = glpi_users.id_puesto_directorio
                AND glpi_users.id <> 344
                AND glpi_users.id <> 29
                AND glpi_users.id <> 22
                ORDER BY glpi_users.firstname ASC";

        $data = DB::connection('intranet')->select($sql);

        dd($data);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
