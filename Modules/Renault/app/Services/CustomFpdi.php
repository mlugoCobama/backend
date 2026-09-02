<?php

namespace Modules\Renault\Services;

use setasign\Fpdi\Fpdi;

class CustomFpdi extends Fpdi
{
    /**
     * Dibuja una elipse en el PDF.
     *
     * @param float $x Centro X
     * @param float $y Centro Y
     * @param float $rx Radio horizontal
     * @param float $ry Radio vertical
     * @param string $style Estilo de dibujo: 'D' (borde), 'F' (relleno), 'FD' o 'DF' (ambos)
     */
    public function Ellipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }

        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;

        $k = $this->k;
        $h = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $rx) * $k, ($h - $y) * $k,
            ($x + $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k, ($h - ($y - $ry)) * $k,
            $x * $k, ($h - ($y - $ry)) * $k,
            ($x - $lx) * $k, ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k, ($h - $y) * $k,
            ($x - $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k, ($h - ($y + $ry)) * $k,
            $x * $k, ($h - ($y + $ry)) * $k,
            ($x + $lx) * $k, ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k, ($h - $y) * $k,
            $op
        ));
    }
}
