<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class Bank extends Model{use HasFactory,HasUuids;protected $guarded=[];public function accounts():HasMany{return $this->hasMany(BankAccount::class);}}
