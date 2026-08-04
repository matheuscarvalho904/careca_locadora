<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RentalDeliveryItem extends Model {
    use HasFactory,HasUuids;
    protected $guarded=[];
    protected function casts():array{return ['odometer'=>'decimal:2','hourmeter'=>'decimal:2','body_ok'=>'boolean','tires_ok'=>'boolean','lights_ok'=>'boolean','glass_ok'=>'boolean','documents_ok'=>'boolean','accessories_ok'=>'boolean','cleanliness_ok'=>'boolean','primary_key_delivered'=>'boolean','spare_key_delivered'=>'boolean','manual_delivered'=>'boolean','photos'=>'array','checklist'=>'array'];}
    public function delivery():BelongsTo{return $this->belongsTo(RentalDelivery::class,'delivery_id');}
    public function contractItem():BelongsTo{return $this->belongsTo(RentalContractItem::class,'contract_item_id');}
    public function asset():BelongsTo{return $this->belongsTo(Asset::class);}
    public function damageMarks():MorphMany{return $this->morphMany(RentalDamageMark::class,'inspectable');}
}
