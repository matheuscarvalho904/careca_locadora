<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RentalDamagePhoto extends Model {
    use HasUuids;
    protected $guarded=[];
    public function damageMark():BelongsTo{return $this->belongsTo(RentalDamageMark::class,'damage_mark_id');}
}
