<?php

namespace App\Enums;

enum EstatusAsignaciones
{

    public const NO_ASIGNABLE = 0;
    public const DISPONIBLE = 1;
    public const ASIGNADA = 2;
    public const OBSOLETO = 3;
    public const EXTRAVIADA = 4;

    public static function labels()
    {
        return [
            self::NO_ASIGNABLE => 'NO ASIGNABLE',
            self::DISPONIBLE => 'DISPONIBLE',
            self::ASIGNADA => 'ASIGNADA',
            self::OBSOLETO => 'OBSOLETO',
            self::EXTRAVIADA => 'EXTRAVIADA',
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
        ];
    }

    public static function colorBS($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }
}