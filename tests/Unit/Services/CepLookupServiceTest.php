<?php

namespace Tests\Unit\Services;

use App\Services\ExternalData\CepLookupService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CepLookupServiceTest extends TestCase
{
    public function test_it_maps_viacep_response(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/*/json/' => Http::response([
                'cep' => '78.050-000',
                'logradouro' => 'Avenida Historiador Rubens de Mendonça',
                'complemento' => '',
                'bairro' => 'Bosque da Saúde',
                'localidade' => 'Cuiabá',
                'uf' => 'MT',
            ]),
        ]);

        $data = app(CepLookupService::class)->lookup('78.050-000');

        $this->assertSame('78050000', $data['postal_code']);
        $this->assertSame('Avenida Historiador Rubens de Mendonça', $data['address']);
        $this->assertSame('Bosque da Saúde', $data['district']);
        $this->assertSame('Cuiabá', $data['city']);
        $this->assertSame('MT', $data['state']);
    }

    public function test_it_rejects_invalid_cep_before_request(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        app(CepLookupService::class)->lookup('123');

        Http::assertNothingSent();
    }

    public function test_it_reports_unknown_cep(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/*/json/' => Http::response(['erro' => true]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CEP não encontrado.');

        app(CepLookupService::class)->lookup('99999999');
    }
}
