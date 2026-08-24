<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Throwable;

use Modules\Renault\Transformers\CitasServicioResource;

use Modules\Renault\Models\RenCitasServicio;
use Modules\Renault\Models\RenDetalleGarantia;
use Modules\Renault\Models\RenDetalleTrabajoSolicitado;
use Modules\Renault\Models\RenEntradaVehiculo;
use Modules\Renault\Models\RenInventarioVehiculo;
use Modules\Renault\Models\RenTestigosFotograficos;
use Modules\Renault\Services\CitaServicioService;
use Modules\Renault\Transformers\DatosEntradaResource;

class VisorCitasController extends Controller
{
    protected $citasService;
    public function __construct(
        CitaServicioService $citasService,
    ){
        $this->citasService = $citasService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->citasService->obtenerOProcesarCitas();
        // return response()->json([
        //         'status' => true,
        //         'message' => 'Se ha guardado correctamente la información',
        //         'data' => $data
        //     ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('renault::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $evento = $this->citasService->generarEvento($request->input('citas_servicio_id'), 1);
            $entrada  = $this->storeEntrada($request->input('folio'), $request->input('num_entrada'), $request->input('citas_servicio_id'));

            $inventario = $this->storeInventario(
            $entrada->id,
            $request->input('antena'),              $request->input('espejo'),              $request->input('tapones'),
            $request->input('rines'),               $request->input('tapon_gasolina'),      $request->input('radio'),
            $request->input('encendedor'),          $request->input('tapetes'),             $request->input('llanta_refaccion'),
            $request->input('herramientas'),        $request->input('reflejantes'),         $request->input('extinguidor'),
            $request->input('cables_corriente'),    $request->input('gato'),                $request->input('objetos_valor'),
            $request->input('otros'),               $request->input('vestiduras'),          $request->input('cristales'),
            $request->input('nivel_gasolina')
            );

            if($request->has('trabajos') && !empty($request->input('trabajos'))){
            foreach ($request->input('trabajos') as $trabajo) {
                RenDetalleTrabajoSolicitado::create([
                'descripcion' => $trabajo['descripcion'],
                'partes' => $trabajo['partes'],
                'ren_entrada_vehiculo_id' => $entrada->id,
                ]);
            }
            }

            if($request->has('garantias') && !empty($request->input('garantias'))){
               foreach ($request->input('garantias') as $garantia) {
                RenDetalleGarantia::create([
                'descripcion' => $garantia['descripcion'] ,
                'tiempo' => $garantia['tiempo'],
                'ren_entrada_vehiculo_id' => $entrada->id,
                ]);
            }
            }

            // 3. Procesar y guardar cada foto enviada
            if ($request->has('fotos')) {
                foreach ($request->input('fotos') as $index => $fotoData) {
                    if ($request->hasFile("fotos.{$index}.file")) {
                        $archivo = $request->file("fotos.{$index}.file");

                        $extension = $archivo->getClientOriginalExtension();
                        $nombreArchivo = $request->input('folio') . '_' .$fotoData['categoria'].'_'. $index . '.' . $extension;
                        $path = $archivo->storeAs('renault/citas_servicio', $nombreArchivo, 'local');
                        // Crear registro en la tabla de fotos
                        RenTestigosFotograficos::create([
                            'folio'            => $request->input('folio'),
                            'nombre' =>             $nombreArchivo,
                            'ruta'             => 'renault/citas_servicio/',
                            'ren_entrada_vehiculo_id' => $entrada->id,
                            'categoria'        => $fotoData['categoria'],
                            'media_type'       => $fotoData['mediaType'],
                            'descripcion'      => $fotoData['descripcion'] ?? null,
                        ]);
                    }
                }
            }

            $image = str_replace('data:image/png;base64,', '', $request->input('firma'));
            $image = str_replace(' ', '+', $image);
            Storage::disk('local')->put("renault/citas_servicio/".$request->input('folio')."_firma.png", base64_decode($image));


            RenCitasServicio::where('id', $request->input('citas_servicio_id'))->update([
                'estatus' => 'AT',
                'email' =>$request->input('correo'),
                'telefono' =>$request->input('telefono'),
                ]);

            $this->citasService->finalizarEvento($evento->id);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Servicio y fotografías procesados correctamente.',
                // 'cita_id' => $cita->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error'   => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeEntrada($folio, $noEntrada, $idCita){
        $entrada = RenEntradaVehiculo::create([
                "fecha" => date('Y-m-d H:i:s'),
                "folio" => $folio,
                "num_entrada" => $noEntrada,
                "ren_citas_servicio_id" => $idCita,
            ]);

        return $entrada;
    }
    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $date = date('Y-m-d');

