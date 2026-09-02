<?php

namespace Modules\Renault\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\EncuestaCalificacionBajaMail;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Renault\Models\RenEncuestaCita;
use Modules\Renault\Models\RenPreguntasEncuesta;
use Modules\Renault\Models\RenRespuestasEncuesta;
use Modules\Renault\Services\PdfEncuestaSatisfaccionService;

class EncuestaCitaController extends Controller
{

    protected $pdfEncuesta;
    public function __construct(
        PdfEncuestaSatisfaccionService $pdfEncuesta,
    ){
        $this->pdfEncuesta = $pdfEncuesta;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = RenPreguntasEncuesta::get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Datos recuperados correctamente'
        ]);
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

        $resultado = DB::transaction(function () use ($request) {


            $encuestaExistente = RenEncuestaCita::where(
                'ren_citas_servicio_id',
                $request->idCita
            )->exists();

            if ($encuestaExistente) {
                throw new \Exception(
                    'La cita ya cuenta con una encuesta registrada.'
                );
            }


            $encuestaCita = new RenEncuestaCita();

            $encuestaCita->ren_citas_servicio_id = $request->idCita;
            $encuestaCita->fecha = now();

            $encuestaCita->save();

            $respuestasBajas = [];


            foreach ($request->respuestas as $respuestaData) {

                $respuesta = new RenRespuestasEncuesta();

                $respuesta->ren_encuesta_cita_id = $encuestaCita->id;
                $respuesta->ren_preguntas_encuesta_id = $respuestaData['preguntaId'];
                $respuesta->motivo = $respuestaData['motivo'] ?? null;
                $respuesta->puntuacion = $respuestaData['puntuacion'];

                $respuesta->save();


                if ($respuesta->puntuacion < 5) {
                    $respuestasBajas[] = [
                        'texto_pregunta' => $respuesta->pregunta->texto,
                        'preguntaId' => $respuesta->ren_preguntas_encuesta_id,
                        'puntuacion' => $respuesta->puntuacion,
                        'motivo' => $respuesta->motivo,
                    ];
                }
            }

            return [
                'encuesta' => $encuestaCita,
                'respuestasBajas' => $respuestasBajas,
            ];
        });


        if (count($resultado['respuestasBajas']) > 0) {

            Mail::to('mlugo@cobama.com.mx')
                ->send(
                    new EncuestaCalificacionBajaMail(
                        $resultado['encuesta'],
                        $resultado['respuestasBajas']
                    )
                );
        }

        return response()->json([
            'message' => 'Encuesta guardada correctamente',
            'data' => $resultado['encuesta'],
            'status' => 'success'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'message' => $e->getMessage(),
            'data' => [],
            'status' => 'error'
        ], 422);
    }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('renault::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('renault::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
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

    public function descargarPdfEncuesta($id){
        // $cita = $this->getDatosOrdenServicio($id);
        // if($cita){

        $file = $this->pdfEncuesta->generarPdf($id);
        // $file = $pdf->OrdenServicioFormatoInterno($cita);
        $fileName = 'encuesta_de_satisfaccion.pdf';

        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
        // }

    }
}
