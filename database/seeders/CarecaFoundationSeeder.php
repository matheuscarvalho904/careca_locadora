<?php
namespace Database\Seeders;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class CarecaFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->firstOrCreate(['slug'=>'careca-locadora'], [
            'name'=>'Careca Locadora de Veículos','legal_name'=>'Careca Locadora de Veículos','timezone'=>'America/Cuiaba','locale'=>'pt_BR','currency'=>'BRL','status'=>'active',
        ]);
        User::query()->updateOrCreate(['email'=>env('CARECA_ADMIN_EMAIL','admin@carecalocadora.local')], [
            'organization_id'=>$organization->id,'name'=>env('CARECA_ADMIN_NAME','Administrador Careca Locadora'),
            'password'=>Hash::make(env('CARECA_ADMIN_PASSWORD','Mudar@123456')),'email_verified_at'=>now(),
            'is_platform_admin'=>true,'must_change_password'=>true,'status'=>'active','activated_at'=>now(),'locale'=>'pt_BR','timezone'=>'America/Cuiaba',
        ]);
    }
}
