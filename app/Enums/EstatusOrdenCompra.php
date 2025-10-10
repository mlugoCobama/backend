<?php


namespace App\Enums;

/**
 * Esta enumeración representa los estados de una los estatus de una orden de compra
 */
enum EstatusOrdenCompra
{
    public const CANCELADA = 0;
    public const EN_ORDEN_COMPRA = 1;
    public const AUTORIZADA = 2;
    public const AUTORIZADO_A_PAGO = 3;
    public const EN_SURTIDO = 4;
    public const ENTREGADA = 5;
    public const FACTURADO = 6;
    public const SOLICITADO_PAGO = 7;
    public const PAGADA = 8;
    public const CARGA_COMPLEMENTO = 9;
    public const FINALIZADA = 10;
    

    public static function labels()
    {
        return [
            self::CANCELADA => 'CANCELADA',
            self::EN_ORDEN_COMPRA => 'ORDEN DE COMPRA',
            self::AUTORIZADA => 'AUTORIZADA',
            self::AUTORIZADO_A_PAGO => 'AUTORIZADO A PAGO',
            self::EN_SURTIDO => 'EN SURTIDO',
            self::ENTREGADA => 'ENTREGADA',
            self::FACTURADO => 'FACTURADO',
            self::SOLICITADO_PAGO => 'SOLICITADO A PAGO',
            self::PAGADA => 'PAGADA',
            self::CARGA_COMPLEMENTO => 'CARGA COMPLEMENTO',
            self::FINALIZADA => 'FINALIZADA',
        ];
    }

    public static function clases()
    {
        return [
            self::CANCELADA => 'bg-danger',
            self::EN_ORDEN_COMPRA => 'bg-warning',
            self::AUTORIZADA => 'badge-soft-success',
             self::AUTORIZADO_A_PAGO => 'badge-soft-primary',       
            self::EN_SURTIDO => 'badge-soft-secondary',
            self::SOLICITADO_PAGO => 'badge-soft-primary',        
            self::FACTURADO => 'badge-soft-dark',           
            self::ENTREGADA => 'badge-soft-dark',
            self::PAGADA => 'bg-success',
            self::FACTURADO => 'badge-dark',
            self::SOLICITADO_PAGO => 'badge-soft-primary',
            self::PAGADA => 'bg-success',
            self::CARGA_COMPLEMENTO => 'badge-soft-indigo',
            self::FINALIZADA => 'bg-teal',
        ];
    }
}
