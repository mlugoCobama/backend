<?php

namespace Modules\Compras\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomologadorService
{
    private const TABLA = 'com_detalle_solicitud';

    public function analizar(
        int $limite = 20000,
        float $umbral = 0.70
    ): array {
        $registros = DB::table(self::TABLA)
            ->select([
                'id',
                'descripcion',
                'cantidad',
                'cat_unidades_medida_id',
                'solicitudes_compra_id',
            ])
            ->whereNotNull('descripcion')
            ->where('descripcion', '<>', '')
            ->orderBy('id')
            ->limit($limite)
            ->get();

        $normalizados = [];
        $exactos = [];
        $indicesTokens = [];

        foreach ($registros as $registro) {
            $texto = $this->normalizar($registro->descripcion);

            if ($texto === '') {
                continue;
            }

            $normalizados[$registro->id] = $texto;

            $exactos[$texto][] = $registro;

            foreach ($this->tokens($texto) as $token) {
                $indicesTokens[$token][] = $registro->id;
            }
        }

        // Agrupar descripciones idénticas.
        $gruposExactos = [];

        foreach ($exactos as $descripcion => $items) {
            if (count($items) < 2) {
                continue;
            }

            $gruposExactos[] = [
                'descripcion_normalizada' => $descripcion,
                'total_registros' => count($items),
                'registros' => array_values($items),
            ];
        }

        // Generar candidatos que compartan al menos una palabra.
        $pares = [];

        foreach ($indicesTokens as $ids) {
            $ids = array_values(array_unique($ids));
            $total = count($ids);

            for ($i = 0; $i < $total; $i++) {
                for ($j = $i + 1; $j < $total; $j++) {
                    $idA = min($ids[$i], $ids[$j]);
                    $idB = max($ids[$i], $ids[$j]);

                    $pares["{$idA}:{$idB}"] = [$idA, $idB];
                }
            }
        }

        $porId = $registros->keyBy('id');
        $candidatos = [];

        foreach ($pares as [$idA, $idB]) {
            $a = $porId->get($idA);
            $b = $porId->get($idB);

            if (!$a || !$b) {
                continue;
            }

            $textoA = $normalizados[$idA] ?? '';
            $textoB = $normalizados[$idB] ?? '';

            if ($textoA === $textoB) {
                continue;
            }

            $similitud = $this->similitud($textoA, $textoB);

            if ($similitud < $umbral) {
                continue;
            }

            $candidatos[] = [
                'registro_a' => $this->formatear($a),
                'registro_b' => $this->formatear($b),
                'similitud' => round($similitud * 100, 2),
                'misma_unidad' =>
                    $a->cat_unidades_medida_id
                    == $b->cat_unidades_medida_id,
                'requiere_revision' => true,
            ];
        }

        usort(
            $candidatos,
            fn ($a, $b) =>
                $b['similitud'] <=> $a['similitud']
        );

        return [
            'resumen' => [
                'registros_analizados' => count($normalizados),
                'grupos_exactos' => count($gruposExactos),
                'pares_probables' => count($candidatos),
                'limite_aplicado' => $limite,
                'umbral_porcentaje' => $umbral * 100,
            ],
            'grupos_exactos' => $gruposExactos,
            'candidatos' => $candidatos,
        ];
    }

    private function normalizar(string $texto): string
    {
        $texto = Str::ascii(Str::lower(trim($texto)));

        $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto);

        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    private function tokens(string $texto): array
    {
        $ignoradas = [
            'de', 'del', 'la', 'las', 'el', 'los',
            'para', 'por', 'con', 'sin', 'un', 'una',
        ];

        return array_values(array_unique(array_filter(
            explode(' ', $texto),
            fn ($token) =>
                strlen($token) >= 2
                && !in_array($token, $ignoradas, true)
        )));
    }

    private function similitud(string $a, string $b): float
    {
        $tokensA = $this->tokens($a);
        $tokensB = $this->tokens($b);

        $union = array_unique(array_merge($tokensA, $tokensB));

        if (count($union) === 0) {
            return 0;
        }

        $interseccion = array_intersect($tokensA, $tokensB);

        $similitudTokens =
            count($interseccion) / count($union);

        similar_text($a, $b, $porcentajeTexto);

        $similitudTexto = $porcentajeTexto / 100;

        return (0.60 * $similitudTokens)
            + (0.40 * $similitudTexto);
    }

    private function formatear(object $registro): array
    {
        return [
            'id' => $registro->id,
            'descripcion' => $registro->descripcion,
            'cantidad' => $registro->cantidad,
            'cat_unidades_medida_id' =>
                $registro->cat_unidades_medida_id,
            'solicitudes_compra_id' =>
                $registro->solicitudes_compra_id,
        ];
    }
}
