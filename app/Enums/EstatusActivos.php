<?php

namespace App\Enums;



enum EstatusActivos
{

    public const NO_ASIGNABLE       = 0;
    public const DISPONIBLE         = 1;
    public const ASIGNADA           = 2;
    public const OBSOLETO           = 3;
    public const EXTRAVIADA         = 4;
    public const EN_MANTENIMIENTO   = 5;
    public const EN_REPARACION      = 6;
    public const BAJA_DEFINITIVA    = 7;


    public static function labels()
    {
        return [
            self::NO_ASIGNABLE => 'NO ASIGNABLE',
            self::DISPONIBLE => 'DISPONIBLE',
            self::ASIGNADA => 'ASIGNADA',
            self::OBSOLETO => 'OBSOLETO',
            self::EXTRAVIADA => 'EXTRAVIADA',
            self::EN_MANTENIMIENTO => 'EN MANTENIMIENTO',
            self::EN_REPARACION => 'EN REPARACIÓN',
            self::BAJA_DEFINITIVA => 'BAJA DEFINITIVA',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }

    public static function colorsBS()
    {
        return [
            self::NO_ASIGNABLE => 'secondary',
            self::DISPONIBLE => 'success',
            self::ASIGNADA => 'primary',
            self::OBSOLETO => 'warning',
            self::EXTRAVIADA => 'danger',
            self::EN_MANTENIMIENTO => 'info',
            self::EN_REPARACION => 'info',
            self::BAJA_DEFINITIVA => 'dark',
        ];
    }

    public static function colorBS($value)
    {
        return self::colorsBS()[$value] ?? 'primary';
    }
}