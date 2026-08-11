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
use SimpleXMLElement;

class XlxsToJsonController extends Controller
{

    protected $parserJson;

    // Inyección de dependencias en el constructor
    public function __construct(
        ParserJsonService $parserJson,
    ) {
        $this->parserJson = $parserJson;
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
        $file  = $request->file('file');


        $jsonStructure = $this->parserJson->generateJson($file);

        // $data = $this->utf8ize($data);

        if ($request->wantsJson()) {
            return response()->json($jsonStructure, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }



        $fileName = 'Reporte_Volumetrico_' . date('Y-m-d_H-i-s') . '.json';
        return response()->streamDownload(function () use ($jsonStructure) {
            echo json_encode($jsonStructure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
 * Helper recursivo para transformar un Array PHP a XML
 */
private function arrayToXml(array $data, SimpleXMLElement &$xmlData)
{
    foreach ($data as $key => $value) {
        if (is_numeric($key)) {
            $key = 'Elemento' . $key;
        }
        if (is_array($value)) {
            $subnode = $xmlData->addChild($key);
            $this->arrayToXml($value, $subnode);
        } else {
            $xmlData->addChild("$key", htmlspecialchars("$value"));
        }
    }
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


}
