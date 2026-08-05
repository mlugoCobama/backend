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
use Modules\Renault\Transformers\DatosEntradaResource;

class VisorCitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [];
        $date = date('Ymd');
        $empleadosCache = [];
        for ($j=1; $j < 5; $j++) {
            $citas = DB::connection('renault')
                    ->table('Se_Citas')
                    ->select(
                        'Se_Citas.citas_folio',
                        'Se_Citas.citas_empl_clave',
                        'empleados.empl_nombre',
                        'Se_Citas.citas_fechacita',
                        'Se_Citas.citas_nombre',
                        'Se_Citas.citas_apaterno',
                        'Se_Citas.citas_amaterno',
                        'Se_Citas.citas_modelo',
                        'Se_Citas.citas_tipo',
                        'Se_Citas.citas_placas',
                        'Se_Citas.citas_observaciones',
                        'Se_Citas.citas_Color1',
                        'Se_Citas.citas_AnioModelo',
                        'Se_Citas.citas_status',
                        'Se_Citas.citas_TipoCita',
                        'Se_Citas.citas_NoSerie',
                        'Se_Citas.citas_TelefonoContacto',
                        'Se_Citas.citas_Domicilio',
                        'Se_Citas.citas_Kilometraje',
                        'Se_Citas.citas_email',
                        'Se_Citas.citas_RFC',
                        )
                    ->join('empleados', 'Se_Citas.citas_empl_clave', '=', 'empleados.empl_clave')
                    ->where('Se_Citas.citas_idagencia', '=',$j)
                    ->where('Se_Citas.citas_status', '<>', 'BO')
                    ->whereBetween('Se_Citas.citas_fechacita', [$date.' 00:00:00.000',$date.' 23:59:59.997'])
                    ->orderBy('Se_Citas.citas_fechacita', 'asc')
                    ->orderBy('Se_Citas.citas_empl_clave', 'asc')
                    ->get();

                    foreach ($citas as $cita) {
                        $empleadoClave = $cita->citas_empl_clave;
                        $empleadoNombre = $cita->empl_nombre;

                        // Si no está en cache, lo consultamos y lo guardamos
                        if (!isset($empleadosCache[$empleadoClave])) {
                            $empleadoId = $this->getAps($empleadoNombre, $j);
                            $empleadosCache[$empleadoClave] = $empleadoId ??  null;
                        }

                        // Ahora ya puedes usar $empleadosCache[$empleadoClave] sin repetir consulta
                        $cita->empleado_id_intranet = $empleadosCache[$empleadoClave];
                    }



         for ($i = 0; $i < count($citas); $i++) {

             $existe = RenCitasServicio::where('folio', $citas[$i]->citas_folio)->get();

             if ($existe->count() == 0) {
                 $citaCita = new RenCitasServicio();
                 $citaCita->folio = $citas[$i]->citas_folio;
                 $citaCita->empleado_id = $citas[$i]->citas_empl_clave;
                 $citaCita->fecha = $citas[$i]->citas_fechacita;
                 $citaCita->nombre = $citas[$i]->citas_nombre;
                 $citaCita->apellido_paterno = $citas[$i]->citas_apaterno;
                 $citaCita->apellido_materno = $citas[$i]->citas_amaterno;
                 $citaCita->rfc = $citas[$i]->citas_RFC;
                 $citaCita->telefono = $citas[$i]->citas_TelefonoContacto;
                 $citaCita->domicilio = $citas[$i]->citas_Domicilio;
                 $citaCita->email = $citas[$i]->citas_email;
                 $citaCita->vin = $citas[$i]->citas_NoSerie;
                 $citaCita->modelo = $citas[$i]->citas_modelo;
                 $citaCita->placas = $citas[$i]->citas_placas;
                 $citaCita->color = $citas[$i]->citas_Color1;
                 $citaCita->tipo = $citas[$i]->citas_tipo;
                 $citaCita->anio = $citas[$i]->citas_AnioModelo;
                 $citaCita->kilometraje = $citas[$i]->citas_Kilometraje;
                 $citaCita->observaciones = $citas[$i]->citas_observaciones;
                 $citaCita->tipo_cita = $citas[$i]->citas_TipoCita;
                 $citaCita->estatus = $citas[$i]->citas_status;
                 $citaCita->agencia_id = $j;
                 $citaCita->id_intranet = $citas[$i]->empleado_id_intranet;
                 $citaCita->save();
             }
         }

            // $data[$j] = $citas;
        }
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
        try {
            /**
             * Insertamos la entrada
             */
            $entrada = RenEntradaVehiculo::create([
                "fecha" => date('Y-m-d H:i:s'),
                "folio" => $request->form['num_entrada'],
                "num_entrada" => $request->form['num_entrada'],
                "ren_citas_servicio_id" => $request->form['citas_servicio_id'],
            ]);
            /**
             * Insertamos el inventario del vehiculo
             */
            RenInventarioVehiculo::create([
                'antena' => $request->form['antena'],
                'espejo' => $request->form['espejo'],
                'tapones' => $request->form['tapones'],
                'rines' => $request->form['rines'],
                'tapon_gasolina' => $request->form['tapon_gasolina'],
                'radio' => $request->form['radio'],
                'encendedor' => $request->form['encendedor'],
                'tapetes' => $request->form['tapetes'],
                'llanta_refaccion' => $request->form['llanta_refaccion'],
                'herramientas' => $request->form['herramientas'],
                'reflejantes' => $request->form['reflejantes'],
                'extinguidor' => $request->form['extinguidor'],
                'cables_corriente' => $request->form['cables_corriente'],
                'gato' => $request->form['gato'],
                'objetos_valor' => $request->form['objetos_valor'],
                'otros' => $request->form['otros'],
                'vestiduras' => $request->form['vestiduras'],
                'cristales' => $request->form['cristales'],
                'ren_entrada_vehiculo_id' => $entrada->id,
                'nivel_gasolina' => $request->form['nivel_gasolina'],
            ]);

            foreach ($request->trabajos as $trabajo) {
                RenDetalleTrabajoSolicitado::create([
                'descripcion' => $trabajo['descripcion'],
                'partes' => $trabajo['partes'],
                'ren_entrada_vehiculo_id' => $entrada->id,
                ]);
            }

            foreach ($request->garantias as $garantia) {
                RenDetalleGarantia::create([
                'descripcion' => $garantia['descripcion'] ,
                'tiempo' => $garantia['tiempo'],
                'ren_entrada_vehiculo_id' => $entrada->id,
                ]);
            }

            // foreach( $request->fotos as $foto) {

            //     $image = $foto['webviewPath'];  // your base64 encoded
            //     $image = str_replace('data:image/jpeg;base64,', '', $image);
            //     $image = str_replace(' ', '+', $image);
            //     Storage::disk('local')->put("renault/citas_servicio/".$foto['filepath'], base64_decode($image));
            //     /**
            //      * Insertamos los testigos fotograficos
            //      */
            //     RenTestigosFotograficos::create([
            //         "folio" => $request->form['folio'],
            //         "ruta" => "renault/citas_servicio/",
            //         "nombre" => basename($foto['filepath']),
            //         'ren_entrada_vehiculo_id' => $entrada->id
            //     ]);

            // }

            foreach ($request->fotos as $foto) {
                    $webviewPath = $foto['webviewPath'];
                    $filepath = $foto['filepath']; // nombre de archivo con extensión, ej: 14762_..._x.png

                    $isDataUri = preg_match('/^data:([\w\/\+\-]+);base64,(.*)$/s', $webviewPath, $matches);

                    if ($isDataUri) {
                        // --- Imagen (o video) enviado como data URI base64 ---
                        $mimeType = $matches[1];   // ej: image/png, image/jpeg, video/mp4
                        $base64Data = $matches[2];

                        // Corrige espacios que a veces reemplazan el '+' al transmitir por URL/form
                        $base64Data = str_replace(' ', '+', $base64Data);

                        $decoded = base64_decode($base64Data, true);

                        if ($decoded === false || strlen($decoded) === 0) {
                            Log::warning("No se pudo decodificar base64 para archivo: {$filepath}");
                            continue; // evitamos guardar un archivo corrupto/vacío
                        }

                        Storage::disk('local')->put(
                            "renault/citas_servicio/" . $filepath,
                            $decoded
                        );

                    }
                    //  elseif (filter_var($webviewPath, FILTER_VALIDATE_URL) || Str::startsWith($webviewPath, ['http://', 'https://', 'blob:'])) {
                    //     // --- Caso: viene como URL/blob (ej. video con convertFileSrc) ---
                    //     // Si tu frontend en su lugar sube el archivo real por multipart, este bloque
                    //     // no debería ejecutarse; ver nota más abajo sobre el enfoque recomendado.
                    //     Log::warning("webviewPath es una URL/blob, no un data URI, no se puede decodificar en backend: {$filepath}");
                    //     continue;

                    // }
                    else {
                        Log::warning("Formato de webviewPath no reconocido para archivo: {$filepath}");
                        continue;
                    }

                    RenTestigosFotograficos::create([
                        "folio" => $request->form['folio'],
                        "ruta" => "renault/citas_servicio/",
                        "nombre" => basename($filepath),
                        'ren_entrada_vehiculo_id' => $entrada->id
                    ]);
                }
            /**
             * Guardarmos la firma
             */
            $image = str_replace('data:image/png;base64,', '', $request->firma);
            $image = str_replace(' ', '+', $image);
            Storage::disk('local')->put("renault/citas_servicio/".$request->form['folio']."_firma.png", base64_decode($image));

            RenCitasServicio::where('id', $request->form['citas_servicio_id'])->update([
                'estatus' => 'AT',
                'email' => $request->form['correo'],
                'telefono' => $request->form['telefono'],
                ]);


            return response()->json([
                'status' => true,
                'message' => 'Se ha guardado correctamente la información',
                'data' => []
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Se ha presentado un problema al guardar la información',
                'data' => []
            ]);
        }


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
        //
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
        return RenCitasServicio::with('Datos.Inventario','Datos.TestigosFotograficos', 'Datos.trabajosSolicitados', 'Datos.garantias')->where('id', $idCita)->first();
    }

    private function getAps($empleadoNombre, $idAgencia){
        $intercompania = match($idAgencia){
                                1 => '7064', 2 => '7062', 3 => '7063',  4 => '7061', default => $idAgencia,
                        };
       return DB::connection('intranet')
                                ->table('glpi_users')
                                ->where('firstname', $empleadoNombre)
                                ->where('name','like', '%aps%')
                                ->where('intercompania', $intercompania)
                                ->value('id');
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


}
