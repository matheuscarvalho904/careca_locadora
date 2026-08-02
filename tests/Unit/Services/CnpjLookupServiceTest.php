<?php

namespace Tests\Unit\Services;

use App\Services\ExternalData\CnpjLookupService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class CnpjLookupServiceTest extends TestCase
{
    public function test_it_maps_brasil_api_cnpj_response(): void
    {
        Http::fake([
            'https://brasilapi.com.br/api/cnpj/v1/*' => Http::response([
                'cnpj' => '06069123000165',
                'razao_social' => 'CARECA LOCADORA DE VEICULOS LTDA',
                'nome_fantasia' => 'CARECA LOCADORA',
                'descricao_situacao_cadastral' => 'ATIVA',
                'cnae_fiscal' => 7711000,
                'data_inicio_atividade' => '2020-08-21',
                'cep' => '78050000',
                'descricao_tipo_de_logradouro' => 'AVENIDA',
                'logradouro' => 'HISTORIADOR RUBENS DE MENDONCA',
                'numero' => '1856',
                'complemento' => 'SALA 01',
                'bairro' => 'BOSQUE DA SAUDE',
                'municipio' => 'CUIABA',
                'uf' => 'MT',
                'ddd_telefone_1' => '65999999999',
                'email' => 'contato@carecalocadora.com.br',
                'porte' => 'EMPRESA DE PEQUENO PORTE',
            ]),
        ]);

        $data = app(CnpjLookupService::class)->lookup('06.069.123/0001-65');

        $this->assertSame('06069123000165', $data['document']);
        $this->assertSame('CARECA LOCADORA DE VEICULOS LTDA', $data['legal_name']);
        $this->assertSame('CARECA LOCADORA', $data['trade_name']);
        $this->assertSame('AVENIDA HISTORIADOR RUBENS DE MENDONCA', $data['address']);
        $this->assertSame('CUIABA', $data['city']);
        $this->assertSame('MT', $data['state']);
        $this->assertSame('small', $data['company_size']);
        $this->assertSame('brasil_api', $data['external_data']['provider']);
    }

    public function test_it_rejects_invalid_cnpj_before_request(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        app(CnpjLookupService::class)->lookup('123');

        Http::assertNothingSent();
    }
}
