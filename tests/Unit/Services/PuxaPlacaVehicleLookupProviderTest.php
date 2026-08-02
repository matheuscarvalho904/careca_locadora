<?php

namespace Tests\Unit\Services;

use App\Services\Fleet\Providers\PuxaPlacaVehicleLookupProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class PuxaPlacaVehicleLookupProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('fleet.vehicle_lookup.puxaplaca.token', 'test-token');
        Config::set(
            'fleet.vehicle_lookup.puxaplaca.base_url',
            'https://api.puxaplaca.app/v2'
        );
        Config::set(
            'fleet.vehicle_lookup.puxaplaca.check_balance_before_lookup',
            true
        );
        Config::set(
            'fleet.vehicle_lookup.puxaplaca.minimum_balance',
            0.08
        );
    }

    public function test_it_maps_the_real_v2_response(): void
    {
        Http::fake([
            'https://api.puxaplaca.app/v2/saldo' => Http::response([
                'saldo' => 19.94,
            ]),
            'https://api.puxaplaca.app/v2/consulta/GAN9D77' => Http::response([
                'basico' => [
                    'error' => false,
                    'dados' => [
                        'placa_modelo_novo' => 'GAN9D77',
                        'placa_modelo_antigo' => 'GAN9377',
                        'marca' => 'FIAT',
                        'modelo' => 'UNO VIVACE 1.0',
                        'ano' => '2015',
                        'anoModelo' => '2016',
                        'cor' => 'Branca',
                        'combustivel' => 'ALCOOL/GASOLINA',
                        'municipio' => 'SAO JOSE DO RIO PRETO',
                        'uf' => 'SP',
                        'tipoVeiculo' => 'AUTOMOVEL',
                        'tipoCarroceria' => 'NãO APLICAVEL',
                        'lotacao' => '5',
                        'motor' => '1.0',
                        'cilindradas' => '1000',
                        'potencia' => '75',
                        'qtdEixos' => '2',
                        'pbt' => '1.2',
                        'cmt' => '2',
                    ],
                ],
                'fipe' => [
                    'dados' => [
                        [
                            'valor' => 'R$ 33.174,00',
                            'modelo' => 'UNO VIVACE/RUA 1.0 EVO Fire Flex 8V 5p',
                            'codigo_fipe' => '001303-0',
                            'mes_referencia' => 'julho de 2026',
                            'score' => 95,
                        ],
                    ],
                ],
                'renavam' => [
                    'dados' => ['renavam' => '01070248484'],
                ],
                'chassi' => [
                    'dados' => ['chassi' => '9BD19510ZG0708925'],
                ],
                'error' => false,
                'message' => 'Consulta efetuada com sucesso',
            ]),
        ]);

        $result = app(PuxaPlacaVehicleLookupProvider::class)->lookup('GAN-9D77');

        $this->assertSame('GAN9D77', $result->plate);
        $this->assertSame('GAN9377', $result->oldPlate);
        $this->assertSame('FIAT', $result->brand);
        $this->assertSame('UNO VIVACE 1.0', $result->model);
        $this->assertSame('Flex', $result->fuelType);
        $this->assertSame('9BD19510ZG0708925', $result->chassis);
        $this->assertSame('01070248484', $result->renavam);
        $this->assertSame('001303-0', $result->fipeCode);
        $this->assertSame(33174.0, $result->fipeValue);
        $this->assertSame(1000, $result->engineDisplacementCc);
        $this->assertSame(75, $result->enginePowerHp);
    }

    public function test_it_keeps_backward_compatibility_with_legacy_envelope(): void
    {
        Http::fake([
            'https://api.puxaplaca.app/v2/saldo' => Http::response([
                'dados' => ['saldo' => '0.5000'],
            ]),
            'https://api.puxaplaca.app/v2/consulta/GAN9D77' => Http::response([
                'data' => [
                    'placa' => 'GAN9D77',
                    'marca' => 'FIAT',
                    'modelo' => 'STRADA',
                    'versao' => 'FREEDOM 1.3',
                    'ano_fabricacao' => 2024,
                    'ano_modelo' => 2025,
                    'chassi' => '9BD00000000000001',
                    'renavam' => '12345678901',
                    'fipe' => [
                        'codigo' => '001234-5',
                        'modelo' => 'Fiat Strada Freedom 1.3',
                        'valor' => 'R$ 105.000,00',
                    ],
                ],
            ]),
        ]);

        $result = app(PuxaPlacaVehicleLookupProvider::class)->lookup('GAN9D77');

        $this->assertSame('FIAT', $result->brand);
        $this->assertSame('STRADA', $result->model);
        $this->assertSame('001234-5', $result->fipeCode);
        $this->assertSame(105000.0, $result->fipeValue);
    }

    public function test_it_reads_decimal_balance_without_multiplying_it(): void
    {
        Http::fake([
            'https://api.puxaplaca.app/v2/saldo' => Http::response([
                'dados' => ['saldo' => '0.5000'],
            ]),
        ]);

        $this->assertSame(
            0.5,
            app(PuxaPlacaVehicleLookupProvider::class)->balance()
        );
    }

    public function test_it_rejects_invalid_plate_without_consuming_credit(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        app(PuxaPlacaVehicleLookupProvider::class)->lookup('123');

        Http::assertNothingSent();
    }
}
