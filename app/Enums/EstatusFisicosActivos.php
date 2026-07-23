<?php

namespace App\Enums;



enum EstatusFisicosActivos
{

    public const EXCELENTE          = 1; // Estado prácticamente nuevo, sin daños, funcionamiento perfecto.
    public const BUENO              = 2; // Desgaste normal por uso, sin afectar la operación.
    public const REGULAR            = 3; // Presenta desgaste visible o pequeños daños, pero funciona correctamente.
    public const MALO               = 4; // Daños importantes, requiere reparación o reemplazo de componentes.
    public const INUTILIZABLE       = 5; // No funciona o el costo de reparación no es justificable.


    public static function labels()
    {
        return [
            self::EXCELENTE => 'EXCELENTE',
            self::BUENO => 'BUENO',
            self::REGULAR => 'REGULAR',
            self::MALO => 'MALO',
            self::INUTILIZABLE => 'INUTILIZABLE',
        ];
    }

    public static function label($value)
    {
        return self::labels()[$value] ?? 'DESCONOCIDO';
    }

    public static function colorsBS()
    {
        return [
            self::EXCELENTE => 'success',
            self::BUENO => 'primary',
            self::REGULAR => 'dark',
            self::MALO => 'warning',
            self::INUTILIZABLE => 'danger',
        ];
    }

    public static function colorBS($value)
    {
        return self::colorsBS()[$value] ?? 'dark';
    }
}