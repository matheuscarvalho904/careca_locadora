<?php

namespace App\Services\Fleet\Providers;

use App\Contracts\Fleet\VehicleLookupProvider;
use App\Data\Fleet\VehicleLookupResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class ApiPlacasVehicleLookupProvider implements VehicleLookupProvider
{
    public function lookup(string $plate): VehicleLookupResult
    {
        $normalizedPlate = $this->normalizePlate($plate);
        $token = trim((string) config('fleet.vehicle_lookup.api_placas.token'));

        if ($token === '') {
            throw new RuntimeException(
                'Configure API_PLACAS_TOKEN no arquivo .env antes de consultar placas.'
            );
        }

        $baseUrl = rtrim(
            (string) config('fleet.vehicle_lookup.api_placas.base_url'),
            '/'
        );

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('fleet.vehicle_lookup.api_placas.timeout', 15))
                ->retry(2, 300, throw: false)
                ->get("{$baseUrl}/consulta/{$normalizedPlate}/{$token}");
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar ao serviço de consulta de placa.',
                previous: $exception,
            );
        }

        if ($response->status() === 401) {
            throw new RuntimeException('A placa informada é inválida.');
        }

        if ($response->status() === 402) {
            throw new RuntimeException('O token da API de placas é inválido.');
        }

        if ($response->status() === 406 || $response->notFound()) {
            throw new RuntimeException('Nenhum veículo foi encontrado para esta placa.');
        }

        if ($response->status() === 429) {
            throw new RuntimeException('O limite de consultas de placa foi atingido.');
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'O serviço de consulta de placa está indisponível no momento.'
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('O serviço de placa retornou dados inválidos.');
        }

        $fipe = $this->bestFipeMatch(
            Arr::get($payload, 'fipe.dados', [])
        );

        return new VehicleLookupResult(
            plate: $this->normalizePlate(
                (string) (Arr::get($payload, 'placa')
                    ?? Arr::get($payload, 'extra.placa_modelo_novo')
                    ?? $normalizedPlate)
            ),
            brand: $this->string(
                Arr::get($payload, 'marca')
                    ?? Arr::get($payload, 'MARCA')
            ),
            model: $this->string(
                Arr::get($payload, 'modelo')
                    ?? Arr::get($payload, 'MODELO')
            ),
            version: $this->string(
                Arr::get($payload, 'VERSAO')
                    ?? Arr::get($payload, 'versao')
            ),
            manufactureYear: $this->integer(
                Arr::get($payload, 'extra.ano_fabricacao')
                    ?? Arr::get($payload, 'ano')
            ),
            modelYear: $this->integer(
                Arr::get($payload, 'extra.ano_modelo')
                    ?? Arr::get($payload, 'anoModelo')
            ),
            color: $this->string(Arr::get($payload, 'cor')),
            fuelType: $this->string(
                Arr::get($payload, 'extra.combustivel')
                    ?? Arr::get($fipe, 'combustivel')
            ),
            transmission: $this->string(
                Arr::get($payload, 'extra.caixa_cambio')
            ),
            seats: $this->integer(
                Arr::get($payload, 'extra.quantidade_passageiro')
            ),
            chassis: $this->string(Arr::get($payload, 'chassi')),
            renavam: $this->string(
                Arr::get($payload, 'renavam')
                    ?? Arr::get($payload, 'extra.renavam')
            ),
            city: $this->string(
                Arr::get($payload, 'municipio')
                    ?? Arr::get($payload, 'extra.municipio')
            ),
            state: $this->string(
                Arr::get($payload, 'uf')
                    ?? Arr::get($payload, 'extra.uf')
            ),
            vehicleType: $this->string(
                Arr::get($payload, 'extra.tipo_veiculo')
            ),
            bodyType: $this->string(
                Arr::get($payload, 'extra.tipo_carroceria')
                    ?? Arr::get($payload, 'extra.carroceria')
            ),
            situation: $this->string(
                Arr::get($payload, 'situacao')
                    ?? Arr::get($payload, 'extra.situacao_veiculo')
            ),
            fipeCode: $this->string(Arr::get($fipe, 'codigo_fipe')),
            fipeDescription: $this->string(Arr::get($fipe, 'texto_modelo')),
            fipeValue: $this->string(Arr::get($fipe, 'texto_valor')),
            fipeScore: $this->integer(Arr::get($fipe, 'score')),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'api_placas';
    }

    private function normalizePlate(string $plate): string
    {
        $plate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate) ?? '');

        if (! preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $plate)) {
            throw new InvalidArgumentException(
                'Informe uma placa válida no padrão antigo ou Mercosul.'
            );
        }

        return $plate;
    }

    /**
     * @param mixed $matches
     * @return array<string, mixed>
     */
    private function bestFipeMatch(mixed $matches): array
    {
        if (! is_array($matches)) {
            return [];
        }

        $matches = array_filter($matches, 'is_array');

        if ($matches === []) {
            return [];
        }

        usort(
            $matches,
            static fn (array $a, array $b): int =>
                ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0))
        );

        return $matches[0] ?? [];
    }

    private function string(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function integer(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
