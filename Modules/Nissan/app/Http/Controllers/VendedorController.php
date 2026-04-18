<?php

namespace Modules\Nissan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Nissan\Models\ComDepartamento;
use Modules\Nissan\Models\ComTipoVendedor;
use Modules\Nissan\Models\Vendedor;
use Modules\Nissan\Services\ComisionesService;
use Modules\Nissan\Transformers\VendedorResource;

class VendedorController extends Controller
{
    protected $comService;
    public function __construct(ComisionesService $comService)
    {
        $this->comService = $comService;
    }
    /**
     * Recupera los vendedores activos
     */
    public function index()
    {
       $catTiposVendedores = ComTipoVendedor::active()->get();
       $catDptosVendedores = ComDepartamento::active()->get();

       $data = [
        'tipos' => $catTiposVendedores,
        'departamentos' => $catDptosVendedores,
       ];

        return response()->json([
            'status' => 'success',
            'message' => 'datos recuperados correctamente',
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nissan::create');
    }

    /**
     * Almacena el registro de un nuevo vendedor
     */
    public function store(Request $request)
    {
        $data =  $request->all();
        $vendedor = new Vendedor();
        $vendedor->tipo = $data['tipo'];
        $vendedor->nro_vendedor_as = $data['nroAutoSystem'];
        $vendedor->clave = $data['clave'];
        $vendedor->nombre = $data['nombre'];
        $vendedor->agencia = $this->comService->parseAgencia($data['agencia']);
        $vendedor->com_departamentos_id = $data['departamento'];
        $vendedor->com_tipo_vendedor_id = $data['tipo'];
        $vendedor->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor agregado correctamente',
            'data' => []
        ]);
    }

    /**
     * Muestra registros de vendedores por agencia
     */
    public function show($id)
    {
        if($id == 333 || $id == 'todos'){
            $data = Vendedor::with(['tipoVendedor', 'departamento'])
            ->active()->orderBy('nombre', 'asc')->get();
        }else{
            $idParseado = $this->comService->parseAgencia($id);
           $data = Vendedor::with(['tipoVendedor', 'departamento'])
            ->where('agencia', $idParseado)
            ->active()
            ->orderBy('nombre', 'asc')
            ->get();

        }
        

        return response()->json([
            'status' => 'success',
            'message' => 'Datos recuperados correctamente',
            'data' => VendedorResource::collection($data),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('nissan::edit');
    }

    /**
     * Actualiza el registro del vendedor
     */
    public function update(Request $request, $id)
    {
        $data =  $request->all();
        $vendedor = Vendedor::find($id);
        if($vendedor){
            $vendedor->tipo = $data['tipo'];
            $vendedor->nro_vendedor_as = $data['nroAutoSystem'];
            $vendedor->clave = $data['clave'];
            $vendedor->nombre = $data['nombre'];
            $vendedor->com_departamentos_id = $data['departamento'];
            $vendedor->com_tipo_vendedor_id = $data['tipo'];
            $vendedor->agencia = $this->comService->parseAgencia($data['agencia']);
            $vendedor->save();
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor actualizado correctamente',
            'data' => []
        ]);
    }

    /**
     * Marca como inactivo el registro del vendedor
     */
    public function destroy($id)
    {
        $vendedor = Vendedor::find($id);
        if($vendedor){
            $vendedor->activo = 0;
            $vendedor->save();
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Vendedor borrado correctamente',
            'data' => []
        ]);
    }

    public function importCsv(Request $request)
    {
        $file = $request->file('file');

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                // Asumiendo que el CSV tiene columnas: numero_vendedor, agencia, nombre, email
                $data = array_combine($header, $row);

                // Buscar registro por numero_vendedor y agencia
                $vendedor = Vendedor::where('nro_vendedor_as', (int) $data['NUMERO DE VENDEDOR'])
                                    ->where('agencia', $data['Agencia'])
                                    ->first();

                if ($vendedor) {
                    // Actualizar si existe
                    $vendedor->update([
                        'tipo' => $data['TIPO VEND'] == 'EXTERNO' ? 2 : 1,
                        'clave' => $data['INICIALES EN SISTEMA'],
                        'nombre' => $data['Vendedor'],
                    ]);
                } else {
                    // Crear si no existe
                    Vendedor::create([
                        'tipo' => $data['TIPO VEND'] == 'EXTERNO' ? 2 : 1,
                        'nro_vendedor_as' => (int) $data['NUMERO DE VENDEDOR'],
                        'clave' => $data['INICIALES EN SISTEMA'],
                        'nombre' => $data['Vendedor'],
                        'agencia' => $data['Agencia'],
                    ]);
                }
            }

            fclose($handle);
        }

        return response()->json(['message' => 'Datos cargados correctamente']);
    }


}
