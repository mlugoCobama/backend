<?php

namespace App\Enums;

enum EstatusOrdenCompra
{
    public const CANCELADA = 0;
    public const EN_ORDEN_COMPRA = 1;
    public const AUTORIZADA = 2;
    public const EN_SURTIDO = 3;
    public const ENTREGADA = 4;
    public const PAGANDO = 5;
    public const PAGADA = 6;

    public static function labels()
    {
        return [
            self::CANCELADA => 'CANCELADA',
            self::EN_ORDEN_COMPRA => 'ORDEN DE COMPRA',
            self::AUTORIZADA => 'AUTORIZADA',
            self::EN_SURTIDO => 'EN SURTIDO',
            self::ENTREGADA => 'ENTREGADA',
            self::PAGANDO => 'PAGANDO',
            self::PAGADA => 'PAGADA',
        ];
    }

    public static function clases()
    {
        return [
            self::CANCELADA => 'bg-danger',
            self::EN_ORDEN_COMPRA => 'bg-warning',
            self::AUTORIZADA => 'badge-soft-success',
            self::EN_SURTIDO => 'badge-soft-secondary',
            self::ENTREGADA => 'badge-soft-dark',
            self::PAGANDO => 'bg-primary',
            self::PAGADA => 'bg-success',
        ];
    }
}
