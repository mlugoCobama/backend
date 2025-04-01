<?php

namespace App\Http\Controllers;

use App\Models\Inventarios;
use App\Models\OrdenesUnidades;
use App\Models\DatosGenerales;
use App\Models\Complementos;
use App\Models\VentasPostVenta;
use App\Models\CostosFinancierosPrestamos;
use App\Models\UtilidadArea;
use App\Models\Personal;
use Illuminate\Support\Facades\File;

use Illuminate\Http\Request;

class importDataController extends Controller
{

    public function index()
    {
        $content = File::get(base_path('/data.json'));
        $json = json_decode(json: $content, associative: true);
        $data = $json['lille'];//Sudvision 

        $idSucursal = 26; //Id de la sucursal
        $anio = '2023'; //año a registrar 
        $serie = "serie$anio";

        $agencias = [
            'campestre',//0->22
            'automotriz',//1->23
            'insugentes',//2->24
            'universidad',//3->25
            'azcapotzalco',//4->26
            'ecatepec',//5->27
            'vallejo',//6->28
            'pachuca',//7->29
            'mitika',//8->30
            'plutarco',//9->31
            'mixcoac',//10->32
            'revolucion',//11->33
            'patriotismo'//12->34
        ];

        $sucursal = $data['generales'];
        $nombreSucursal = $agencias[$idSucursal-22];
        
        //*Modelo utlidadArea
        // $utlidad_area_comercial = $sucursal['departamentos']['comercial'];
        // $utlidad_area_postventa = $sucursal['departamentos']['postventa'];
        // $area_comercial = $sucursal['areaComercial'];
        // $area_postventa = $sucursal['areaPostventa'];
        //*Modelo Datos Generales 
        // $gastos = $sucursal['totalGastos'];
        // $uno = $sucursal['uno'];
        // $personal = $sucursal['acumuladoPersonal'];
        //*Modelo OrdenesUnidades
        // $ordenesServicio = $sucursal['ordenesServicio'];
        // $unidadesVendidas = $sucursal['unidadesVendidas'];
        //*Modelo Costos Financieros prestamos
        // $costos_financieros = $sucursal['costoFinanciero'];
        // $prestamos = $sucursal['prestamos'];
        
        //*Modelo inventarios
        // $inventario = $sucursal['inventario'];
        // $inventario_nuevo = $sucursal['inventarioNuevo'];
        // $inventario_semi = $sucursal['inventarioSemi'];
        //*Modelo complementos
        // $bonos = $sucursal['bonos'];
        // $complementos = $sucursal['otrosIngresos'];
        // $descuentos = $sucursal['descuentos'];
        // $cumplimiento = $sucursal['cumplimiento'];
        //*Ventas post venta
        // $ventas = $sucursal['ventas'];
        //* Modelo Personal
        $personal = $sucursal['acumuladoPersonal2'];

        
        $fechas = [
            "$anio-01-01",
            "$anio-02-01",
            "$anio-03-01",
            "$anio-04-01",
            "$anio-05-01",
            "$anio-06-01",
            "$anio-07-01",
            "$anio-08-01",
            "$anio-09-01",
            "$anio-10-01",
            "$anio-11-01",
            "$anio-12-01"
        ];

        
        for ($i = 0; $i < 12; $i++) {
        //for ($i=0; $i < 2; $i++) {
          //TODO MODELO DATOS GENERALES
            //   $datosGenerales = new DatosGenerales();
            //   $datosGenerales->uno = $uno[$serie][$i] ?? 0;
            //   $datosGenerales->gasto = $gastos[$serie][$i] ?? 0;
            //   $tPersonal = $personal['ventas'][$serie][$i] + $personal['usados'][$serie][$i]
            //   + $personal['refacciones'][$serie][$i] + $personal['servicios'][$serie][$i]
            //   +$personal['admin'][$serie][$i] + $personal['apvs'][$serie][$i];
            //   $datosGenerales->personal = $tPersonal ?? 0;
            //   $datosGenerales->fecha = $fechas[$i];
            //   $datosGenerales->sucursales_id = $idSucursal;
            //  $datosGenerales->save();

            //TODO MODELO INVENTARIOS
            //  $tInventarios = new Inventarios();
            //  $tInventarios->nuevos = $inventario['nuevos'][$serie][$i] ?? 0;
            //  $tInventarios->refacciones = $inventario['refacciones'][$serie][$i] ?? 0;
            //  $tInventarios->seminuevos = $inventario['seminuevos'][$serie][$i] ?? 0;
            //  $tInventarios->inv_nuevo_101  = $inventario_nuevo['anti101'][$serie][$i] ?? 0;
            //  $tInventarios->inv_nuevo_201= $inventario_nuevo['anti201'][$serie][$i] ?? 0;
            //  $tInventarios->inv_nuevo_301= $inventario_nuevo['anti301'][$serie][$i] ?? 0;
            //  $tInventarios->inv_nuevo_401= $inventario_nuevo['anti401'][$serie][$i] ?? 0;
            //  $tInventarios->inv_semi_101= $inventario_semi['anti101'][$serie][$i] ?? 0;
            //  $tInventarios->inv_semi_201= $inventario_semi['anti201'][$serie][$i] ?? 0;
            //  $tInventarios->inv_semi_301= $inventario_semi['anti301'][$serie][$i] ?? 0;
            //  $tInventarios->inv_semi_401= $inventario_semi['anti401'][$serie][$i] ?? 0;
            //  $tInventarios->fecha = $fechas[$i];
            //  $tInventarios->sucursales_id = $idSucursal;
            //  $tInventarios->save();

            //TODO MODELO ORDENES UNIDADES
            //  $tOrdenesUnidades =  new OrdenesUnidades();
            //  $tOrdenesUnidades->servicio = $ordenesServicio['servicio'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->utilidad_servicio = $ordenesServicio['servicioPesos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->hyp = $ordenesServicio['hyp'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->utilidad_hyp  = $ordenesServicio['hypPesos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->nuevos  = $unidadesVendidas['nuevos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->utilidad_nuevos  = $unidadesVendidas['nuevosPesos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->flotillas  = $unidadesVendidas['flotillas'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->utilidad_flotillas  = $unidadesVendidas['flotillasPesos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->seminuevos  = $unidadesVendidas['seminuevos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->utilidad_seminuevos  = $unidadesVendidas['seminuevosPesos'][$serie][$i] ?? 0;
            //  $tOrdenesUnidades->fecha = $fechas[$i];
            //  $tOrdenesUnidades->sucursales_id = $idSucursal;
            //  $tOrdenesUnidades->save();

            //TODO MODELO UTILIDAD AREA
            //   $tUtilidadArea =  new UtilidadArea();
            //   $tUtilidadArea->nuevos = $area_comercial['nuevos'][$serie][$i] ?? 0;
            //   $tUtilidadArea->flotillas = $area_comercial['flotillas'][$serie][$i] ?? 0;
            //   $tUtilidadArea->seminuevos = $area_comercial['seminuevos'][$serie][$i] ?? 0;
            //   $tUtilidadArea->servicio  = $area_postventa['servicio'][$serie][$i] ?? 0;
            //   $tUtilidadArea->refacciones  = $area_postventa['refacciones'][$serie][$i] ?? 0;
            //   $tUtilidadArea->hyp  = $area_postventa['hyp'][$serie][$i] ?? 0;
            //   $tUtilidadArea->area_comercial  = $utlidad_area_comercial[$serie][$i] ?? 0;
            //   $tUtilidadArea->area_postventa  = $utlidad_area_postventa[$serie][$i] ?? 0;
            //   $tUtilidadArea->fecha = $fechas[$i];
            //   $tUtilidadArea->sucursales_id = $idSucursal;
            //   $tUtilidadArea->save();
            //TODO MODELO PERSONAL
               $tPersonal =  new Personal();
               $tPersonal->ventas = $personal['ventas'][$serie][$i] ?? 0;
               $tPersonal->usados = $personal['usados'][$serie][$i] ?? 0;
               $tPersonal->refacciones = $personal['refacciones'][$serie][$i] ?? 0;
               $tPersonal->servicios  = $personal['servicios'][$serie][$i] ?? 0;
               $tPersonal->admin  = $personal['admin'][$serie][$i] ?? 0;
               $tPersonal->apvs  = $personal['apvs'][$serie][$i] ?? 0;
               $tPersonal->fecha = $fechas[$i];
               $tPersonal->sucursales_id = $idSucursal;
               $tPersonal->save();
            //TODO MODELO VENTAS POST VENTA 
            //   $tVentaPostVenta =  new VentasPostVenta();
            //   $tVentaPostVenta->ventas_servicio = $ventas['servicio'][$serie][$i] ?? 0;
            //   $tVentaPostVenta->total_ventas_ref = $ventas['total_refacciones'][$serie][$i] ?? 0;
            //   $tVentaPostVenta->refacciones_servicio = $ventas['refacciones_servicio'][$serie][$i] ?? 0;
            //   $tVentaPostVenta->refacciones_hyp  = $ventas['refacciones_hyp'][$serie][$i] ?? 0;
            //   $tVentaPostVenta->refacciones_mostrador  = $ventas['refacciones_mostrador'][$serie][$i] ?? 0;
            //   $tVentaPostVenta->fecha = $fechas[$i];
            //   $tVentaPostVenta->sucursales_id = $idSucursal;
            //   $tVentaPostVenta->save();
             //TODO MODELO COMPLEMENTOS
            //   $tComplementos =  new Complementos();
            //   $tComplementos->objetivo = $cumplimiento['objetivo'][$serie][$i] ?? 0;
            //   $tComplementos->cumplimiento = $cumplimiento['cumplimiento'][$serie][$i] ?? 0;
            //   $tComplementos->porcentaje = $cumplimiento['porcentaje'][$serie][$i] ?? 0;
            //   $tComplementos->bono_marca  = $bonos['marca'][$serie][$i] ?? 0;
            //   $tComplementos->bonos  = $complementos['bonos'][$serie][$i] ?? 0;
            //   $tComplementos->incentivos  = $complementos['incentivos'][$serie][$i] ?? 0;
            //   $tComplementos->otros  = $complementos['otros'][$serie][$i] ?? 0;
            //   $tComplementos->descuentos  = $descuentos['descuentos'][$serie][$i] ?? 0;
            //   $tComplementos->fecha = $fechas[$i];
            //   $tComplementos->sucursales_id = $idSucursal;
            //   $tComplementos->save();

             //TODO  COSTOS FINANCIEROS PRESTAMOS
            //   $tCostosFinacierosPrestamos =  new CostosFinancierosPrestamos();
            //   $tCostosFinacierosPrestamos->nuevos = $costos_financieros['nuevos'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->flotillas = $costos_financieros['flotillas'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->refacciones = $costos_financieros['refacciones'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->bajio  = $costos_financieros['bajio'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->intercias  = $costos_financieros['intercias'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->plan_piso  = $prestamos['planPiso'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->plan_piso_interes  = $prestamos['planPisoInteres'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->nrf  = $prestamos['nrf'][$serie][$i] ?? 0;
            //   $tCostosFinacierosPrestamos->nrf_interes  = $prestamos['nrfInteres'][$serie][$i] ?? 0;
        
            //   $tCostosFinacierosPrestamos->fecha = $fechas[$i];
            //   $tCostosFinacierosPrestamos->sucursales_id = $idSucursal;
            //   $tCostosFinacierosPrestamos->save();

         }


        //  $inventario = $data['inventario'];
        //  $antiNuevos = $data['inventarioNuevo'];
        //  $antiSemi   = $data['inventarioSemi'];
        return response()->json([
         'message' => "Se guardaron los datos de $nombreSucursal 
         con id $idSucursal serie $serie",
        //  'data' => $tCostosFinacierosPrestamos,
        ]);
    }

