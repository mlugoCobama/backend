<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Compras\Models\AcuseEntrega;
use Modules\Compras\Models\CatUnidadesMedidas;
use Modules\Compras\Models\EntregaTecnicos;
use Modules\Compras\Models\Productos;
use Modules\Compras\Services\AlmacenProductosService;
use Modules\Compras\Services\FormatoEntregaInsumosService;
use Modules\Compras\Transformers\MovimientosAlamacenResource;
use Modules\Compras\Transformers\StockAlmacenProductosResource;

class AlmacenProductosController extends Controller
{

    public function __construct(
        protected AlmacenProductosService $almacenProductos,
        protected FormatoEntregaInsumosService $formatoEntrega
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->almacenProductos->queryExistencias();

        return response()->json([
            'data' => StockAlmacenProductosResource::collection($data)
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compras::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $usuarioEntrega = $request->user();
            $this->almacenProductos->storeSalidaInventario($request->tipoSalida, $request->empresa, $usuarioEntrega->id, $request->tecnico, $request->materiales);

        return response()->json([
            'status' => 'success',
            'data' => [],
            'message' => 'Salidas registradas correctamente'
        ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la salida: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $data = $this->almacenProductos->queryExistencias($id);

        return response()->json([
            'data' => StockAlmacenProductosResource::collection($data)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('compras::edit');
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

    public function getExistenciasAlmacen(Request $request){


        $data = $this->almacenProductos->queryExistencias($request->intercompania, $request->tipo, $request->categoria, $request->subcategoria);
        $dataB = $this->almacenProductos->queryExistenciasB($request->tipo, $request->intercompania, $request->categoria);

        $resultado = $data->concat($dataB);

        return response()->json([
            'data' => StockAlmacenProductosResource::collection($resultado)
        ]);

    }

    public function getMoviemientosAlmacen(Request $request ){

    $data = $this->almacenProductos->queryMovimientosAlmacen($request->idProducto, $request->almacen);

    return response()->json([
        'status' => 'success',
        'data' => MovimientosAlamacenResource::collection($data),
        'messsage' => 'Almacen recuperado correctamente '
    ]);

    }

    public function generatePdf($idSalida)
    {

        $pdfContent = $this->formatoEntrega->generateResourceDeliveryReport($idSalida);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="entrega_recursos'. '.pdf"');
    }

    public function subirAcuse(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $entrega = EntregaTecnicos::find($request->id);

        if ($request->hasFile('archivo') && $entrega) {
            $file = $request->file('archivo');
            $nombreArchivo = $entrega->folio.'_'.time() . '_' . $file->getClientOriginalName();

            $ruta = $file->storeAs('entregaRecursos/'.$entrega->id, $nombreArchivo, 'public');

            $entrega->ruta_formato = $ruta;
            $entrega->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Archivo guardado correctamente',
                'ruta' => $ruta,
                'url' => asset('storage/' . $ruta)
            ], 200);
        }

        return response()->json(['error' => 'No se recibió ningún archivo'], 400);
    }

    public function getFile($id, $file)
    {
        $fileName = basename($file);

        $path = storage_path("app/public/entregaRecursos/{$id}/{$fileName}");
        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }


    public function importarProductos(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return response()->json(['message' => 'No se pudo abrir el archivo']);
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return response()->json(['message' => 'El archivo CSV está vacío']);
        }

        $header = array_map(function ($col) {
            $col = preg_replace('/^\xEF\xBB\xBF/', '', (string) $col);
            return trim($col);
        }, $header);

        $headerCount = count($header);

        $unidadesMap = CatUnidadesMedidas::pluck('id', 'nombre')
            ->mapWithKeys(fn ($id, $nombre) => [mb_strtolower(trim((string) $nombre)) => $id]);

        $insertados = 0;

        try {
            DB::transaction(function () use ($handle, $header, $headerCount, $unidadesMap, &$insertados) {
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    // Saltar filas completamente vacías
                    if ($row === [null] || count(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== '')) === 0) {
                        continue;
                    }

                    if (count($row) !== $headerCount) {
                        throw new \RuntimeException('Una fila del CSV no coincide con el número de columnas del encabezado.');
                    }

                    $data = array_combine($header, $row);

                    $unidadTexto = trim((string) ($data['unidad_medida'] ?? ''));
                    $primeraPalabra = mb_strtolower(explode(' ', $unidadTexto)[0] ?? '');
                    $unidadMedidaId = $unidadesMap[$primeraPalabra] ?? 11;

                    $producto = new Productos();
                    $producto->com_subcategoria_id = $data['com_subcategoria_id'] ?? null;
                    $producto->clave_interna = trim((string) ($data['clave_interna'] ?? ''));
                    $producto->cant_default = 1;
                    $producto->com_cat_unidad_medida_id = $unidadMedidaId;
                    $producto->descripcion_planta = trim((string) ($data['descripcion_planta'] ?? ''));
                    $producto->descripcion_proveedor = trim((string) ($data['descripcion_proveedor'] ?? ''));
                    $producto->observaciones = isset($data['observaciones']) && trim((string) $data['observaciones']) !== ''
                        ? trim((string) $data['observaciones'])
                        : '';
                    $producto->save();

                    $insertados++;
                }
            });
        } catch (\Throwable $e) {
            fclose($handle);
            return response()->json([
                'message' => 'Error al importar productos: ' . $e->getMessage(),
            ]);
        }

        fclose($handle);

        return response()->json([
            'message' => 'Datos cargados correctamente',
            'insertados' => $insertados,
            'unidadesMap' => $unidadesMap
        ]);
    }
}
