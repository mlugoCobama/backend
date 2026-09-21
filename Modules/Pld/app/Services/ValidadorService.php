<?php

namespace Modules\Pld\Services;

use DateTimeImmutable;
use Exception;

class ValidadorService
{

    private const MAX_DIAS_ANTIGUEDAD = 90;

    public function evaluarExpediente(array $data): array
    {
        $resultados = [
            'es_valido' => true,
            'exitos'   => [],
            'errores'  => []
        ];

        $ine = $data['ine'] ?? [];
        $curpDoc = $data['curp'] ?? [];
        $csf = $data['csf'] ?? [];
        $comprobante = $data['comprobante_domicilio'] ?? [];

        $curpIne    = strtoupper(trim($ine['curp'] ?? ''));
        $curpRenapo = strtoupper(trim($curpDoc['curp'] ?? ''));
        $curpCsf    = strtoupper(trim($csf['identificacion_fiscal']['curp'] ?? ''));

        if (!$curpIne || !$curpRenapo || !$curpCsf) {
            $resultados['errores'][] = "Falta la CURP en uno o más documentos (INE, CURP o CSF).";
        } elseif ($curpIne === $curpRenapo) {
            $resultados['exitos'][] = "La CURP y el ine coinciden correctamente.";
        } elseif ($curpIne === $curpRenapo && $curpIne === $curpCsf) {
            $resultados['exitos'][] = "La CURP coinciden correctamente en los 3 documentos ($curpIne).";
        } else {
            $resultados['errores'][] = "La CURP no coincide entre los documentos (INE: $curpIne, RENAPO: $curpRenapo, CSF: $curpCsf).";
        }

        $nombreIne  = $this->normalizarTexto(($ine['nombre'] ?? '') . ' ' . ($ine['apellido_paterno'] ?? '') . ' ' . ($ine['apellido_materno'] ?? ''));
        $nombreCurp = $this->normalizarTexto(($curpDoc['nombre'] ?? '') . ' ' . ($curpDoc['primer_apellido'] ?? '') . ' ' . ($curpDoc['segundo_apellido'] ?? ''));
        $nombreCsf  = $this->normalizarTexto($csf['identificacion_fiscal']['nombre_razon_social'] ?? '');

        if ($nombreIne === $nombreCurp && $nombreIne === $nombreCsf) {
            $resultados['exitos'][] = "El nombre titular coincide en todos los documentos ('$nombreIne').";
        } elseif ($curpIne === $curpRenapo) {
            $resultados['exitos'][] = "El nombre en la CURP y en el INE coinciden correctamente";
        }  else {
            $resultados['errores'][] = "El nombre no coincide entre los documentos (INE: '$nombreIne', CURP: '$nombreCurp', CSF: '$nombreCsf').";
        }

        // 3. Antigüedad CSF (< 90 días)
        if (!empty($curpDoc['fecha_emision'])) {
            $diasCsf = $this->obtenerAntiguedadDias($curpDoc['fecha_emision']);
            if ($diasCsf <= self::MAX_DIAS_ANTIGUEDAD && $diasCsf >= 0) {
                $resultados['exitos'][] = "La CSF está dentro del rango de antigüedad ($diasCsf días de emisión).";
            } else if ($diasCsf < 0) {
                $resultados['errores'][] = "La fecha de emisión de la CSF es errónea (se encuentra en el futuro).";
            } else {
                $resultados['errores'][] = "La CSF supera los 3 meses de antigüedad ($diasCsf días).";
            }
        } else {
            $resultados['errores'][] = "No se encontró la fecha de emisión en la CSF.";
        }

        $fechaComprobante = $comprobante['periodo']['fecha_fin']
            ?? $comprobante['periodo']['fecha_vencimiento']
            ?? $comprobante['periodo']['fecha_emision']
            ?? null;

        if ($fechaComprobante) {
            $diasComprobante = $this->obtenerAntiguedadDias($fechaComprobante);
            if ($diasComprobante <= self::MAX_DIAS_ANTIGUEDAD && $diasComprobante >= 0) {
                $resultados['exitos'][] = "El comprobante de domicilio está dentro del rango permitido ($diasComprobante días de antigüedad).";
            } else {
                $resultados['errores'][] = "El comprobante de domicilio supera los 3 meses de antigüedad ($diasComprobante días).";
            }
        } else {
            $resultados['errores'][] = "No se encontró la fecha de periodo/vencimiento en el comprobante de domicilio.";
        }

        $resultados['es_valido'] = empty($resultados['errores']);

        return $resultados;
    }


     /**
     * Elimina acentos, caracteres especiales y convierte a mayúsculas.
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtoupper(trim($texto), 'UTF-8');
        $originales = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'];
        $reemplazos = ['A', 'E', 'I', 'O', 'U', 'U', 'N'];
        $texto = str_replace($originales, $reemplazos, $texto);
        return preg_replace('/\s+/', ' ', $texto);
    }

    /**
     * Calcula los días transcurridos desde una fecha (YYYY-MM-DD) hasta hoy.
     */
    private function obtenerAntiguedadDias(string $fechaStr): ?int
    {
        try {
            $fecha = new DateTimeImmutable($fechaStr);
            $hoy = new DateTimeImmutable('now');
            $diferencia = $fecha->diff($hoy);
            return $diferencia->invert === 1 ? -$diferencia->days : $diferencia->days;
        } catch (Exception $e) {
            return null;
        }
    }
}

