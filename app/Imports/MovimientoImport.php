<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;


class MovimientoImport implements ToCollection, WithHeadingRow, WithLimit, WithChunkReading, SkipsEmptyRows
{
    public array $data = [];

    /**
     * Define la fila 3 como la fila de encabezados.
     */
    public function headingRow(): int
    {
        return 3;
    }

    public function limit(): int
    {
        return 100000;
    }

     public function collection(Collection $rows)
     {
         foreach ($rows as $row) {

            if (!preg_match('/^[A-Za-z]{3}-\d+$/', $row['claveinstalacion'])) {
                continue;
            }

             if (empty($row['claveinstalacion'])
                 && empty($row['cfdi'])
                 && empty($row['rfcclienteoproveedor'])
             ) {
                 continue;
             }



             $this->data[] = [
                 'ClaveInstalacion'               => (string)($row['claveinstalacion'] ?? ''),
                 'TerminalAlmYDist'              => (string)($row['terminalalmydist'] ?? ''),
                 'PermisoAlmYDist'               => (string)($row['permisoalmydist'] ?? ''),
                 'TarifaDeAlmacenamiento'        => (float)($row['tarifadealmacenamiento'] ?? 0),
                 'CargoPorCapacidadAlmac'        => (float)($row['cargoporcapacidadalmac'] ?? 0),
                 'CargoPorUsoAlmac'              => (float)($row['cargoporousoalmac'] ?? 0),
                 'CargoVolumetricoAlmac'          => (float)($row['cargovolumetricoalmac'] ?? 0),
                 'PermisoTransporte'             => (string)($row['permisotransporte'] ?? ''),
                 'ClaveDeVehiculo'               => (string)($row['clavedevehiculo'] ?? ''),
                 'TarifaDeTransporte'            => (float)($row['tarifadetransporte'] ?? 0),
                 'CargoPorCapacidadTrans'        => (float)($row['cargoporcapacidadtrans'] ?? 0),
                 'CargoPorUsoTrans'              => (float)($row['cargoporousotrans'] ?? 0),
                 'CargoVolumetricoTrans'          => (float)($row['cargovolumetricotrans'] ?? 0),
                 'RfcClienteOProveedor'          => (string)($row['rfcclienteoproveedor'] ?? ''),
                 'NombreClienteOProveedor'       => (string)($row['nombreclienteproveedor'] ?? ''),
                 'PermisoClienteOProveedor'      => (string)($row['permisoclienteoproveedor'] ?? ''),
                 'CFDI'                          => (string)($row['cfdi'] ?? ''),
                 'TipoCFDI'                      => (string)($row['tipocfdi'] ?? ''),
                 'PrecioVentaOCompraOContrap'     => (float)($row['precioventaocompraocontrap'] ?? 0),
                 'FechaYHoraTransaccion'         => (string)($row['fechayhoratransaccion'] ?? ''),
                 'VolumenDocumentado'            => (float)($row['volumendocumentado'] ?? 0),
                 'FechaDeLaRecepcion'            => (string)($row['fechadelarecepcion'] ?? $row['fecha_de_la_recepcion_aleatoria_pendiente_determinado'] ?? ''),
                 'Aclaracion'                    => (string)($row['aclaración'] ?? $row['aclaracion'] ?? ''),
             ];
         }
     }

    public function chunkSize(): int
    {
        return 5000;
    }

}
