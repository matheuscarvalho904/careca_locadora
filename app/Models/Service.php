<?php
namespace App\Models;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Service extends Model {
    use BelongsToOrganization, HasFactory, HasUuids, SoftDeletes;
    protected $guarded = [];
}