        $agencia = match($id){
            '7064' => 1, '7062' => 2, '7063' => 3, '7061' => 4, default => $id,
        };

        $citas = $this->getCitas($agencia, null, $date);

        return response()->json([
            'status' => true,
            'message' => '',
            'data' =>  CitasServicioResource::collection($citas)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('renault::edit');
    }

    public function getDatosIngreso($id){
        $cita = $this->getDatosOrdenServicio($id);
        if($cita && $cita->Datos){
            return response()->json([
                'status' => 'success',
                'message' => '',
                'data' =>  new DatosEntradaResource($cita)
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Datos de entrada no encontrados',
                'data' =>  []
            ]);
        }

    }

    public function descargarPdfOrdenServicio($id){
        $cita = $this->getDatosOrdenServicio($id);
        if($cita){
            $pdf = new OrdenServicioPdfController();
        $file = $pdf->OrdenServicioFormatoInterno($cita);
        $fileName = 'orden_de_reparacion_mecanica_'.($cita->Datos->num_entrada ?? 0).'.pdf';

        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
        }

    }

    public function datosFiltrados($intercomapania, $aps, $fechaInicial, $fechaFinal ){
        $agencia = match($intercomapania){
            '7064' => 1, '7062' => 2, '7063' => 3, '7061' => 4, 'todas' => null, default => $intercomapania,
        };

        $apsDef = $aps == 'todos' ? null : $aps;

        $citas = $this->getCitas($agencia, $apsDef, $fechaInicial, $fechaFinal);

        return response()->json([
            'status' => true,
            'message' => '',
            'data' =>  CitasServicioResource::collection($citas)
        ]);

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
       DB::beginTransaction();
            try {
                // 1. Obtener la entrada del vehículo existente
                $entrada = RenEntradaVehiculo::findOrFail($id);
                $folio = $request->input('folio', $entrada->folio);

                // 2. INHABILITAR TESTIGOS RETIRADOS
                // Recibimos un arreglo con las IDs de las fotos que el usuario DECIDIÓ CONSERVAR
                $fotosConservadasIds = $request->input('fotos_existentes_ids', []);

                // Inhabilitamos todos los testigos de esta entrada que NO estén en la lista conservada
                RenTestigosFotograficos::where('ren_entrada_vehiculo_id', $entrada->id)
                    ->whereNotIn('id', $fotosConservadasIds)
                    ->update([
                        'activo' => 0 // O $table->boolean('activo')->default(false)
                    ]);

                // 3. PROCESAR Y AGREGAR NUEVOS TESTIGOS FOTOGRÁFICOS
                if ($request->has('nuevas_fotos')) {
                    foreach ($request->input('nuevas_fotos') as $index => $fotoData) {
                        if ($request->hasFile("nuevas_fotos.{$index}.file")) {
                            $archivo = $request->file("nuevas_fotos.{$index}.file");

                            $extension = $archivo->getClientOriginalExtension();
                            // Usamos time() o microtime() para evitar colisión de nombres en actualizaciones repetidas
                            $nombreArchivo = $folio . '_' . $fotoData['categoria'] . '_' . time() . '_' . $index . '.' . $extension;
                            $path = $archivo->storeAs('renault/citas_servicio', $nombreArchivo, 'local');

                            // Crear registro en la base de datos
                            RenTestigosFotograficos::create([
                                'folio'                   => $folio,
                                'nombre'                  => $nombreArchivo,
                                'ruta'                    => 'renault/citas_servicio/',
                                'ren_entrada_vehiculo_id' => $entrada->id,
                                'categoria'               => $fotoData['categoria'],
                                'media_type'              => $fotoData['mediaType'],
                                'descripcion'             => $fotoData['descripcion'] ?? null,
                                'activo'                 => 1
                            ]);
                        }
                    }
                }

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Testigos fotográficos actualizados correctamente.'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'error'  => 'Error al actualizar los testigos: ' . $e->getMessage()
                ], 500);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function getFile($fileName)
    {
        $path = storage_path("app/renault/citas_servicio/$fileName");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }


    private function getCitas($agencia = null, $empleadoId = null, $fechaInicio = null, $fechaFin = null)
    {
        $fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : null;
        $fechaFin    = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : null;


        return RenCitasServicio::query()
            ->when($agencia && $agencia != 333, fn($q) => $q->where('agencia_id', $agencia))
            ->when($empleadoId, fn($q) => $q->where('id_intranet', $empleadoId))
            ->when($fechaInicio && $fechaFin, fn($q) => $q->whereBetween('fecha', [$fechaInicio, $fechaFin]))
            ->when($fechaInicio && !$fechaFin, fn($q) => $q->where('fecha', 'like', $fechaInicio->format('Y-m-d')."%"))
            // ->when($fechaInicio && !$fechaFin, function ($q) use ($fechaInicio) {
            //         $q->where('fecha', 'like', $fechaInicio . "%");
            // })
            ->get();
    }

    private function getDatosOrdenServicio($idCita){
        return RenCitasServicio::with('Datos.Inventario','Datos.TestigosFotograficos', 'Datos.trabajosSolicitados', 'Datos.garantias', 'eventosCita')->where('id', $idCita)->first();
    }



   public function getApsByAgencia($idAgencia){

        $intercompania = match($idAgencia)
                    { 1 => '7064', 2 => '7062', 3 => '7063',  4 => '7061', default => $idAgencia };

       $data = DB::connection('intranet')
                                ->table('glpi_users')
                                ->where('name','like', '%aps%')
                                ->where('intercompania', $intercompania)
                                ->where('is_active', 1)
                                ->get(['id','name', 'firstname', 'realname', 'intercompania' ]);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Aps Recuperados correctamente'
        ]);

    }

    public function storeInventario( $id_entrada, $antena, $espejo, $tapones, $rines, $tapon_gasolina, $radio,
    $encendedor, $tapetes, $llanta_refa, $herramientas, $reflejantes, $extinguidor, $cables_corriente, $gato, $objetos_valor,
       $otros, $vestiduras, $cristales, $nivel_gasolina ){
       $inventario = RenInventarioVehiculo::create([
                'antena' =>$antena ? 1 : 0,
                'espejo' => $espejo? 1 : 0,
                'tapones' => $tapones? 1 : 0,
                'rines' => $rines? 1 : 0,
                'tapon_gasolina' => $tapon_gasolina? 1 : 0,
                'radio' => $radio? 1 : 0,
                'encendedor' => $encendedor? 1 : 0,
                'tapetes' => $tapetes? 1 : 0,
                'llanta_refaccion' => $llanta_refa? 1 : 0,
                'herramientas' => $herramientas? 1 : 0,
                'reflejantes' => $reflejantes? 1 : 0,
                'extinguidor' => $extinguidor? 1 : 0,
                'cables_corriente' => $cables_corriente? 1 : 0,
                'gato' => $gato ? 1 : 0,
                'objetos_valor' => $objetos_valor? 1 : 0,
                'otros' => $otros ?? 0,
                'vestiduras' => $vestiduras ?? 0,
                'cristales' => $cristales ?? 0,
                'ren_entrada_vehiculo_id' => $id_entrada,
                'nivel_gasolina' => $nivel_gasolina ?? 0
            ]);

        return $inventario;
    }

}