    public function insertGasseras()
    {
        $content = File::get(base_path('/data.json'));
        $json = json_decode(json: $content, associative: true);
        $data = $json['gaseras'];

        $idSucursal = 4;
        $anio = '2024';

        $gaseras = [
            'servigas',
            "urbano",
            "multi",
            "gasamex",
            "reyes",
            "iztagas",
            "flamamex",
            "azteca",
            "satelite",
            "garzagas",
            "garzasur",
            "flamazul",
            "segas",
            "zugas",
            "premio",
            // "baragas",
        ];


        $uno = $data['dataUnoPlanta'];
        $ubo = $data['dataUboPlanta'];
        $utilidad_bruta = $data['dataUtilidadPlanta'];
        $gasto = $data['dataGastoPlanta'];
        $eficiencia = $data['dataEfePlanta'];
        // $personal = $data['dataAcumuladoPersonal'];
        $ventas = $data['dataVentaPlanta'];
        $venta_litros = $data['dataLitrosPlanta'];


        $serie = "serie$anio";
        $fechas = [
            "$anio-01-01",
            "$anio-02-01",
            "$anio-03-01",
            "$anio-04-01",
            "$anio-05-01",
            "$anio-06-01",
            "$anio-07-01",
            "$anio-08-01",
            "$anio-09-01",
            "$anio-10-01",
            "$anio-11-01",
            "$anio-12-01"
        ];


        // $nombreSucursal = "temamatla";


        $nombreSucursal = $gaseras[$idSucursal - 1];
        $estacionanio = "$nombreSucursal$anio";
        for ($i = 4; $i < count($fechas); $i++) {
            // for ($i=0; $i < 2; $i++) {
            $table = new DatosGenerales();
            $table->uno = $uno[$nombreSucursal][$serie][$i] ?? 0;
            $table->gasto = $gasto[$nombreSucursal][$serie][$i] ?? 0;
            $table->ventas = $ventas[$nombreSucursal][$serie][$i] ?? 0;
            $table->venta_litros = $venta_litros[$nombreSucursal][$serie][$i] ?? 0;
            $table->utilidad_bruta = $utilidad_bruta[$nombreSucursal][$serie][$i] ?? 0;
            $table->ubo = $ubo[$nombreSucursal][$serie][$i] ?? 0;
            $table->eficiencia = $eficiencia[$nombreSucursal][$serie][$i] ?? 0;
            $table->fecha = $fechas[$i];
            $table->sucursales_id = $idSucursal;
            $table->save();
        }
        // $inventario = $data['inventario'];
        // $antiNuevos = $data['inventarioNuevo'];
        // $antiSemi   = $data['inventarioSemi'];
        return response()->json([
            'message' => "Se guardaron los datos de $nombreSucursal 
        con id $idSucursal serie $serie, $estacionanio"
        ]);
    }

