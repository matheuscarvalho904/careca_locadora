<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\PaymentCondition;
use Illuminate\Database\Seeder;

class PaymentConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            ['code' => 'AVISTA', 'name' => 'À vista', 'installments' => 1, 'first_due_days' => 0, 'interval_days' => 0],
            ['code' => '7D', 'name' => '7 dias', 'installments' => 1, 'first_due_days' => 7, 'interval_days' => 0],
            ['code' => '14D', 'name' => '14 dias', 'installments' => 1, 'first_due_days' => 14, 'interval_days' => 0],
            ['code' => '21D', 'name' => '21 dias', 'installments' => 1, 'first_due_days' => 21, 'interval_days' => 0],
            ['code' => '28D', 'name' => '28 dias', 'installments' => 1, 'first_due_days' => 28, 'interval_days' => 0],
            ['code' => '30D', 'name' => '30 dias', 'installments' => 1, 'first_due_days' => 30, 'interval_days' => 0],
            ['code' => '30-60', 'name' => '30/60 dias', 'installments' => 2, 'first_due_days' => 30, 'interval_days' => 30],
            ['code' => '30-60-90', 'name' => '30/60/90 dias', 'installments' => 3, 'first_due_days' => 30, 'interval_days' => 30],
            ['code' => '15-30-45', 'name' => '15/30/45 dias', 'installments' => 3, 'first_due_days' => 15, 'interval_days' => 15],
            ['code' => '7-14-21-28', 'name' => '7/14/21/28 dias', 'installments' => 4, 'first_due_days' => 7, 'interval_days' => 7],
            ['code' => 'ENT-30', 'name' => 'Entrada + 30 dias', 'installments' => 2, 'first_due_days' => 0, 'interval_days' => 30, 'requires_down_payment' => true],
            ['code' => 'ENT-30-60', 'name' => 'Entrada + 30/60 dias', 'installments' => 3, 'first_due_days' => 0, 'interval_days' => 30, 'requires_down_payment' => true],
        ];

        Organization::query()->each(function (Organization $organization) use ($conditions): void {
            foreach ($conditions as $condition) {
                PaymentCondition::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'code' => $condition['code'],
                    ],
                    [
                        'name' => $condition['name'],
                        'installments' => $condition['installments'],
                        'first_due_days' => $condition['first_due_days'],
                        'interval_days' => $condition['interval_days'],
                        'requires_down_payment' => $condition['requires_down_payment'] ?? false,
                        'down_payment_percent' => $condition['down_payment_percent'] ?? 0,
                        'status' => 'active',
                    ],
                );
            }
        });
    }
}
