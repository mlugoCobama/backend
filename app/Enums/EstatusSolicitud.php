<?php

namespace App\Enums;

class EstatusSolicitud
{
    public const ESP_AUT_PLANTA = 1;
    public const SOLICITADO = 2;
    public const EN_COTIZACION = 3;

    public const CANCELADA = 4;
    public const EN_ORDEN_COMPRA = 5;
    
    public const AUTORIZADA = 6;
    public const EN_SURTIDO = 7;
    public const ENTREGADA = 8;
    public const PAGANDO = 9;
    public const PAGADA = 10;
    public const RECHAZADA = 11;

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
            self::RECHAZADA => 'RECHAZADA',
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
            self::RECHAZADA => 'bg-danger',
        ];
    }
}