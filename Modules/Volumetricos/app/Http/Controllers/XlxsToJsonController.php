<?php

namespace Modules\Volumetricos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\VolumetricosImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Modules\Volumetricos\Services\ParserJsonService;
use Modules\Volumetricos\Services\ParserXmlService;
use SimpleXMLElement;
use Illuminate\Support\Str;

class XlxsToJsonController extends Controller
{

    protected $parserJson;
    protected $parserXml;

    // Inyección de dependencias en el constructor
    public function __construct(
        ParserJsonService $parserJson,
        ParserXmlService $parserXml,
    ) {
        $this->parserJson = $parserJson;
        $this->parserXml = $parserXml;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('volumetricos::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('volumetricos::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:20480',
        ]);

        $file = $request->file('file');
        $uuid = (string) Str::uuid();

        $resultado = $this->parserJson->generateJson($file);

        if ($request->wantsJson()) {

            return response()->json([
                'json' => $resultado['json'],
                'uuidInvalidos' => $resultado['uuidInvalidos'],
            ], 200, [
                'X-Report-UUID' => $uuid,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }

        $fileName = 'Reporte_Volumetrico_' . date('Y-m-d_H-i-s') . '.json';

        return response()->streamDownload(function () use ($resultado) {

            echo json_encode(
                $resultado['json'],
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_PRESERVE_ZERO_FRACTION
            );

        }, $fileName, [
            'Content-Type' => 'application/json',
            'X-Report-UUID' => $uuid,
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('volumetricos::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('volumetricos::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id){}
        //


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function descargarXml(Request $request, ParserXmlService $service)
    {
        $file = $request->file('archivo');

        $xmlContent = $service->generateXml($file);

        $nombreArchivo = 'reporte_volumetrico_' . now()->format('Ymd_His') . '.xml';

        return response($xmlContent, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

}
