<?php
namespace App\Exports;

use App\Queries\SqlServer\UboAutos\PueQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FacturasExport implements Export, FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $companias;
    protected $fechaInicial;
    protected $fechaFinal;
    protected $query;

    public function __construct(array $companias, string $fechaInicial, string $fechaFinal)
    {
        $this->query = app(PueQuery::class);
        $this->companias = $companias;
        $this->fechaInicial = $fechaInicial . ' 00:00:00';
        $this->fechaFinal   = $fechaFinal . ' 23:59:59';
    }

    public function collection(): Collection
    {
        $resultados = collect();

        foreach ($this->companias as $companiaId) {
            $connectionName = $this->getConnectionName($companiaId);

            if (!$connectionName) {
                continue;
            }
            // Ejecutamos el script T-SQL
            $rows = $this->query->getPues($connectionName, $this->fechaInicial, $this->fechaFinal);

            $resultados = $resultados->concat($rows);
        }
        return $resultados;
    }

    public function headings(): array
    {
        return [
            'Renglón', 'Tipo Documento', 'Cartera', 'Agencia', 'Nombre Agencia',
            'Factura', 'Fecha Factura', 'Fecha Timbrado', 'Folio', 'Razón Social',
            'RFC', 'ISAN', 'IVA', 'Subtotal', 'Total', 'Condición Pago',
            'Tipo Venta', 'Método Pago', 'Saldo', 'Tipo Comprobante', 'Póliza',
            'UUID', 'Póliza Canc.', 'Fecha Canc.', 'Estatus', 'Fecha Cancela',
            'Cancelado', 'Estatus CFDI', 'Inventario', 'Serie', 'Descripción', 'Año Modelo'
        ];
    }

    public function map($row): array
    {
        return [
            $row->RENGLON,
            $row->TIPODOCUMENTO,
            $row->CARTERA,
            $row->AGENCIA,
            $row->NOMBREAGENCIA,
            $row->FACTURA,
            $row->FECHAFACTURA,
            $row->FECHATIMBRADO,
            $row->FOLIO,
            $row->RAZONSOCIAL,
            $row->RFC,
            $row->ISAN,
            $row->IVA,
            $row->SUBTOTAL,
            $row->TOTAL,
            $row->CONDICIONDEPAGO,
            $row->TIPODEVENTA,
            $row->METODODEPAGO,
            $row->SALDO,
            $row->TIPOCOMPROBANTE,
            $row->POLIZA,
            $row->UUID,
            $row->POLIZACANC,
            $row->FECHACANCELACION,
            $row->ESTATUS,
            $row->FECHACANCELA,
            $row->CANCELADO,
            $row->ESTATUSCFDI,
            $row->INVENTARIO,
            $row->SERIE,
            $row->DESCRIPCIONMODELO,
            $row->ANIODELMODELO,
        ];
    }

    private function getConnectionName($id)
    {
        return match ((int)$id) {
            706 => 'renault',
            // 707 => 'nissan_insurgentes',
            705 => 'nissan_azcapotzalco',
            709 => 'nissan_campestre',
            710 => 'nissan_universidad',
            default => null,
        };
    }
}
