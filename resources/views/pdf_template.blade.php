<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Orden de Compra</title>

    <!-- Cargar Bootstrap -->
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}" type="text/css">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
        }
        /* .table-bordered {
            border: 1px solid #000;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
        } */
    </style>
</head>
<body>

    {{-- <!-- Encabezado -->
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
        <h2>Orden de Compra - Equipo de Cómputo</h2>
        <p><strong>Folio:</strong> {{$folio}} | <strong>Fecha:</strong> {{$fecha}}</p>
    </div>

    <!-- Información General -->
    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>Solicita (Área)</th>
                <td>{{$id}}</td>
                <th>Usuario Solicita</th>
                <td>{{$usuario_solicita}}</td>
            </tr>
            <tr>
                <th>Usuario Destino Equipo</th>
                <td>{{$usuario_destino}}</td>
                <th>Puesto</th>
                <td>Director Operativo</td>
            </tr>
            <tr>
                <th>Empresa</th>
                <td colspan="3">Corporación Administrativa del Sur</td>
            </tr>
            <tr>
                <th>Objetivo/Uso del Equipo</th>
                <td colspan="3">{{$motivo}}</td>
            </tr>
        </tbody>
    </table>

    <!-- Tabla de Detalles -->
    <h4 class="mt-4">Detalles de la Orden</h4>
    <table class="table table-bordered">
        <thead class="bg-secondary text-white">
            <tr>
                <th>Cantidad</th>
                <th>U. Medida</th>
                <th>Descripción</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detalles as $detalle)
                <tr>
                    <td>{{$detalle['cantidad']}}</td>
                    <td>{{$detalle['unidadMedida']['nombre']}}</td>
                    <td>{{$detalle['descripcion']}}</td>
                    <td>{{$detalle['observaciones']}}</td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}
</body>
</html>