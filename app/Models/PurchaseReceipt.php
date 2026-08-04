<?php
namespace App\Models;
use App\Services\Numbering\NumberSequenceService;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model {
 use BelongsToOrganization,HasFactory,HasUuids,SoftDeletes;
 protected $guarded=[];
 protected $attributes=['status'=>'draft','subtotal'=>0,'discount_value'=>0,'freight_value'=>0,'additional_value'=>0,'total_value'=>0];

 protected static function booted():void{
  static::creating(function(self $receipt):void{
   $receipt->organization_id??=auth()->user()?->organization_id;
   $receipt->received_by??=auth()->id();
   $receipt->received_at??=now();
   if(blank($receipt->number)&&filled($receipt->organization_id)){
    $receipt->number=app(NumberSequenceService::class)->next(
     organizationId:$receipt->organization_id,key:'purchase_receipt',
     name:'Recebimentos de mercadorias',prefix:'REC-',padding:8
    );
   }
  });
 }

 protected function casts():array{
  return ['received_at'=>'datetime','invoice_issued_at'=>'date','confirmed_at'=>'datetime',
   'subtotal'=>'decimal:2','discount_value'=>'decimal:2','freight_value'=>'decimal:2',
   'additional_value'=>'decimal:2','total_value'=>'decimal:2'];
 }

 public function purchaseOrder():BelongsTo{return $this->belongsTo(PurchaseOrder::class);}
 public function supplier():BelongsTo{return $this->belongsTo(BusinessPartner::class,'supplier_id');}
 public function warehouse():BelongsTo{return $this->belongsTo(Warehouse::class);}
 public function receiver():BelongsTo{return $this->belongsTo(User::class,'received_by');}
 public function items():HasMany{return $this->hasMany(PurchaseReceiptItem::class);}
 public function accountsPayable():HasMany{return $this->hasMany(AccountPayable::class);}
}
