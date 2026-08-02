<?php

namespace Tests\Unit\Services;

use App\Services\Fleet\Providers\ApiPlacasVehicleLookupProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class ApiPlacasVehicleLookupProviderTest extends TestCase
{
    public function test_it_maps_vehicle_and_best_fipe_result(): void
    {
        Config::set('fleet.vehicle_lookup.api_placas.token', 'test-token');

        Http::fake([
            'https://wdapi2.com.br/consulta/*' => Http::response([
                'marca' => 'FIAT',
                'modelo' => 'STRADA',
                'VERSAO' => 'FREEDOM 1.3',
                'ano' => '2024',
                'anoModelo' => '2025',
                'chassi' => '*****12345',
                'cor' => 'Branca',
                'municipio' => 'Aripuana',
                'uf' => 'MT',
                'placa' => 'ABC1D23',
                'situacao' => 'Sem restricao',
                'extra' => [
                    'combustivel' => 'Alcool / Gasolina',
                    'caixa_cambio' => 'Manual',
                    'quantidade_passageiro' => '5',
                    'tipo_veiculo' => 'Automovel',
                ],
                'fipe' => [
                    'dados' => [
                        [
                            'codigo_fipe' => '001111-1',
                            'texto_modelo' => 'Resultado inferior',
                            'texto_valor' => 'R$ 90.000,00',
                            'score' => 80,
                        ],
                        [
                            'codigo_fipe' => '001222-2',
                            'texto_modelo' => 'Fiat Strada Freedom 1.3',
                            'texto_valor' => 'R$ 105.000,00',
                            'score' => 100,
                        ],
                    ],
                ],
            ]),
        ]);

        $result = app(ApiPlacasVehicleLookupProvider::class)->lookup('ABC-1D23');

        $this->assertSame('ABC1D23', $result->plate);
        $this->assertSame('FIAT', $result->brand);
        $this->assertSame('STRADA', $result->model);
        $this->assertSame(2024, $result->manufactureYear);
        $this->assertSame(2025, $result->modelYear);
        $this->assertSame('001222-2', $result->fipeCode);
        $this->assertSame(100, $result->fipeScore);
    }

    public function test_it_rejects_invalid_plate_before_request(): void
    {
        Config::set('fleet.vehicle_lookup.api_placas.token', 'test-token');
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        app(ApiPlacasVehicleLookupProvider::class)->lookup('123');

        Http::assertNothingSent();
    }
}
