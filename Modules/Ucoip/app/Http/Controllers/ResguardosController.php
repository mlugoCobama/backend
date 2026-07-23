<?php

namespace Modules\Ucoip\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Ucoip\Models\HardwareUcoip;
use Modules\Ucoip\Models\Resguardo;
use Modules\Ucoip\Services\HardwareService;
use Modules\Ucoip\Services\PdfResguardoService;
use Modules\Ucoip\Services\ResguardosService;
use Modules\Ucoip\Transformers\ResguardoResource;

class ResguardosController extends Controller
{


    private $resguardoPDFService;
    private $resguardoService;
    private $hwService;

    public function __construct(  
        PdfResguardoService $resguardoPdfService,
        ResguardosService $resguardoService,
        HardwareService $hwService) {

        $this->resguardoService = $resguardoService;
        $this->resguardoPDFService = $resguardoPdfService;
        $this->hwService = $hwService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resguardo = Resguardo::with(['empresa', 'detalles.hardware.tipoHardware'])->where('id',40)->first();
        $usuario = DB::connection('intranet')->select('CALL SP_GetUsuarioId(?)', [$resguardo->id_usuario_asignado]);
        $nombreUsuario = $usuario ? $usuario[0]->firstname.' '.$usuario[0]->realname : 'Dato No Disponible ';
        $email = $usuario ? $usuario[0]->name .' - '. $usuario[0]->area : 'Dato No Disponible ' ;
        $empresa = $usuario ? $usuario[0]->empresa : 'Dato No Disponible ';
        
        $file = $this->resguardoPDFService->generarPdfResguardo($resguardo, $nombreUsuario, $email, $empresa);

        $fileName = 'Resguardo'.'.pdf';
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ucoip::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $this->resguardoService->asignarRecurso($data['hardware'], null, $data['id']);
        $this->hwService->updateEstatusHardware($data['hardware'], 2 );


        return response()->json([
            'message' => 'Activo asignado correctamente',
            'data' => [],
            'status' => 'success'
        ]);


    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $resguardo = HardwareUcoip::with(['hardware.tipoHardware'])->where('glpi_user_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $resguardo,
            'message' => 'Datos recuperados correctamente',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ucoip::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
// $resguardo = Resguardo::with(['empresa', 'detalles.hardware.tipoHardware'])->where('id',40)->first();
        $detalles = HardwareUcoip::with(['hardware.tipoHardware'])->whereIn('id', $data['idSleccionados'])->get();

        
        $usuario = DB::connection('intranet')->select('CALL SP_GetUsuarioId(?)', [$id]);
        $nombreUsuario = $usuario ? $usuario[0]->firstname.' '.$usuario[0]->realname : 'Dato No Disponible ';
        $email = $usuario ? $usuario[0]->name .' - '. $usuario[0]->area : 'Dato No Disponible ' ;
        $empresa = $usuario ? $usuario[0]->empresa : 'Dato No Disponible ';
        
        $file = $this->resguardoPDFService->generarPdfResguardo($detalles, $nombreUsuario, $email, $empresa);

        $fileName = 'Resguardo'.'.pdf';
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Filename', $fileName)
            ->header('Access-Control-Expose-Headers', 'X-Filename');
        //  return $resguardo;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $asignacion = HardwareUcoip::find($id);
        if($asignacion){
            $asignacion->fecha_fin = now();
            $asignacion->estatus = 0;
            $asignacion->save();
            $this->hwService->updateEstatusHardware($asignacion->ucoip_hardware_id, 1);
        }


        return response()->json([
            'status' => 'success',
            'data' => [],
            'messages' => 'Activo retirado correctamente'
        ]);
    }
}
