<?php

namespace Modules\Ucoip\Http\Controllers;

use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Ucoip\Models\CatAreas;
use Modules\Ucoip\Models\CatEmpresas;
use Modules\Ucoip\Models\CatRecursos;
use Modules\Ucoip\Models\CatSistemas;
use Modules\Ucoip\Models\TitularesUcoip;
use Modules\Ucoip\Models\Ucoip;
use Modules\Ucoip\Models\UsuarioPuesto;
use Modules\Ucoip\Services\CifradoService;
use Modules\Ucoip\Services\GlpiService;
use Symfony\Component\CssSelector\Node\FunctionNode;

class UcoipController extends Controller
{
    protected $cifradoService;
    protected $glpiService;
    public function __construct(
        CifradoService $cifradoService,
        GlpiService $glpiService,
    ){
        $this->cifradoService = $cifradoService;
        $this->glpiService = $glpiService;
    }


    public function index()
    {

        $user = DB::connection('intranet')
                ->table('glpi_users')
                ->join('glpi_entities', 'glpi_users.intercompania', '=', 'glpi_entities.intercompania')
                ->leftjoin('glpi_directorio_puestos', 'glpi_users.id_puesto_directorio', '=', 'glpi_directorio_puestos.id_glpi_directorio_puestos')
                ->leftjoin('glpi_directorio_area', 'glpi_users.id_areas_directorio', '=', 'glpi_directorio_area.id_glpi_directorio_area')
                ->select('glpi_users.id',
                        'glpi_users.name',
                        'glpi_users.realname',
                        'glpi_users.firstname',
                        'glpi_users.intercompania',
                        'glpi_entities.name as empresa',
                        'glpi_directorio_puestos.nombre as puesto',
                        'glpi_directorio_area.nombre as area')
                ->where('glpi_users.is_active', '1')
                ->where('glpi_users.id', '<>', 344)
                ->where('glpi_users.id', '<>', 29)
                ->where('glpi_users.id', '<>', 22)
                ->get();

        // $user =  $this->queryPuestosUsuarios();


        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $user
        ]);


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
        $data =  $request->all();

        if(!empty($data['idUcoip'])){
            $ucoip = Ucoip::find($data['idUcoip']);
        }else{
            $ucoip = new Ucoip();
        }

        $ucoip->ucoip =  $data['ucoip'];
        if(!empty($data['password'])){
            $ucoip->contrasenia = $this->cifradoService->encrypt($data['password']);
        }
        $ucoip->user_id =  $data['id'];
        $ucoip->ucoip_cat_puestos =  $data['puesto_id'];
        $ucoip->cat_empresa_id =  $this->matchEmpresa($data['intercompania'])->id ?? 15;
        $ucoip->save();

        $this->glpiService->updateGlpiUser($data['id'], $data['nombre'], $data['apellidos'], $data['password'], $data['area_id'],$data['departamento_id'],$data['puesto_id'] );

        $ultimoTitular = TitularesUcoip::where('ucoip_ucoip_id', $ucoip->id)->latest('id')->first();

            $nuevoNombre = trim($data['nombre'].' '.$data['apellidos']);
            if (!$ultimoTitular) {
                // Es la primera asignación
                $this->storeTitular($ucoip->id, $data);
            } elseif ($ultimoTitular->nombre_titular != $nuevoNombre) {
                // Finalizar el registro anterior
                $ultimoTitular->fecha_fin = now();
                $ultimoTitular->save();
                // Crear el nuevo titular
                $this->storeTitular($ucoip->id, $data);
            }


        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'Ucoip guardado correctamente'
        ]);


    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
       $ucoip = Ucoip::with(['puesto.departamento.area', 'extensiones'])->where('user_id', $id)->first();

       return response()->json([
        'status' => 'success',
        'data' => $ucoip,
        'message' => 'Ucoip recuperado correctamente'
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

    public function queryPuestosUsuarios(){
        $data = UsuarioPuesto::with(['userGlpi', 'puesto.departamento.area'])->get();
        return $data;
    }

    public function getPassword(int $id)
    {
        $ucoip = Ucoip::findOrFail($id);
        $password = $this->cifradoService->decrypt($ucoip->contrasenia);

        return response()->json([
            'success' => true,
            'data' => $password,
            'message' => 'Dato recuperado correctamente'
        ]);
    }


    public function getCatalogosUcoip(){
        $data =[
           'areas' => CatAreas::with(['departamentos.puestos'])->get(),
           'sistemas' => CatSistemas::get(),
           'recursos' => CatRecursos::get(),
        ];

        return response()->json([
        'status'  => 'success',
        'data'    => $data,
        'message' => 'Catalogos recuperados Correctamente'
        ]);
    }

    public function matchEmpresa($intercompania){
        $empresa =  CatEmpresas::where('intercompania', $intercompania)->first();
        return $empresa;
    }

    public function queryUsuariosRenault(){

        $data=  DB::connection('intranet')->select("SELECT id, name, realname, firstname, intercompania  FROM SOPORTEZM.glpi_users where  intercompania in (7051,712,710) and is_active = 1;");
        return $data;
    }

    public function storeUcoipFromQuery(){
                $user =  $this->queryUsuariosRenault();

                foreach ($user as $item) {

                $correo = $item->name;
                $partes = explode('@', $correo);
                $usuario = $partes[0];

                    $ucoip = new Ucoip();
                    $ucoip->ucoip = $usuario;
                    $ucoip->user_id = $item->id;
                    $ucoip->cat_empresa_id =  $this->matchEmpresa($item->intercompania)->id;
                    $ucoip->save();
                }
    }

    public function storeTitular( $idUcoip , $data){
        $titular = new TitularesUcoip();
        $titular->ucoip_ucoip_id = $idUcoip;
        $titular->nombre_titular = $data['nombre'].' '.$data['apellidos'];
        $titular->correo = $data['name'];
        $titular->puesto = $data['puesto'];
        $titular->empresa = $data['empresa'];
        $titular->fecha_incio = now();
        $titular->save();
    }
}
