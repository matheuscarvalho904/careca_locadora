<?php
namespace App\Console\Commands;
use App\Models\PurchaseReceipt;
use App\Services\Finance\PurchaseReceiptPayableService;
use Illuminate\Console\Command;
use Throwable;

class GenerateMissingPurchaseReceiptPayables extends Command {
 protected $signature='finance:generate-missing-purchase-payables {--receipt=}';
 protected $description='Gera Contas a Pagar para recebimentos confirmados sem títulos financeiros';

 public function handle(PurchaseReceiptPayableService $service):int{
  $query=PurchaseReceipt::query()->where('status','confirmed')->whereDoesntHave('accountsPayable');
  if(filled($this->option('receipt'))){$query->where('number',$this->option('receipt'));}
  $generated=0;$failed=0;
  $query->chunkById(100,function($receipts)use($service,&$generated,&$failed):void{
   foreach($receipts as $receipt){
    try{$generated+=$service->generate($receipt)->count();}
    catch(Throwable $exception){$failed++;$this->warn("{$receipt->number}: {$exception->getMessage()}");}
   }
  });
  $this->info("{$generated} título(s) gerado(s).");
  if($failed>0){$this->warn("{$failed} recebimento(s) não puderam ser processados.");}
  return self::SUCCESS;
 }
}
