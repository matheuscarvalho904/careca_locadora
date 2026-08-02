<?php

namespace App\Services\ExternalData;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class CepLookupService
{
    /**
     * @return array<string, mixed>
     */
    public function lookup(string $postalCode): array
    {
        $cep = preg_replace('/\D+/', '', $postalCode);

        if (! is_string($cep) || strlen($cep) !== 8) {
            throw new InvalidArgumentException('Informe um CEP válido com 8 dígitos.');
        }

        $baseUrl = rtrim((string) config('services.viacep.base_url'), '/');

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.viacep.timeout', 10))
                ->retry(2, 250, throw: false)
                ->get("{$baseUrl}/ws/{$cep}/json/");
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar ao serviço de consulta de CEP.',
                previous: $exception,
            );
        }

        if ($response->failed()) {
            throw new RuntimeException('O serviço de consulta de CEP está indisponível no momento.');
        }

        $payload = $response->json();

        if (! is_array($payload) || Arr::get($payload, 'erro') === true) {
            throw new RuntimeException('CEP não encontrado.');
        }

        return [
            'postal_code' => preg_replace('/\D+/', '', (string) Arr::get($payload, 'cep')) ?: $cep,
            'address' => $this->nullableString(Arr::get($payload, 'logradouro')),
            'address_complement' => $this->nullableString(Arr::get($payload, 'complemento')),
            'district' => $this->nullableString(Arr::get($payload, 'bairro')),
            'city' => $this->nullableString(Arr::get($payload, 'localidade')),
            'state' => $this->nullableString(Arr::get($payload, 'uf')),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
