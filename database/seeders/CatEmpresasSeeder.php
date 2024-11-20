<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatEmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cat_empresas')->insert([
            ['num_intercompania' => 110, 'nombre' =>	"Garza gas"],
            ['num_intercompania' => 111, 'nombre' =>	"Garza Sur"],
            ['num_intercompania' => 120, 'nombre' =>	"Tanques garza gas"],
            ['num_intercompania' => 130, 'nombre' =>	"Satelite"],
            ['num_intercompania' => 131, 'nombre' =>	"Azteca gas"],
            ['num_intercompania' => 132, 'nombre' =>	"Gas Premio"],
            ['num_intercompania' => 133, 'nombre' =>	"Gasera Multiregional"],
            ['num_intercompania' => 135, 'nombre' =>	"Servicios Especiales de Gas"],
            ['num_intercompania' => 153, 'nombre' =>	"Invalle"],
            ['num_intercompania' => 155, 'nombre' =>	"Gasamex"],
            ['num_intercompania' => 160, 'nombre' =>	"Tragamex"],
            ['num_intercompania' => 161, 'nombre' =>	"Tran Soni"],
            ['num_intercompania' => 180, 'nombre' =>	"Teoloyucan"],
            ['num_intercompania' => 183, 'nombre' =>	"Servicio el Once"],
            ['num_intercompania' => 190, 'nombre' =>	"Zugas"],
            ['num_intercompania' => 191, 'nombre' =>	"Baragas"],
            ['num_intercompania' => 200, 'nombre' =>	"Tanques soni"],
            ['num_intercompania' => 201, 'nombre' =>	"Agrupamiento"],
            ['num_intercompania' => 210, 'nombre' =>	"Reyes Gas"],
            ['num_intercompania' => 240, 'nombre' =>	"Servigas del Valle"],
            ['num_intercompania' => 250, 'nombre' =>	"Flamazul"],
            ['num_intercompania' => 251, 'nombre' =>	"Flamamex"],
            ['num_intercompania' => 333, 'nombre' =>	"CAS"],
            ['num_intercompania' => 353, 'nombre' =>	"Gas urbano"],
            ['num_intercompania' => 354, 'nombre' =>	"Iztagas y Energia"],
            ['num_intercompania' => 730, 'nombre' =>	"Nissan Azcapotzalco"],
            ['num_intercompania' => 714, 'nombre' =>	"Nissan Campestre"],
            ['num_intercompania' => 773, 'nombre' =>	"Nissan Patriotismo"],
            ['num_intercompania' => 770, 'nombre' =>	"Nissan Revolucion"],
            ['num_intercompania' => 776, 'nombre' =>	"Nissan Mixcoac"],
            ['num_intercompania' => 710, 'nombre' =>	"Nissan Universidad"],
            ['num_intercompania' => 713, 'nombre' =>	"Nissan Mitikah"],
            ['num_intercompania' => 740, 'nombre' =>	"Renault Azcapotzalco"],
            ['num_intercompania' => 746, 'nombre' =>	"Renault Ecatepec"],
            ['num_intercompania' => 760, 'nombre' =>	"Renault Pachuca"],
            ['num_intercompania' => 743, 'nombre' =>	"Renault Vallejo"]
        ]);
    }
}
