<?php

namespace Modules\Volumetricos\Transformers;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ReportesVolumetricosResource extends JsonResource
{
    private static $empresasCache = [];
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $nombreEmpresa = $this->setEmpresaName($this->empresa);
        $fechaReporte = $this->parserFecha( $this->fecha_reporte);
        $estatusTxt = $this->setEstatusText($this->estatus);
        return [
            'id' => $this->id,
            'empresa' => $this->empresa,
            'nombre_empresa' => $nombreEmpresa,
            'ruta_archivo' => $this->ruta_archivo,
            'ruta_plantilla' => $this->ruta_plantilla,
            'uuid_plantilla' => $this->uuid_plantilla,
            'estaciones' => $this->estaciones,
            'tipo' => $this->tipo,
            'activo' => $this->activo,
            'fecha_reporte' => $this->fechaReporte,
            'fecha_reporte_txt' => mb_strtoupper($fechaReporte),
            'descripcion' => $this->descripcion,
            'estatus' => $this->estatus,
            'estatus_txt' => $estatusTxt,
            'comentarios' => $this->comentarios,
            'created_at' => $this->created_at,
        ];
    }

    private function setEmpresaName($intercompania)
    {
        if (isset(self::$empresasCache[$intercompania])) {
            return self::$empresasCache[$intercompania];
        }

        $empresas = DB::connection('intranet')->select('CALL SP_GetEmpresas()');

        foreach ($empresas as $empresa) {
            self::$empresasCache[$empresa->intercompania] = $empresa->name;
        }

        return self::$empresasCache[$intercompania] ?? 'No disponible';
    }


    private function setEstatusText($estatus){
        return match ($estatus) {
            1 => 'GENERADO',
            2 => 'ENVIADO' ,
            3 => 'RECHAZADO',
            4 => 'ACEPTADO',
            default => 'Desconocido'
        };
    }


    private function parserFecha($fechaIso)
    {
        if(empty($fechaIso)){
            return 'DATO NO DISPONIBLE';
        }
        // Resultado: "31 de enero de 2026"
        return Carbon::parse($fechaIso)
            ->locale('es')
            // ->isoFormat('D [de] MMMM [de] YYYY');
            ->isoFormat( 'MMMM [de] YYYY');
    }
}
