<?php
namespace App\Models;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalDamageMark extends Model {
    use BelongsToOrganization, HasUuids, SoftDeletes;
    protected $guarded=[];
    protected $attributes=['severity'=>'light','condition'=>'preexisting','status'=>'active','estimated_value'=>0];
    protected function casts():array{return ['position_x'=>'decimal:4','position_y'=>'decimal:4','estimated_value'=>'decimal:2','metadata'=>'array'];}
    public function inspectable():MorphTo{return $this->morphTo();}
    public function asset():BelongsTo{return $this->belongsTo(Asset::class);}
    public function templateView():BelongsTo{return $this->belongsTo(InspectionDiagramView::class,'template_view_id');}
    public function photos():HasMany{return $this->hasMany(RentalDamagePhoto::class,'damage_mark_id');}
}
