<?php
namespace Database\Seeders;
use App\Models\AssetCategory;
use App\Models\InspectionDiagramTemplate;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InspectionDiagramSeeder extends Seeder {
    public function run():void {
        Organization::query()->each(function(Organization $organization):void{
            AssetCategory::query()->where('organization_id',$organization->id)->get()->each(
                function(AssetCategory $category)use($organization):void{
                    $diagram=$this->diagramType($category->name,$category->asset_type);
                    $template=InspectionDiagramTemplate::query()->updateOrCreate(
                        ['organization_id'=>$organization->id,'code'=>'inspection-'.Str::slug($category->prefix)],
                        ['asset_category_id'=>$category->id,'name'=>"Inspeção — {$category->name}",'asset_type'=>$category->asset_type,'status'=>'active','is_default'=>true,'metadata'=>['diagram_type'=>$diagram,'seeded'=>true]]
                    );
                    foreach([['front','Frontal',10],['rear','Traseira',20],['left','Lateral esquerda',30],['right','Lateral direita',40],['top','Superior',50]] as [$code,$name,$order]){
                        $template->views()->updateOrCreate(['code'=>$code],['name'=>$name,'image_path'=>"images/inspection-diagrams/{$diagram}-{$code}.svg",'display_order'=>$order,'is_active'=>true]);
                    }
                }
            );
        });
    }

    private function diagramType(string $name,string $assetType):string {
        $n=Str::lower(Str::ascii($name));
        if(Str::contains($n,['caminhao','cavalo','carreta','reboque','semi'])) return 'truck';
        if(Str::contains($n,['moto','motocicleta'])) return 'motorcycle';
        if($assetType!=='vehicle'||Str::contains($n,['escavadeira','retroescavadeira','motoniveladora','carregadeira','trator','rolo','gerador','compressor','plataforma','betoneira','torre','maquina','equipamento'])) return 'equipment';
        return 'vehicle';
    }
}
