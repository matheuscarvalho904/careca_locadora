<?php

namespace Database\Factories;

use App\Models\BusinessPartner;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessPartner>
 */
class BusinessPartnerFactory extends Factory
{
    protected $model = BusinessPartner::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'person_type' => 'legal',
            'roles' => ['customer'],
            'legal_name' => fake()->company(),
            'trade_name' => fake()->company(),
            'document' => fake()->unique()->numerify('##############'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('###########'),
            'status' => 'active',
            'credit_limit' => 0,
            'payment_term_days' => 0,
        ];
    }
}
