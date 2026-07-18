<?php

namespace App\Enums;

enum EstatusAsignaciones
{

    public const INACTIVA   = 0;
    public const ACTIVA     = 1;
    public const FINALIZADA = 2;
    public const SUSPENDIDA = 3;


    public static function labels()
    {
        return [
            self::INACTIVA => 'INACTIVA',
            self::ACTIVA => 'ACTIVA',
            self::FINALIZADA => 'FINALIZADA',
            self::SUSPENDIDA => 'SUSPENDIDA',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }

    public static function colorsBS()
    {
        return [
            self::INACTIVA => 'dark',
            self::ACTIVA => 'success',
            self::FINALIZADA => 'danger',
            self::SUSPENDIDA => 'info',
        ];
    }

    public static function colorBS($value)
    {
        return self::colorsBS()[$value] ?? 'primary';
    }
}