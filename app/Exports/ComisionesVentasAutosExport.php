<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;





class ComisionesVentasAutosExport implements FromCollection, WithMapping, WithTitle, WithHeadings,ShouldAutoSize, WithColumnFormatting, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $estatus;

    protected array $baseHeadings = [
            "Fecha de factura", 
            "No. factura",
            "Razón Social",
            "Clave Inventario",
            "No. Vendedor",
            "Descripción",
            "No. Serie",
            "Precio de Venta",      //numerico-pesos-alineacion_izquierda
            "Costos",               //numerico-pesos-alineacion_izquierda
            "Bonificación Extra" ,  //numerico-pesos-alineacion_izquierda
            "Utilidad",             //numerico-pesos-negritas-alineacion_izquierda
            "Tipo de venta",        
            "% Tipo de venta",      //numerico-porcentaje
            'Otros',                //numerico-pesos-alineacion_izquierda
            'Gasolina',             //numerico-pesos-alineacion_izquierda
            'Previa',               //numerico-pesos-alineacion_izquierda
            'Descuentos',           //numerico-pesos-alineacion_izquierda
            'Traslados',            //numerico-pesos-alineacion_izquierda
            'Descuento Impulso',    //numerico-pesos-alineacion_izquierda
            'Descuento Gasto',      //numerico-pesos-alineacion_izquierda
            'Cortesía',             //numerico-pesos-alineacion_izquierda
            'Accesorios',           //numerico-pesos-alineacion_izquierda
            'Total de gastos',      //numerico-pesos-negritas-alineacion_izquierda
            '% BDC',                //numerico-porcentaje
            'Comisión de APV',      //numerico-pesos-negritas-alineacion_izquierda
            'Comisión de BDC',      //numerico-pesos-alineacion_izquierda
        ];

    public function __construct($data, $estatus)
    {
        $this->data = $data;
        $this->estatus = $estatus;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function title(): string
    {

        return $this->setNombreHoja( (string)$this->estatus);
    }

    public function headings(): array
    {

        return $this->setHeadersByTipo((string)$this->estatus);
    }

    public function map($item): array
    {
        return [
            $item['fecha_factura'],
            $item['no_factura'],
            $item['razon_social'],
            $item['clave_inventario'],
            $item['vendedor_agencia'],
            $item['descripcion'],
            $item['serie'],
            $item['venta'],
            $item['costos'],
            $item['bonificacion_extra'],
            $item['utlidad'],
            $item['tipo_venta_nombre'],
            $item['porcentaje_tipo_venta'],

            $item['otros'],
            $item['gasolina'],
            $item['previa'],
            $item['descuentos'],
            $item['traslados'],
            $item['descuento_impulso'],
            $item['descuento_gastos'],
            $item['cortesia'],
            $item['accesorios'],
            $item['total_gastos'],
            $item['porcentaje_bdc'],
            $item['comision_apv_pesos'],
            $item['comision_bdc_pesos'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Precio de Venta
            'I' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Costos
            'J' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Bonificación Extra
            'K' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Utilidad
            'L' => NumberFormat::FORMAT_TEXT,                // Tipo de venta
            'M' => NumberFormat::FORMAT_PERCENTAGE_00,       // % Tipo de venta
            'N' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Otros
            'O' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Gasolina
            'P' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Previa
            'Q' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Descuentos
            'R' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Traslados
            'S' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Descuento Impulso
            'T' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Descuento Gasto
            'U' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Cortesía
            'V' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Accesorios
            'W' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Total de gastos
            'X' => NumberFormat::FORMAT_PERCENTAGE_00,       // % BDC
            'Y' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Comisión APV
            'Z' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, // Comisión BDC
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Encabezados en negritas
            'K' => ['font' => ['bold' => true]], // Columna Utilidad en negritas
            'W' => ['font' => ['bold' => true]], // Total de gastos en negritas
            'Y' => ['font' => ['bold' => true]], // Comisión APV en negritas
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $event->sheet->getStyle('H:Z')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    public function setHeadersByTipo($estatus){
        return match($estatus) {
            '2' => array_splice($this->baseHeadings, 0, 23),
            '3' => $this->baseHeadings,
            '4' => $this->baseHeadings,
            '5' => $this->baseHeadings,
            '12345' => $this->baseHeadings,
            default => array_splice($this->baseHeadings, 0, 13),
        };


    }

    public function setNombreHoja($estatus){
        return match($estatus) {
            '2' => 'Libro de ventas - Entregados',
            '3' => 'Libro de ventas - Gastos',
            '4' => 'Libro de ventas - Autorizados',
            '4' => 'Libro de ventas - Pagados',
            '12345' => 'Libro de ventas - Todos',
            default => 'Libro de ventas',
        };
    }





}
