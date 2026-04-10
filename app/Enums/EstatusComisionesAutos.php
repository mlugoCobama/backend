<?php
namespace App\Enums;

/**
 * Esta enumeración representa los estados de una los estatus de comisiones de autos (accesorios, financiamientos, seguros, toma de unidad)
 */
enum EstatusComisionesAutos
{
    public const RECHAZADA = 0;
    public const POR_AUTORIZAR = 1;
    public const EN_ESPERA = 2;
    public const AUTORIZADA = 3;
    public const PAGADA = 4;
    

    public static function labels()
    {
        return [
            self::RECHAZADA => 'RECHAZADA',
            self::POR_AUTORIZAR => 'POR AUTORIZAR',
            self::AUTORIZADA => 'AUTORIZADA',
            self::EN_ESPERA => 'EN ESPERA',
            self::PAGADA => 'PAGADA'
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }
}