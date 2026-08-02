<?php

namespace App\Services\ExternalData;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class CnpjLookupService
{
    /**
     * @return array<string, mixed>
     */
    public function lookup(string $document): array
    {
        $cnpj = preg_replace('/\D+/', '', $document);

        if (! is_string($cnpj) || strlen($cnpj) !== 14) {
            throw new InvalidArgumentException('Informe um CNPJ válido com 14 dígitos.');
        }

        $baseUrl = rtrim((string) config('services.brasil_api.base_url'), '/');

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.brasil_api.timeout', 10))
                ->retry(2, 250, throw: false)
                ->get("{$baseUrl}/cnpj/v1/{$cnpj}");
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar ao serviço de consulta de CNPJ.',
                previous: $exception,
            );
        }

        if ($response->notFound()) {
            throw new RuntimeException('CNPJ não encontrado.');
        }

        if ($response->failed()) {
            throw new RuntimeException('O serviço de consulta de CNPJ está indisponível no momento.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('O serviço de CNPJ retornou dados inválidos.');
        }

        return [
            'document' => $cnpj,
            'legal_name' => $this->nullableString(Arr::get($payload, 'razao_social')),
            'trade_name' => $this->nullableString(Arr::get($payload, 'nome_fantasia')),
            'registration_status' => $this->nullableString(
                Arr::get($payload, 'descricao_situacao_cadastral')
                    ?? Arr::get($payload, 'situacao_cadastral')
            ),
            'cnae' => $this->nullableString(Arr::get($payload, 'cnae_fiscal')),
            'opened_at' => $this->normalizeDate(Arr::get($payload, 'data_inicio_atividade')),
            'postal_code' => $this->digits(Arr::get($payload, 'cep')),
            'address' => $this->buildStreet($payload),
            'address_number' => $this->nullableString(Arr::get($payload, 'numero')),
            'address_complement' => $this->nullableString(Arr::get($payload, 'complemento')),
            'district' => $this->nullableString(Arr::get($payload, 'bairro')),
            'city' => $this->nullableString(Arr::get($payload, 'municipio')),
            'state' => $this->nullableString(Arr::get($payload, 'uf')),
            'phone' => $this->firstPhone($payload),
            'email' => $this->nullableString(Arr::get($payload, 'email')),
            'company_size' => $this->mapCompanySize(
                Arr::get($payload, 'porte')
                    ?? Arr::get($payload, 'descricao_porte')
            ),
            'external_data' => [
                'provider' => 'brasil_api',
                'payload' => $payload,
            ],
            'external_data_synced_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildStreet(array $payload): ?string
    {
        $type = $this->nullableString(Arr::get($payload, 'descricao_tipo_de_logradouro'));
        $street = $this->nullableString(Arr::get($payload, 'logradouro'));

        return trim(collect([$type, $street])->filter()->implode(' ')) ?: null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function firstPhone(array $payload): ?string
    {
        return $this->nullableString(
            Arr::get($payload, 'ddd_telefone_1')
                ?? Arr::get($payload, 'ddd_telefone_2')
        );
    }

    private function digits(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function mapCompanySize(mixed $value): ?string
    {
        $normalized = mb_strtolower($this->nullableString($value) ?? '');

        return match (true) {
            str_contains($normalized, 'micro empreendedor'),
            str_contains($normalized, 'microempreendedor'),
            $normalized === 'mei' => 'mei',

            str_contains($normalized, 'micro empresa'),
            str_contains($normalized, 'microempresa') => 'micro',

            str_contains($normalized, 'pequeno porte'),
            str_contains($normalized, 'epp') => 'small',

            str_contains($normalized, 'médio'),
            str_contains($normalized, 'medio') => 'medium',

            str_contains($normalized, 'grande') => 'large',

            default => null,
        };
    }
}
