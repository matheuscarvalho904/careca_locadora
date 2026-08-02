<?php

namespace App\Services\Fleet\Providers;

use App\Contracts\Fleet\VehicleLookupProvider;
use App\Data\Fleet\VehicleLookupResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class PuxaPlacaVehicleLookupProvider implements VehicleLookupProvider
{
    public function lookup(string $plate): VehicleLookupResult
    {
        $plate = $this->normalizePlate($plate);

        if ((bool) config('fleet.vehicle_lookup.puxaplaca.check_balance_before_lookup', true)) {
            $minimum = (float) config('fleet.vehicle_lookup.puxaplaca.minimum_balance', 0.08);
            $balance = $this->balance();

            if ($balance < $minimum) {
                throw new RuntimeException(
                    'Saldo insuficiente na PuxaPlaca. Saldo atual: R$ '
                    . number_format($balance, 2, ',', '.')
                );
            }
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['token' => $this->token()])
                ->timeout($this->timeout())
                ->retry(2, 350, throw: false)
                ->get($this->baseUrl().'/consulta/'.$plate);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar à PuxaPlaca.',
                previous: $exception,
            );
        }

        $this->ensureSuccessful($response);

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('A PuxaPlaca retornou uma resposta inválida.');
        }

        [$basic, $renavamData, $chassisData, $fipeMatches] = $this->extractSections($payload);
        $bestFipe = $this->bestFipeMatch($fipeMatches);

        return new VehicleLookupResult(
            plate: $this->normalizePlate(
                $this->scalarString(
                    $basic['placa_modelo_novo']
                    ?? $basic['placa']
                    ?? $plate
                ) ?? $plate
            ),
            oldPlate: $this->normalizeOptionalPlate($basic['placa_modelo_antigo'] ?? null),
            brand: $this->scalarString($basic['marca'] ?? null),
            model: $this->scalarString($basic['modelo'] ?? $basic['marcamodelo'] ?? null),
            version: $this->scalarString($basic['versao'] ?? null),
            manufactureYear: $this->scalarInteger(
                $basic['ano_fabricacao']
                ?? $basic['ano']
                ?? null
            ),
            modelYear: $this->scalarInteger(
                $basic['ano_modelo']
                ?? $basic['anoModelo']
                ?? null
            ),
            color: $this->normalizeText($basic['cor'] ?? null),
            fuelType: $this->normalizeFuel(
                $basic['combustivel']
                ?? $bestFipe['combustivel']
                ?? null
            ),
            transmission: $this->normalizeText(
                $basic['cambio']
                ?? $basic['caixa_cambio']
                ?? null
            ),
            seats: $this->scalarInteger(
                $basic['passageiros']
                ?? $basic['lotacao']
                ?? null
            ),
            chassis: $this->scalarString(
                $chassisData['chassi']
                ?? $basic['chassi']
                ?? null
            ),
            renavam: $this->scalarString(
                $renavamData['renavam']
                ?? $basic['renavam']
                ?? null
            ),
            engineNumber: $this->scalarString($basic['numero_motor'] ?? null),
            engineDescription: $this->scalarString($basic['motor'] ?? null),
            engineDisplacementCc: $this->scalarInteger($basic['cilindradas'] ?? null),
            enginePowerHp: $this->scalarInteger($basic['potencia'] ?? null),
            axles: $this->scalarInteger($basic['qtdEixos'] ?? null),
            grossWeightT: $this->scalarFloat($basic['pbt'] ?? null),
            maximumTractionCapacityT: $this->scalarFloat($basic['cmt'] ?? null),
            city: $this->normalizeCity($basic['municipio'] ?? null),
            state: $this->normalizeState($basic['uf'] ?? null),
            vehicleType: $this->normalizeText(
                $basic['tipo_veiculo']
                ?? $basic['tipoVeiculo']
                ?? $basic['tipo']
                ?? null
            ),
            species: $this->normalizeText($basic['especie'] ?? null),
            bodyType: $this->normalizeText(
                $basic['carroceria']
                ?? $basic['tipoCarroceria']
                ?? null
            ),
            origin: $this->normalizeText(
                $basic['procedencia']
                ?? $basic['origem']
                ?? null
            ),
            segment: $this->normalizeText($basic['segmento'] ?? null),
            subsegment: $this->normalizeText($basic['sub_segmento'] ?? null),
            situation: $this->normalizeText(
                $basic['situacao']
                ?? $basic['mensagemRetorno']
                ?? null
            ),
            fipeCode: $this->scalarString(
                $bestFipe['codigo_fipe']
                ?? $bestFipe['codigo']
                ?? null
            ),
            fipeDescription: $this->scalarString(
                $bestFipe['modelo']
                ?? $bestFipe['descricao']
                ?? null
            ),
            fipeValue: $this->parseMoney(
                $bestFipe['valor']
                ?? $bestFipe['valor_atual']
                ?? null
            ),
            fipeReferenceMonth: $this->scalarString(
                $bestFipe['mes_referencia']
                ?? null
            ),
            fipeScore: $this->scalarInteger($bestFipe['score'] ?? null),
            raw: $payload,
        );
    }

    public function balance(): float
    {
        try {
            $response = Http::acceptJson()
                ->withHeaders(['token' => $this->token()])
                ->timeout($this->timeout())
                ->retry(2, 250, throw: false)
                ->get($this->baseUrl().'/saldo');
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível consultar o saldo da PuxaPlaca.',
                previous: $exception,
            );
        }

        $this->ensureSuccessful($response, true);

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('A PuxaPlaca retornou um saldo inválido.');
        }

        $value = Arr::get($payload, 'saldo')
            ?? Arr::get($payload, 'dados.saldo')
            ?? Arr::get($payload, 'data.saldo');

        $parsed = $this->parseDecimal($value);

        if ($parsed === null) {
            throw new RuntimeException('Não foi possível interpretar o saldo da PuxaPlaca.');
        }

        return $parsed;
    }

    public function name(): string
    {
        return 'puxaplaca';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, mixed>, 3: mixed}
     */
    private function extractSections(array $payload): array
    {
        $basic = Arr::get($payload, 'basico.dados');
        $renavam = Arr::get($payload, 'renavam.dados');
        $chassis = Arr::get($payload, 'chassi.dados');
        $fipe = Arr::get($payload, 'fipe.dados');

        if (! is_array($basic)) {
            $legacy = Arr::get($payload, 'data');

            if (is_array($legacy)) {
                $basic = $legacy;
                $renavam = ['renavam' => $legacy['renavam'] ?? null];
                $chassis = ['chassi' => $legacy['chassi'] ?? null];

                $legacyFipe = $legacy['fipe'] ?? [];
                $fipe = is_array($legacyFipe) ? [$legacyFipe] : [];
            } else {
                $basic = [];
            }
        }

        return [
            $basic,
            is_array($renavam) ? $renavam : [],
            is_array($chassis) ? $chassis : [],
            $fipe,
        ];
    }

    private function token(): string
    {
        $token = trim((string) config('fleet.vehicle_lookup.puxaplaca.token'));

        if ($token === '') {
            throw new RuntimeException(
                'Configure PUXAPLACA_TOKEN no arquivo .env antes de consultar placas.'
            );
        }

        return $token;
    }

    private function baseUrl(): string
    {
        return rtrim(
            (string) config(
                'fleet.vehicle_lookup.puxaplaca.base_url',
                'https://api.puxaplaca.app/v2'
            ),
            '/'
        );
    }

    private function timeout(): int
    {
        return max(5, (int) config('fleet.vehicle_lookup.puxaplaca.timeout', 15));
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

    private function normalizeOptionalPlate(mixed $plate): ?string
    {
        $plate = $this->scalarString($plate);

        if ($plate === null) {
            return null;
        }

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate) ?? '');
    }

    private function ensureSuccessful(
        Response $response,
        bool $balanceRequest = false,
    ): void {
        if ($response->successful()) {
            return;
        }

        $payload = $response->json();
        $message = is_array($payload)
            ? $this->scalarString(
                $payload['message']
                ?? $payload['mensagem']
                ?? $payload['erro']
                ?? null
            )
            : null;

        throw match ($response->status()) {
            400, 404, 422 => new RuntimeException(
                $message ?: (
                    $balanceRequest
                        ? 'Não foi possível consultar o saldo.'
                        : 'Veículo não encontrado para a placa informada.'
                )
            ),
            401, 403 => new RuntimeException(
                $message ?: 'Token da PuxaPlaca inválido ou sem permissão.'
            ),
            402 => new RuntimeException(
                $message ?: 'Saldo insuficiente na PuxaPlaca.'
            ),
            429 => new RuntimeException(
                $message ?: 'Limite de consultas da PuxaPlaca atingido.'
            ),
            default => new RuntimeException(
                $message ?: 'A PuxaPlaca está indisponível no momento.'
            ),
        };
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

        $valid = array_values(array_filter(
            $matches,
            static fn (mixed $item): bool => is_array($item)
        ));

        if ($valid === []) {
            return [];
        }

        usort(
            $valid,
            static fn (array $a, array $b): int =>
                ((int) ($b['score'] ?? 0)) <=> ((int) ($a['score'] ?? 0))
        );

        return $valid[0];
    }

    private function scalarString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function scalarInteger(mixed $value): ?int
    {
        if (! is_scalar($value) || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function scalarFloat(mixed $value): ?float
    {
        return $this->parseDecimal($value);
    }

    private function parseDecimal(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim(str_replace(['R$', ' '], '', $value));

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseMoney(mixed $value): ?float
    {
        return $this->parseDecimal($value);
    }

    private function normalizeFuel(mixed $value): ?string
    {
        $value = $this->normalizeText($value);

        if ($value === null) {
            return null;
        }

        return match (mb_strtoupper($value)) {
            'ALCOOL/GASOLINA',
            'ÁLCOOL/GASOLINA',
            'GASOLINA/ALCOOL',
            'GASOLINA/ÁLCOOL' => 'Flex',
            default => $value,
        };
    }

    private function normalizeState(mixed $value): ?string
    {
        $value = $this->scalarString($value);

        return $value !== null ? mb_strtoupper($value) : null;
    }

    private function normalizeCity(mixed $value): ?string
    {
        $value = $this->scalarString($value);

        if ($value === null) {
            return null;
        }

        $normalized = mb_convert_case(
            mb_strtolower($value),
            MB_CASE_TITLE,
            'UTF-8'
        );

        return str_replace(
            ['Sao ', ' Joao ', ' Jose '],
            ['São ', ' João ', ' José '],
            $normalized
        );
    }

    private function normalizeText(mixed $value): ?string
    {
        $value = $this->scalarString($value);

        if ($value === null) {
            return null;
        }

        $value = str_replace(
            ['NãO', 'NÃO APLICAVEL', 'NAO APLICAVEL'],
            ['NÃO', 'Não aplicável', 'Não aplicável'],
            $value
        );

        if (mb_strtoupper($value) === $value) {
            return mb_convert_case(
                mb_strtolower($value),
                MB_CASE_TITLE,
                'UTF-8'
            );
        }

        return $value;
    }
}
