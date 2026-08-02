<?php

namespace App\Support\UI;

final class StatusPalette
{
    public static function color(?string $status): string
    {
        return match ($status) {
            'active',
            'available',
            'approved',
            'completed',
            'paid',
            'valid' => 'success',

            'trial',
            'pending',
            'scheduled',
            'reserved',
            'warning',
            'due_soon' => 'warning',

            'blocked',
            'suspended',
            'rejected',
            'overdue',
            'cancelled',
            'expired',
            'maintenance' => 'danger',

            'in_progress',
            'rented',
            'processing' => 'info',

            default => 'gray',
        };
    }

    public static function label(?string $status): string
    {
        return match ($status) {
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'available' => 'Disponível',
            'reserved' => 'Reservado',
            'rented' => 'Locado',
            'maintenance' => 'Em manutenção',
            'blocked' => 'Bloqueado',
            'suspended' => 'Suspenso',
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Reprovado',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'valid' => 'Válido',
            'expired' => 'Vencido',
            'due_soon' => 'Próximo do vencimento',
            default => filled($status) ? ucfirst(str_replace('_', ' ', $status)) : 'Não informado',
        };
    }

    private function __construct()
    {
    }
}
