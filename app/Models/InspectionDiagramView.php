<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InspectionDiagramView extends Model {
    use HasUuids;
    protected $guarded=[];
    protected function casts():array{return ['is_active'=>'boolean','metadata'=>'array'];}
    public function template():BelongsTo{return $this->belongsTo(InspectionDiagramTemplate::class,'template_id');}
}
