<!DOCTYPE html>
<html>

<head>
    <title>Solicitud de Cotización</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Solicitud de Cotización</h2>
    <p>El area de compras COBAMA solicita la siguiente cotización: </p>
    <h3>Detalles:</h3>
    <table>
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Unidad de Medida</th>
                <th>Descripción</th>
                <th>Observaciones</th>
                <th>Imagen de Referencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['detalles'] as $detalle)
                <tr>
                    <td>{{ $detalle['cantidad'] }}</td>
                    <td>{{ $detalle['unidadMedida']['abreviatura'] }}</td>
                    <td>{{ $detalle['descripcion'] }}</td>
                    <td>{{ $detalle['observaciones'] }}</td>
                    <td>
                        @if ($detalle['img_referencia'])
                            <img src="{{ $detalle['img_referencia'] }}" alt="Referencia" style="max-width: 100px;">
                        @else
                            No disponible
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Consideraciones: {{ $data['consideraciones'] ?? 'No se especificaron consideraciones' }}</p>
    <br>
    <br>
    <br>
    <p>Saludos cordiales</p>
</body>

</html>
