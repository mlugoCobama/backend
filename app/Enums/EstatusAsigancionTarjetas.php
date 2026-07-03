<?php

namespace App\Enums;

enum EstatusAsigancionTarjetas
{
    public const DISPONIBLE = 0;
    public const ASIGNADA = 1;

    public static function labels()
    {
        return [
            self::DISPONIBLE => 'DISPONIBLE',
            self::ASIGNADA => 'ASIGNADA',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }

    public static function colorsBS()
    {
        return [
            self::DISPONIBLE => 'success',
            self::ASIGNADA => 'primary',
        ];
    }

    public static function colorBS($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }
}