<?php
namespace App\Enums;

/**
 * Esta enumeración representa los estados de una los estatus de comisiones de autos (Venta de autos nuevos y seminuevos)
 */
enum EstatusComisionesVentasAutos
{
    public const RECHAZADA = 0;
    public const REV_CXC = 1;
    public const REV_GV = 2;
    public const REV_CONTA = 3;
    // Equivalente a autorizado en las otras ventas
    public const REV_RH = 4; 
    public const EN_ESPERA = 6;
    public const PAGADO = 5;
    

    public static function labels()
    {
        return [
            self::RECHAZADA => 'RECHAZADA',
            self::REV_CXC => 'ENTRADA',
            self::REV_GV => 'GASTOS',
            self::REV_CONTA => 'VALIDACIÓN',
            self::REV_RH => 'VALIDADO',
            self::EN_ESPERA => 'EN ESPERA',
            self::PAGADO => 'EN ESPERA',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }
}