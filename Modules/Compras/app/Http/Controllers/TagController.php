<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Support\Generator\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Compras\Models\Tags;
use Modules\Compras\Transformers\TagsResource;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Tags::get();

        
        return response()->json([
            'status' => 'success',
            'data' => TagsResource::collection($data),
            'message' => 'Datos recuperados correctamente'
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
        $data = $request->all();
        $existe = false;

        // Validamos que este tag no halla sido registrado anteriormente, solo en registros nuevos
        if($data['tipo'] == 'nueva'){
            $existe = Tags::where('num_tag', $data['num_tag'])->first();
        }

        //Si ya fue registrado se notifica la usuario
        if($existe){
            return response()->json([
                'status' => 'error',
                'message' => 'Este tag ya esta registrado',
                'data' => []
             ]);
        }

        // si el tipo es nuevo se intancia al modelo para crear un nuevo registro
        if($data['tipo'] == 'nueva'){
            $tag = new Tags();
        // si no se actualiza el registro
        }else{
            $tag = Tags::find($data['id']);
        }
        
        $tag->proveedor = $data['proveedor'];
        $tag->num_tag = $data['num_tag'];
        $tag->serie = $data['serie'];
        $tag->observaciones = $data['observaciones'];
        $tag->intercompania = $data['intercompania'];
        $tag->estatus = $data['estatus'];
        $tag->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tarjeta Guardada Correctamente',
            'data' => []
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $tags =  Tags::where('intercompania', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $tags, 
            'message' => 'Tarjetas recuperadas correctamente'
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
}
