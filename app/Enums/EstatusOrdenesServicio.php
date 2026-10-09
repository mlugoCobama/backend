<?php

namespace App\Enums;

enum EstatusOrdenesServicio
{

    public const ESP_ASIGNACION = 1;
    public const ASIGNADO       = 2;
    public const EN_DIAGNOSTICO = 3;
    public const POR_AUTORIZAR  = 4;
    public const EN_PROCESO     = 5;
    public const TERMINADO      = 6;
    public const ENTREGADO      = 7;
    public const FINALIZADA     = 8;


    public static function labels()
    {
        return [
            self::ESP_ASIGNACION => 'Esp. Asignacion',
            self::ASIGNADO => 'Asignado',
            self::EN_DIAGNOSTICO => 'En Diagnostico',
            self::POR_AUTORIZAR => 'Por Autorizar',
            self::EN_PROCESO => 'En Proceso',
            self::TERMINADO => 'Terminado',
            self::ENTREGADO => 'Entregado',
            self::FINALIZADA => 'Finalizada',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }

    public static function colorsBS()
    {
        return [
            self::ESP_ASIGNACION => 'warning',
            self::ASIGNADO => 'primary',
            self::EN_DIAGNOSTICO => 'secondary',
            self::POR_AUTORIZAR => 'warning',
            self::EN_PROCESO => 'dark',
            self::TERMINADO => 'info',
            self::ENTREGADO => 'success',
            self::FINALIZADA => 'dark',
        ];
    }

    public static function colorBS($value)
    {
        return self::colorsBS()[$value] ?? 'primary';
    }
}
