@extends('compras::layouts.master')
@section('content')
    <div class="container-fluid">
        <div class="p-5 d-flex justify-content-center align-items-center">
            <!-- Simplicity is an acquired taste. - Katharine Gerould -->
            <div class="card text-center">
                <div class="card-header">
                    Autorización de solicitud de compras
                </div>
                <div class="card-body">
                    <h5 class="card-title">Listo!!</h5>
                    <p class="card-text">Se ha autorizado la solicitud de compra y se ha enviado al area de compras para
                        continuar con el proceso.</p>
                    {{-- <a href="#" class="btn btn-primary">Go somewhere</a> --}}
                </div>
                <div class="card-footer text-body-secondary">
                    Compras Cobama
                </div>
            </div>
        </div>
    </div>
@endsection
