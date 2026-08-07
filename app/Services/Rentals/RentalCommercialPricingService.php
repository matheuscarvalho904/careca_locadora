<?php
namespace App\Services\Rentals;
use App\Data\Rentals\ReservationSearch; use App\Domain\Reservations\ReservationPricingEngine; use App\Models\RentalCommercialItem; use App\Models\RentalCommercialRule; use App\Models\RentalCoupon; use App\Models\RentalRatePlan; use Illuminate\Database\Eloquent\Builder; use Illuminate\Validation\ValidationException;
final class RentalCommercialPricingService {
 public function __construct(private readonly ReservationPricingEngine $pricing){}
 public function quote(ReservationSearch $search,array $itemIds=[],?string $couponCode=null):array{
  $rate=$this->rate($search); if(!$rate) throw ValidationException::withMessages(['rate_plan'=>'Nenhuma tarifa comercial valida foi encontrada.']);
  $base=$this->pricing->quote($search->startsAt,$search->endsAt,$rate->billing_unit,(float)$rate->unit_value,depositValue:(float)$rate->deposit_value);
  $items=RentalCommercialItem::query()
            ->withoutGlobalScopes()->where('organization_id',$search->organizationId)->where('status','active')->where(fn(Builder $q)=>$q->where('required',true)->when($itemIds!==[],fn(Builder $q)=>$q->orWhereIn('id',$itemIds)))->get()->map(function($i)use($base){$factor=in_array($i->charge_mode,['per_day','per_hour','per_period'],true)?$base->quantity:1;return ['id'=>$i->id,'name'=>$i->name,'type'=>$i->type,'total'=>round((float)$i->value*$factor,2),'deposit_adjustment'=>(float)$i->deposit_adjustment];});
  $gross=round($base->subtotal+$items->sum('total'),2); $coupon=$this->coupon($search,$couponCode,$gross); $discount=$coupon?$this->discount($coupon,$gross):0;
  return ['rate_plan'=>['id'=>$rate->id,'code'=>$rate->code,'name'=>$rate->name,'billing_unit'=>$rate->billing_unit,'unit_value'=>(float)$rate->unit_value,'deposit_value'=>(float)$rate->deposit_value],'base'=>$base->toArray(),'commercial_items'=>$items->values()->all(),'gross_total'=>$gross,'coupon_discount'=>$discount,'deposit_value'=>round((float)$rate->deposit_value+$items->sum('deposit_adjustment'),2),'total_value'=>round(max(0,$gross-$discount),2),'commercial_rules'=>RentalCommercialRule::query()
            ->withoutGlobalScopes()->where('organization_id',$search->organizationId)->where('status','active')->orderBy('priority')->get()->toArray()];
 }
 private function rate(ReservationSearch $s):?RentalRatePlan{return RentalRatePlan::query()
            ->withoutGlobalScopes()->where('organization_id',$s->organizationId)->where('status','active')->where(fn(Builder $q)=>$q->whereNull('branch_id')->orWhere('branch_id',$s->branchId))->where(fn(Builder $q)=>$q->whereNull('asset_category_id')->orWhere('asset_category_id',$s->categoryId))->orderBy('priority')->first();}
 private function coupon(ReservationSearch $s,?string $code,float $gross):?RentalCoupon{if(blank($code))return null;$c=RentalCoupon::query()
            ->withoutGlobalScopes()->where('organization_id',$s->organizationId)->whereRaw('upper(code) = ?',[mb_strtoupper($code)])->where('status','active')->first();if(!$c)throw ValidationException::withMessages(['coupon'=>'Cupom invalido ou inativo.']);if($c->valid_from&&now()->lessThan($c->valid_from)||$c->valid_until&&now()->greaterThan($c->valid_until)||$gross<(float)$c->minimum_order_value)throw ValidationException::withMessages(['coupon'=>'Cupom fora das regras de uso.']);return $c;}
 private function discount(RentalCoupon $c,float $gross):float{$d=$c->discount_type==='fixed'?(float)$c->discount_value:$gross*((float)$c->discount_value/100);if($c->maximum_discount_value!==null)$d=min($d,(float)$c->maximum_discount_value);return round(min($gross,max(0,$d)),2);}
}
