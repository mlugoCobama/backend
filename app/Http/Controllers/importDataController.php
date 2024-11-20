<?php

namespace App\Http\Controllers;

use App\Models\Inventarios;
use App\Models\OrdenesUnidades;
use App\Models\UtilidadArea;
use Illuminate\Support\Facades\File;

use Illuminate\Http\Request;

class importDataController extends Controller
{

    public function index()
    {
        $fechas = [
            '2024-01-01',
            '2024-02-01',
            '2024-03-01',
            '2024-04-01',
        ];

        $content = File::get(base_path('/data.json'));
        $json = json_decode( json: $content, associative: true );

        $data = $json['nissan']['universidad'];

        $inventario = $data['inventario'];
        $antiNuevos = $data['inventarioNuevo'];
        $antiSemi   = $data['inventarioSemi'];

        for ($i=0; $i < count($fechas); $i++) {
        //for ($i=0; $i < 4; $i++) {

            $table = new Inventarios();

            $table->nuevos = $inventario['nuevos']['serie2024'][$i];
            $table->refacciones = $inventario['refacciones']['serie2024'][$i];
            $table->seminuevos = $inventario['seminuevos']['serie2024'][$i];
            $table->inv_nuevo_101 = $antiNuevos['anti101']['serie2024'][$i];
            $table->inv_nuevo_201 = $antiNuevos['anti201']['serie2024'][$i];
            $table->inv_nuevo_301 = $antiNuevos['anti301']['serie2024'][$i];
            $table->inv_nuevo_401 = $antiNuevos['anti401']['serie2024'][$i];
            $table->inv_semi_101 = $antiSemi['anti101']['serie2024'][$i];
            $table->inv_semi_201 = $antiSemi['anti201']['serie2024'][$i];
            $table->inv_semi_301 = $antiSemi['anti301']['serie2024'][$i];
            $table->inv_semi_401 = $antiSemi['anti401']['serie2024'][$i];
            $table->fecha = $fechas[$i];
            $table->sucursales_id = 25;

            $table->save();

        }

       //return $unoPlanta;
    }
}
