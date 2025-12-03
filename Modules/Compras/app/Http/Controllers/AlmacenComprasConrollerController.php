<?php

namespace Modules\Compras\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlmacenComprasConrollerController extends Controller
{
    public function index()
    {
        return view('compras::index');
    }


    public function create()
    {
        return view('compras::create');
    }


    public function store(Request $request): RedirectResponse
    {
        //
    }

    public function show($id)
    {
        return view('compras::show');
    }


    public function edit($id)
    {
        return view('compras::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function altaAlmacem(){

    }

    public function calcularExistencia(){

    }

    public function actualizarExistencia(){

    }

    public function recuperarMovimientosSolicitud(){

    }

    public function recuperarAlmacenEmpresa(){

    }


}
