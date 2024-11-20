<?php

namespace Tests\Feature;

use App\Models\datos_acumulados;
use App\Models\DatosAcumulados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ObtenerDatosTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_index(): void
    {
        $response = $this->get('api/data');
        $response->assertStatus(Response::HTTP_OK);

    }
    public function test_store(): void
    {
        //$data = DatosAcumulados::factory()->create();

        $payload = [
            'anio' => fake()->year(),
            'mes' => fake()->month(),
            'valor' => fake()->randomFloat(2, 0, 1000000),
            "cat_empresas_id" => 1,
            "cat_reportes_id" => 1
        ];

        $this->json('post', 'api/data', $payload)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('datos_acumulados', $payload);

        DatosAcumulados::where([
            ['anio','=',$payload['anio']],
            ['mes' ,'=',$payload['mes']],
            ['valor','=',$payload['valor']],
        ])->delete();

    }
}
