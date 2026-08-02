<?php

namespace App\Services\Procurement;

use Illuminate\Validation\ValidationException;

final class ProcurementValidationService
{
    public function validatePurchaseItem(array $item): void
    {
        if (blank($item['product_id'] ?? null)) {
            throw ValidationException::withMessages([
                'product_id' => 'Uma Ordem de Compra aceita somente produtos cadastrados.',
            ]);
        }

        if (filled($item['service_description'] ?? null)) {
            throw ValidationException::withMessages([
                'service_description' => 'Serviços devem ser lançados em uma Ordem de Serviço.',
            ]);
        }

        $this->validateApplication(
            item: $item,
            allowStock: true,
        );
    }

    public function validateServiceItem(array $item): void
    {
        if (filled($item['product_id'] ?? null)) {
            throw ValidationException::withMessages([
                'product_id' => 'Produtos devem ser lançados em uma Ordem de Compra.',
            ]);
        }

        if (blank($item['service_description'] ?? null)) {
            throw ValidationException::withMessages([
                'service_description' => 'Informe a descrição do serviço.',
            ]);
        }

        if (filled($item['service_id'] ?? null)) {
            throw ValidationException::withMessages([
                'service_id' => 'O serviço deve ser digitado diretamente na Ordem de Serviço.',
            ]);
        }

        if (filled($item['warehouse_id'] ?? null)) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Serviços não geram entrada em estoque.',
            ]);
        }

        $this->validateApplication(
            item: $item,
            allowStock: false,
        );
    }

    private function validateApplication(
        array $item,
        bool $allowStock,
    ): void {
        $type = $item['application_type'] ?? null;

        if (
            $type === 'application_center'
            && blank($item['application_center_id'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'application_center_id' => 'Selecione o centro de aplicação.',
            ]);
        }

        if ($type === 'asset') {
            if (blank($item['asset_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'asset_id' => 'Selecione o ativo de aplicação.',
                ]);
            }

            if (! in_array(
                $item['meter_type'] ?? null,
                ['odometer', 'hourmeter'],
                true,
            )) {
                throw ValidationException::withMessages([
                    'meter_type' => 'Informe se a leitura é de hodômetro ou horímetro.',
                ]);
            }

            if (
                ! is_numeric($item['meter_reading'] ?? null)
                || (float) $item['meter_reading'] < 0
            ) {
                throw ValidationException::withMessages([
                    'meter_reading' => 'Informe uma leitura válida do ativo.',
                ]);
            }
        }

        if ($type === 'stock') {
            if (! $allowStock) {
                throw ValidationException::withMessages([
                    'application_type' => 'Uma Ordem de Serviço não pode ser aplicada em estoque.',
                ]);
            }

            if (blank($item['warehouse_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Selecione o estoque de destino.',
                ]);
            }
        }

        if (! in_array(
            $type,
            [
                'application_center',
                'asset',
                'stock',
                'direct_consumption',
            ],
            true,
        )) {
            throw ValidationException::withMessages([
                'application_type' => 'Tipo de aplicação inválido.',
            ]);
        }
    }
}
