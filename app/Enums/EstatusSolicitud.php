<?php

namespace App\Enums;

/**
 * Esta enumeración representa los estados de una los estatus de una orden de compra
 */
class EstatusSolicitud
{
    public const ESP_AUT_PLANTA = 1;        //En espera de autorización

    public const SOLICITADO = 2;            //Solicitado
    public const EN_COTIZACION = 3;         //Solicitado

    public const CANCELADA = 4;             //En cotización
    public const EN_ORDEN_COMPRA = 5;       //Orden de compra

    public const AUTORIZADA = 6;            //Autorizado
    //No mover ningún estado hasta aquí
    public const AUTORIZADO_A_PAGO = 7;     //Autorizado a pago
    public const EN_SURTIDO = 8;            //En surtido
    public const ENTREGADA = 9;             //Entregada Parcialmente
    public const FACTURADO = 10;            //Por facturar
    public const SOLICITADO_PAGO = 11;      //Pago Solicitado
    public const PAGADA = 12;               //Pagada

    public const CARGA_COMPLEMENTO = 13;    //Cargar complemento
    public const FINALIZADA = 14;           //Finalizada


    /**
     * Etiqueta basada en cada estado de la solicitud de compra
     */
    public static function labels()
    {
        return [
            self::CANCELADA => 'CANCELADA',
            self::ESP_AUT_PLANTA => 'ESP. AUT. PLANTA',
            self::SOLICITADO => 'SOLICITADO',
            self::EN_COTIZACION => 'EN COTIZACIÓN',
            self::EN_ORDEN_COMPRA => 'ORDEN DE COMPRA',
            self::AUTORIZADA => 'AUTORIZADA',
            //No mover ningún estado hasta aquí

            self::AUTORIZADO_A_PAGO => 'AUTORIZADO A PAGO',
            self::EN_SURTIDO => 'EN SURTIDO',
            self::ENTREGADA => 'ENTREGADA',
            self::FACTURADO => 'POR FACTURAR',
            self::SOLICITADO_PAGO => 'SOLICITADO A PAGO',
            self::PAGADA => 'PAGADA',
            self::CARGA_COMPLEMENTO => 'CARGA COMPLEMENTO',
            self::FINALIZADA => 'FINALIZADA',
        ];
    }

    /**
     * Etiqueta basada en cada clase bootstrap de la solicitud de compra
     */
    public static function clases()
    {
        return [
            self::CANCELADA => 'bg-danger',
            self::ESP_AUT_PLANTA => 'badge-soft-info',
            self::SOLICITADO => 'bg-info',
            self::EN_COTIZACION => 'badge-soft-warning',
            self::EN_ORDEN_COMPRA => 'bg-warning',
            self::AUTORIZADA => 'badge-soft-success',
            //No mover ningún estado hasta aquí
            self::AUTORIZADO_A_PAGO => 'badge-soft-primary',
            self::EN_SURTIDO => 'badge-soft-secondary',
            self::ENTREGADA => 'badge-soft-dark',
            self::FACTURADO => 'badge-dark',
            self::SOLICITADO_PAGO => 'badge-soft-primary',
            self::PAGADA => 'bg-success',
            self::CARGA_COMPLEMENTO => 'badge-soft-indigo',
            self::FINALIZADA => 'bg-teal',




        ];
    }

    public static function getLabel(int $estatus): string
    {
        $labels = self::labels();
        return $labels[$estatus] ?? 'DESCONOCIDO';
    }
}
