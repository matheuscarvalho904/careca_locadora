<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();
        return ['name'=>$name,'slug'=>Str::slug($name).'-'.fake()->unique()->numberBetween(100,999),'email'=>fake()->companyEmail(),'timezone'=>'America/Cuiaba','locale'=>'pt_BR','currency'=>'BRL','status'=>'active'];
    }
}
