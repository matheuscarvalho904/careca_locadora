<?php
namespace App\Models;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionDiagramTemplate extends Model {
    use BelongsToOrganization, HasUuids, SoftDeletes;
    protected $guarded=[];
    protected function casts():array{return ['is_default'=>'boolean','metadata'=>'array'];}
    public function assetCategory():BelongsTo{return $this->belongsTo(AssetCategory::class);}
    public function views():HasMany{return $this->hasMany(InspectionDiagramView::class,'template_id')->orderBy('display_order');}
}
