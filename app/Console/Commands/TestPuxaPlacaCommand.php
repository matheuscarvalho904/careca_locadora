<?php

namespace App\Console\Commands;

use App\Services\Fleet\Providers\PuxaPlacaVehicleLookupProvider;
use Illuminate\Console\Command;
use Throwable;

class TestPuxaPlacaCommand extends Command
{
    protected $signature = 'fleet:test-puxaplaca
        {plate? : Placa para consulta real}
        {--balance : Consultar somente o saldo}';

    protected $description = 'Testa a integração real com a API PuxaPlaca';

    public function handle(PuxaPlacaVehicleLookupProvider $provider): int
    {
        try {
            if ($this->option('balance')) {
                $this->info(
                    'Saldo PuxaPlaca: R$ '
                    . number_format($provider->balance(), 2, ',', '.')
                );

                return self::SUCCESS;
            }

            $plate = strtoupper(
                preg_replace(
                    '/[^A-Za-z0-9]/',
                    '',
                    (string) ($this->argument('plate') ?: '')
                ) ?? ''
            );

            if ($plate === '') {
                $this->error(
                    'Informe uma placa ou utilize --balance para consultar o saldo.'
                );

                return self::INVALID;
            }

            if (! $this->confirm(
                'Esta consulta pode consumir saldo real da PuxaPlaca. Continuar?',
                false
            )) {
                $this->warn('Consulta cancelada.');

                return self::SUCCESS;
            }

            $result = $provider->lookup($plate);

            $this->table(
                ['Campo', 'Valor'],
                [
                    ['Placa', $this->display($result->plate)],
                    ['Marca', $this->display($result->brand)],
                    ['Modelo', $this->display($result->model)],
                    ['Versão', $this->display($result->version)],
                    ['Ano fabricação', $this->display($result->manufactureYear)],
                    ['Ano modelo', $this->display($result->modelYear)],
                    ['Cor', $this->display($result->color)],
                    ['Combustível', $this->display($result->fuelType)],
                    ['Câmbio', $this->display($result->transmission)],
                    ['Passageiros', $this->display($result->seats)],
                    ['Chassi', $this->display($result->chassis)],
                    ['RENAVAM', $this->display($result->renavam)],
                    ['Município', $this->display($result->city)],
                    ['UF', $this->display($result->state)],
                    ['Tipo', $this->display($result->vehicleType)],
                    ['Carroceria', $this->display($result->bodyType)],
                    ['Situação', $this->display($result->situation)],
                    ['Código FIPE', $this->display($result->fipeCode)],
                    ['Descrição FIPE', $this->display($result->fipeDescription)],
                    ['Valor FIPE', $this->display($result->fipeValue)],
                    ['Score FIPE', $this->display($result->fipeScore)],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function display(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Não informado';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: 'Não informado';
    }
}
