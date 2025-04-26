<?php

namespace App\Enums;

class EstatusSolicitud
{
    public const CANCELADA = 0;
    public const ESP_AUT_PLANTA = 1;
    public const SOLICITADO = 2;
    public const EN_COTIZACION = 3;
    public const EN_ORDEN_COMPRA = 4;
    public const AUTORIZADA = 5;
    public const EN_SURTIDO = 6;
    public const ENTREGADA = 7;
    public const PAGANDO = 8;
    public const PAGADA = 9;

    public static function labels()
    {
        return [
            self::CANCELADA => 'CANCELADA',
            self::ESP_AUT_PLANTA => 'ESP. AUT. PLANTA',
            self::SOLICITADO => 'SOLICITADO',
            self::EN_COTIZACION => 'EN COTIZACIÓN',
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
            self::ESP_AUT_PLANTA => 'badge-soft-info',
            self::SOLICITADO => 'bg-info',
            self::EN_COTIZACION => 'badge-soft-warning',
            self::EN_ORDEN_COMPRA => 'bg-warning',
            self::AUTORIZADA => 'badge-soft-success',
            self::EN_SURTIDO => 'badge-soft-secondary',
            self::ENTREGADA => 'badge-soft-dark',
            self::PAGANDO => 'bg-primary',
            self::PAGADA => 'bg-success',
        ];
    }
}