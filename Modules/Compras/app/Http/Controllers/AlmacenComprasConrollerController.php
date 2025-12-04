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
        // 25
        // 169
        // 169
        // 169
        // 192
        // 202
        // 213
        // 224
        // 225
        // 230
        // 234
        // 239
        // 245
        // 249
        // 250
        // 251
        // 252
        // 253
        // 254
        // 255
        // 256
        // 257
        // 259
        // 259
        // 268
        // 342
        // 359
        // 359
        // 359
        // 360
        // 366
        // 381
        // 382
        // 384
        // 393
        // 415
        // 578
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