    public function insetNissan()
    {
        $content = File::get(base_path('/data.json'));
        $json = json_decode(json: $content, associative: true);
        $data = $json['lille'];//Sudvision 

        $idSucursal = 26; //Id de la sucursal
        $anio = '2025'; //año a registrar 
        $serie = "serie$anio";

        $agencias = [
            'campestre',//0
            'automotriz',//1
            'insugentes',//2
            'universidad',//3
            'azcapotzalco',//4
            'ecatepec',//5
            'pachuca',//6
            'vallejo',//7
        ];

        $sucursal = $data[$agencias[$idSucursal-22]];
        $nombreSucursal = $agencias[$idSucursal-22];
        
        //*Modelo utlidadArea
        $utlidad_area_comercial = $sucursal['departamentos']['comercial'];
        $utlidad_area_postventa = $sucursal['departamentos']['postventa'];
        $area_comercial = $sucursal['comercial'];
        $area_postventa = $sucursal['postventa'];
        //*Modelo Datos Generales 
        $gastos = $sucursal['totalGastos'];
        $uno = $sucursal['uno'];
        $personal = $sucursal['acumuladoPersonal'];
        //*Modelo OrdenesUnidades
        $ordenesServicio = $sucursal['ordenesServicio'];
        $unidadesVendidas = $sucursal['unidadesVendidas'];
        //*Modelo Costos Financieros prestamos
        $costos_financieros = $sucursal['costoFinanciero'];
        $prestamos = $sucursal['prestamos'];
        
        //*Modelo inventarios
        $inventario = $sucursal['inventario'];
        $inventario_nuevo = $sucursal['inventarioNuevo'];
        $inventario_semi = $sucursal['inventarioSemi'];
        //*Modelo complementos
        $bonos = $sucursal['bonos'];
        $complementos = $sucursal['otrosIngresos'];
        $descuentos = $sucursal['descuentos'];
        $cumplimiento = $sucursal['cumplimiento'];
        //*Ventas post venta
        $ventas = $sucursal['ventas'];
        //* Modelo Personal
        $personal = $sucursal['acumuladoPersonal'];

        
        $fechas = [
            "$anio-01-01",
            "$anio-02-01",
            "$anio-03-01",
            "$anio-04-01",
            "$anio-05-01",
            "$anio-06-01",
            "$anio-07-01",
            "$anio-08-01",
            "$anio-09-01",
            "$anio-10-01",
            "$anio-11-01",
            "$anio-12-01"
        ];

        
        //  for ($i = 0; $i < count($fechas); $i++) {
        for ($i=0; $i < 2; $i++) {
          //TODO MODELO DATOS GENERALES
              $datosGenerales = new DatosGenerales();
              $datosGenerales->uno = $uno[$serie][$i] ?? 0;
              $datosGenerales->gasto = $gastos[$serie][$i] ?? 0;
              $tPersonal = $personal['ventas'][$serie][$i] + $personal['usados'][$serie][$i]
              + $personal['refacciones'][$serie][$i] + $personal['servicios'][$serie][$i]
              +$personal['admin'][$serie][$i] + $personal['apvs'][$serie][$i];
              $datosGenerales->personal = $tPersonal ?? 0;
              $datosGenerales->fecha = $fechas[$i];
              $datosGenerales->sucursales_id = $idSucursal;
             $datosGenerales->save();

            //TODO MODELO INVENTARIOS
             $tInventarios = new Inventarios();
             $tInventarios->nuevos = $inventario['nuevos'][$serie][$i] ?? 0;
             $tInventarios->refacciones = $inventario['refacciones'][$serie][$i] ?? 0;
             $tInventarios->seminuevos = $inventario['seminuevos'][$serie][$i] ?? 0;
             $tInventarios->inv_nuevo_101  = $inventario_nuevo['anti101'][$serie][$i] ?? 0;
             $tInventarios->inv_nuevo_201= $inventario_nuevo['anti201'][$serie][$i] ?? 0;
             $tInventarios->inv_nuevo_301= $inventario_nuevo['anti301'][$serie][$i] ?? 0;
             $tInventarios->inv_nuevo_401= $inventario_nuevo['anti401'][$serie][$i] ?? 0;
             $tInventarios->inv_semi_101= $inventario_semi['anti101'][$serie][$i] ?? 0;
             $tInventarios->inv_semi_201= $inventario_semi['anti201'][$serie][$i] ?? 0;
             $tInventarios->inv_semi_301= $inventario_semi['anti301'][$serie][$i] ?? 0;
             $tInventarios->inv_semi_401= $inventario_semi['anti401'][$serie][$i] ?? 0;
             $tInventarios->fecha = $fechas[$i];
             $tInventarios->sucursales_id = $idSucursal;
             $tInventarios->save();

            //TODO MODELO ORDENES UNIDADES
             $tOrdenesUnidades =  new OrdenesUnidades();
             $tOrdenesUnidades->servicio = $ordenesServicio['servicio'][$serie][$i] ?? 0;
             $tOrdenesUnidades->utilidad_servicio = $ordenesServicio['servicioPesos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->hyp = $ordenesServicio['hyp'][$serie][$i] ?? 0;
             $tOrdenesUnidades->utilidad_hyp  = $ordenesServicio['hypPesos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->nuevos  = $unidadesVendidas['nuevos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->utilidad_nuevos  = $unidadesVendidas['nuevosPesos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->flotillas  = $unidadesVendidas['flotillas'][$serie][$i] ?? 0;
             $tOrdenesUnidades->utilidad_flotillas  = $unidadesVendidas['flotillasPesos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->seminuevos  = $unidadesVendidas['seminuevos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->utilidad_seminuevos  = $unidadesVendidas['seminuevosPesos'][$serie][$i] ?? 0;
             $tOrdenesUnidades->fecha = $fechas[$i];
             $tOrdenesUnidades->sucursales_id = $idSucursal;
             $tOrdenesUnidades->save();

            //TODO MODELO UTILIDAD AREA
              $tUtilidadArea =  new UtilidadArea();
              $tUtilidadArea->nuevos = $area_comercial['nuevos'][$serie][$i] ?? 0;
              $tUtilidadArea->flotillas = $area_comercial['flotillas'][$serie][$i] ?? 0;
              $tUtilidadArea->seminuevos = $area_comercial['seminuevos'][$serie][$i] ?? 0;
              $tUtilidadArea->servicio  = $area_postventa['servicios'][$serie][$i] ?? 0;
              $tUtilidadArea->refacciones  = $area_postventa['refacciones'][$serie][$i] ?? 0;
              $tUtilidadArea->hyp  = $area_postventa['hyp'][$serie][$i] ?? 0;
              $tUtilidadArea->area_comercial  = $utlidad_area_comercial[$serie][$i] ?? 0;
              $tUtilidadArea->area_postventa  = $utlidad_area_postventa[$serie][$i] ?? 0;
              $tUtilidadArea->fecha = $fechas[$i];
              $tUtilidadArea->sucursales_id = $idSucursal;
              $tUtilidadArea->save();
            //TODO MODELO PERSONAL
              $tPersonal =  new Personal();
              $tPersonal->ventas = $personal['ventas'][$serie][$i] ?? 0;
              $tPersonal->usados = $personal['usados'][$serie][$i] ?? 0;
              $tPersonal->refacciones = $personal['refacciones'][$serie][$i] ?? 0;
              $tPersonal->servicios  = $personal['servicios'][$serie][$i] ?? 0;
              $tPersonal->admin  = $personal['admin'][$serie][$i] ?? 0;
              $tPersonal->apvs  = $personal['apvs'][$serie][$i] ?? 0;
              $tPersonal->fecha = $fechas[$i];
              $tPersonal->sucursales_id = $idSucursal;
              $tPersonal->save();
            //TODO MODELO VENTAS POST VENTA 
              $tVentaPostVenta =  new VentasPostVenta();
              $tVentaPostVenta->ventas_servicio = $ventas['servicio'][$serie][$i] ?? 0;
              $tVentaPostVenta->total_ventas_ref = $ventas['total_refacciones'][$serie][$i] ?? 0;
              $tVentaPostVenta->refacciones_servicio = $ventas['refacciones_servicio'][$serie][$i] ?? 0;
              $tVentaPostVenta->refacciones_hyp  = $ventas['refacciones_hyp'][$serie][$i] ?? 0;
              $tVentaPostVenta->refacciones_mostrador  = $ventas['refacciones_mostrador'][$serie][$i] ?? 0;
              $tVentaPostVenta->fecha = $fechas[$i];
              $tVentaPostVenta->sucursales_id = $idSucursal;
              $tVentaPostVenta->save();
             //TODO MODELO COMPLEMENTOS
              $tComplementos =  new Complementos();
              $tComplementos->objetivo = $cumplimiento['objetivo'][$serie][$i] ?? 0;
              $tComplementos->cumplimiento = $cumplimiento['cumplimiento'][$serie][$i] ?? 0;
              $tComplementos->porcentaje = $cumplimiento['porcentaje'][$serie][$i] ?? 0;
              $tComplementos->bono_marca  = $bonos['marca'][$serie][$i] ?? 0;
              $tComplementos->bonos  = $complementos['bonos'][$serie][$i] ?? 0;
              $tComplementos->incentivos  = $complementos['incentivos'][$serie][$i] ?? 0;
              $tComplementos->otros  = $complementos['otros'][$serie][$i] ?? 0;
              $tComplementos->descuentos  = $descuentos['descuentos'][$serie][$i] ?? 0;
              $tComplementos->fecha = $fechas[$i];
              $tComplementos->sucursales_id = $idSucursal;
              $tComplementos->save();

             //TODO  COSTOS FINANCIEROS PRESTAMOS
              $tCostosFinacierosPrestamos =  new CostosFinancierosPrestamos();
              $tCostosFinacierosPrestamos->nuevos = $costos_financieros['nuevos'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->flotillas = $costos_financieros['flotillas'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->refacciones = $costos_financieros['refacciones'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->bajio  = $costos_financieros['bajio'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->intercias  = $costos_financieros['intercias'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->plan_piso  = $prestamos['planPiso'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->plan_piso_interes  = $prestamos['planPisoInteres'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->nrf  = $prestamos['nrf'][$serie][$i] ?? 0;
              $tCostosFinacierosPrestamos->nrf_interes  = $prestamos['nrfInteres'][$serie][$i] ?? 0;
        
              $tCostosFinacierosPrestamos->fecha = $fechas[$i];
              $tCostosFinacierosPrestamos->sucursales_id = $idSucursal;
              $tCostosFinacierosPrestamos->save();

         }


        //  $inventario = $data['inventario'];
        //  $antiNuevos = $data['inventarioNuevo'];
        //  $antiSemi   = $data['inventarioSemi'];
        return response()->json([
         'message' => "Se guardaron los datos de $nombreSucursal 
         con id $idSucursal serie $serie",
        //  'data' => $tCostosFinacierosPrestamos,
        ]);
    }

    public function insertRenault()
    {
        $content = File::get(base_path('/data.json'));
        $json = json_decode(json: $content, associative: true);
        $data = $json['lille'];//Sudvision 

        $idSucursal = 29; //Id de la sucursal
        $anio = '2023'; //año a registrar 
        $serie = "serie$anio";
        $mesInicio = 4;

        $agencias = [
            'campestre',//0
            'automotriz',//1
            'insugentes',//2
            'universidad',//3
            'azcapotzalco',//4
            'ecatepec',//5
            'vallejo',//6
            'pachuca',//7
        ];

        $sucursal = $data[$agencias[$idSucursal-22]];
        $nombreSucursal = $agencias[$idSucursal-22];
        
        //*Modelo utlidadArea -TODO RENAULT
        $utlidad_area_comercial = $sucursal['departamentos']['comercial']; //*TODO RENAULT
        $utlidad_area_postventa = $sucursal['departamentos']['postventa'];//*TODO RENAULT
        $area_comercial = $sucursal['comercial']; //*TODO RENAULT
        $area_postventa = $sucursal['postventa']; //*TODO RENAULT

        //*Modelo Datos Generales -TODO RENAULT
        $gastos = $sucursal['gasto']; //*TODO RENAULT
        $uno = $sucursal['uno']; //*TODO RENAULT
        $personal = $data['generales']['acumuladoPersonal']; //*TODO RENAULT

        //*Modelo OrdenesUnidades
        $ordenesServicio = $sucursal['ordenesServicio']; //*TODO RENAULT
        $unidadesVendidas = $sucursal['unidadesVendidas']; //*TODO RENAULT

        //*Modelo Costos Financieros prestamos TODO RENAULT
        $costos_financieros = $sucursal['costoFinanciero'];//*TODO RENAULT
        $prestamos = $data['generales']['prestamos']; //* RENAULT AZCAPOTZALCO
        
        //*Modelo complementos
        $bonos = $sucursal['bonos']; //*TODO RENAULT
        $generales = $data['generales']; //* RENAULT AZCAPOTZALCO
        $otrosIngresos = $generales['otrosIngresos'];
        $bonosMarca = $generales['bonos'];
        $cumplimiento = $generales['cumplimiento'];
        $descuentos = $generales['descuentos'];

        //*Modelo inventarios
        $inventario = $generales['inventario'];//* RENAULT AZCAPOTZALCO
        $inventario_nuevo = $generales['inventarioNuevo']; //* RENAULT AZCAPOTZALCO
        $inventario_semi = $generales['inventarioSemi']; //* RENAULT AZCAPOTZALCO


        //*Ventas post venta
        $ventas = $sucursal['ventas']; //*TODO RENAULT
        //* Modelo Personal
        //$personal = $sucursal['acumuladoPersonal']; //*TODO RENAULT

        
        $fechas = [
            "$anio-01-01",
            "$anio-02-01",
            "$anio-03-01",
            "$anio-04-01",
            "$anio-05-01",
            "$anio-06-01",
            "$anio-07-01",
            "$anio-08-01",
            "$anio-09-01",
            "$anio-10-01",
            "$anio-11-01",
            "$anio-12-01"
        ];

        
        //for ($i = $mesInicio; $i < count($fechas); $i++) {
        for ($i=0; $i < 4; $i++) {
          //TODO MODELO DATOS GENERALES
            $datosGenerales = new DatosGenerales();
            $datosGenerales->uno = $uno[$serie][$i] ?? 0;
            $datosGenerales->gasto = $gastos[$serie][$i] ?? 0;
            $datosGenerales->personal = $personal[$nombreSucursal][$serie][$i] ?? 0;
            $datosGenerales->fecha = $fechas[$i];
            $datosGenerales->sucursales_id = $idSucursal;
            $datosGenerales->save();

            //TODO MODELO INVENTARIOS
            // $tInventarios = new Inventarios();
            // $tInventarios->nuevos = $inventario['nuevos'][$serie][$i] ?? 0;
            // $tInventarios->refacciones = $inventario['refacciones'][$serie][$i] ?? 0;
            // $tInventarios->seminuevos = $inventario['seminuevos'][$serie][$i] ?? 0;
            // $tInventarios->inv_nuevo_101  = $inventario_nuevo['anti101'][$serie][$i] ?? 0;
            // $tInventarios->inv_nuevo_201= $inventario_nuevo['anti201'][$serie][$i] ?? 0;
            // $tInventarios->inv_nuevo_301= $inventario_nuevo['anti301'][$serie][$i] ?? 0;
            // $tInventarios->inv_nuevo_401= $inventario_nuevo['anti401'][$serie][$i] ?? 0;
            // $tInventarios->inv_semi_101= $inventario_semi['anti101'][$serie][$i] ?? 0;
            // $tInventarios->inv_semi_201= $inventario_semi['anti201'][$serie][$i] ?? 0;
            // $tInventarios->inv_semi_301= $inventario_semi['anti301'][$serie][$i] ?? 0;
            // $tInventarios->inv_semi_401= $inventario_semi['anti401'][$serie][$i] ?? 0;
            // $tInventarios->fecha = $fechas[$i];
            // $tInventarios->sucursales_id = $idSucursal;
            // $tInventarios->save();

            //TODO MODELO ORDENES UNIDADES
            //   $tOrdenesUnidades =  new OrdenesUnidades();
            //   $tOrdenesUnidades->servicio = $ordenesServicio['servicio'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->utilidad_servicio = $ordenesServicio['utilidadServicio'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->hyp = $ordenesServicio['hyp'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->utilidad_hyp  = $ordenesServicio['utilidadHyp'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->nuevos  = $unidadesVendidas['nuevos'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->utilidad_nuevos  = $unidadesVendidas['utilidadNuevas'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->flotillas  = $unidadesVendidas['flotillas'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->utilidad_flotillas  = $unidadesVendidas['utilidadFlotillas'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->seminuevos  = $unidadesVendidas['seminuevos'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->utilidad_seminuevos  = $unidadesVendidas['utilidadUsados'][$serie][$i] ?? 0;
            //   $tOrdenesUnidades->fecha = $fechas[$i];
            //   $tOrdenesUnidades->sucursales_id = $idSucursal;
            //   $tOrdenesUnidades->save();

            //TODO MODELO UTILIDAD AREA
            // $tUtilidadArea =  new UtilidadArea();
            // $tUtilidadArea->nuevos = $area_comercial['nuevos'][$serie][$i] ?? 0;
            // $tUtilidadArea->flotillas = $area_comercial['flotillas'][$serie][$i] ?? 0;
            // $tUtilidadArea->seminuevos = $area_comercial['seminuevos'][$serie][$i] ?? 0;
            // $tUtilidadArea->servicio  = $area_postventa['servicio'][$serie][$i] ?? 0;
            // $tUtilidadArea->refacciones  = $area_postventa['refacciones'][$serie][$i] ?? 0;
            // $tUtilidadArea->hyp  = $area_postventa['hyp'][$serie][$i] ?? 0;
            // $tUtilidadArea->area_comercial  = $utlidad_area_comercial[$serie][$i] ?? 0;
            // $tUtilidadArea->area_postventa  = $utlidad_area_postventa[$serie][$i] ?? 0;
            // $tUtilidadArea->fecha = $fechas[$i];
            // $tUtilidadArea->sucursales_id = $idSucursal;
            // $tUtilidadArea->save();
              
            //TODO MODELO PERSONAL
            //   $tPersonal =  new Personal();
            //   $tPersonal->ventas = $personal['ventas'][$serie][$i] ?? 0;
            //   $tPersonal->usados = $personal['usados'][$serie][$i] ?? 0;
            //   $tPersonal->refacciones = $personal['refacciones'][$serie][$i] ?? 0;
            //   $tPersonal->servicios  = $personal['servicios'][$serie][$i] ?? 0;
            //   $tPersonal->admin  = $personal['admin'][$serie][$i] ?? 0;
            //   $tPersonal->apvs  = $personal['apvs'][$serie][$i] ?? 0;
            //   $tPersonal->fecha = $fechas[$i];
            //   $tPersonal->sucursales_id = $idSucursal;
            //   $tPersonal->save();
            //TODO MODELO VENTAS POST VENTA 
            //  $tVentaPostVenta =  new VentasPostVenta();
            //  $tVentaPostVenta->ventas_servicio = $ventas['servicio'][$serie][$i] ?? 0;
            //  $tVentaPostVenta->total_ventas_ref = $ventas['total_refacciones'][$serie][$i] ?? 0;
            //  $tVentaPostVenta->refacciones_servicio = $ventas['refacciones_servicio'][$serie][$i] ?? 0;
            //  $tVentaPostVenta->refacciones_hyp  = $ventas['refacciones_hyp'][$serie][$i] ?? 0;
            //  $tVentaPostVenta->refacciones_mostrador  = $ventas['refacciones_mostrador'][$serie][$i] ?? 0;
            //  $tVentaPostVenta->fecha = $fechas[$i];
            //  $tVentaPostVenta->sucursales_id = $idSucursal;
            //  $tVentaPostVenta->save();
             //TODO MODELO COMPLEMENTOS
            // $tComplementos =  new Complementos();
            // $tComplementos->objetivo =  0;
            // $tComplementos->cumplimiento = 0;
            // $tComplementos->porcentaje = 0;
            // $tComplementos->bono_marca  = 0;
            // $tComplementos->bonos  = $bonos[$serie][$i] ?? 0;
            // $tComplementos->incentivos  =  0;
            // $tComplementos->otros  =  0;
            // $tComplementos->descuentos  =  0;

            // $tComplementos->objetivo = $cumplimiento['objetivo'][$serie][$i] ?? 0;
            // $tComplementos->cumplimiento = $cumplimiento['cumplimiento'][$serie][$i] ?? 0;
            // $tComplementos->porcentaje = $cumplimiento['porcentaje'][$serie][$i] ?? 0;
            // $tComplementos->bono_marca  = $bonosMarca['marca'][$serie][$i] ?? 0;
            // $tComplementos->incentivos  = $otrosIngresos['incentivos'][$serie][$i] ?? 0;
            // $tComplementos->otros  = $otrosIngresos['otros'][$serie][$i] ?? 0;
            // $tComplementos->descuentos  = $descuentos[$serie][$i] ?? 0;
            // $tComplementos->fecha = $fechas[$i];
            // $tComplementos->sucursales_id = $idSucursal;
            // $tComplementos->save();

             //TODO  COSTOS FINANCIEROS PRESTAMOS
            // $tCostosFinacierosPrestamos =  new CostosFinancierosPrestamos();
            // $tCostosFinacierosPrestamos->nuevos = $costos_financieros['nuevos'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->flotillas = $costos_financieros['flotillas'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->refacciones = $costos_financieros['refacciones'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->bajio  = $costos_financieros['bajio'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->intercias  = $costos_financieros['intercias'][$serie][$i] ?? 0;

            // $tCostosFinacierosPrestamos->plan_piso  = $prestamos['planPiso'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->plan_piso_interes  = $prestamos['planPisoInteres'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->nrf  = $prestamos['nrf'][$serie][$i] ?? 0;
            // $tCostosFinacierosPrestamos->nrf_interes  = $prestamos['nrfInteres'][$serie][$i] ?? 0;
        
            // $tCostosFinacierosPrestamos->plan_piso  =  0;
            // $tCostosFinacierosPrestamos->plan_piso_interes  =  0;
            // $tCostosFinacierosPrestamos->nrf  =  0;
            // $tCostosFinacierosPrestamos->nrf_interes  =  0;

            // $tCostosFinacierosPrestamos->fecha = $fechas[$i];
            // $tCostosFinacierosPrestamos->sucursales_id = $idSucursal;
            // $tCostosFinacierosPrestamos->save();

         }


        //  $inventario = $data['inventario'];
        //  $antiNuevos = $data['inventarioNuevo'];
        //  $antiSemi   = $data['inventarioSemi'];
        return response()->json([
         'message' => "Se guardaron los datos de $nombreSucursal 
         con id $idSucursal serie $serie",
        //  'data' => $tCostosFinacierosPrestamos,
        ]);
    }
}
