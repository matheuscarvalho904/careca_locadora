<?php
namespace App\Filament\Resources\PurchaseReceipts\Pages;
use App\Filament\Resources\AccountPayables\AccountPayableResource;
use App\Filament\Resources\PurchaseReceipts\PurchaseReceiptResource;
use App\Services\Finance\PurchaseReceiptPayableService;
use App\Services\Procurement\PurchaseReceiptService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditPurchaseReceipt extends EditRecord {
 protected static string $resource=PurchaseReceiptResource::class;

 protected function getHeaderActions():array{
  return [
   Action::make('confirm')->label('Confirmar recebimento')->icon('heroicon-o-check-circle')
    ->color('success')->requiresConfirmation()
    ->visible(fn():bool=>$this->record->status==='draft')
    ->action(function():void{
     try{
      $receipt=app(PurchaseReceiptService::class)->confirm($this->record);
      Notification::make()->success()->title('Recebimento confirmado')
       ->body("Estoque atualizado e {$receipt->accountsPayable()->count()} título(s) financeiro(s) gerado(s).")->send();
      $this->redirect(PurchaseReceiptResource::getUrl('edit',['record'=>$this->record]));
     }catch(Throwable $exception){
      Notification::make()->danger()->title('Não foi possível confirmar')->body($exception->getMessage())->send();
     }
    }),

   Action::make('generate_payables')->label('Gerar títulos financeiros')->icon('heroicon-o-banknotes')
    ->color('warning')->requiresConfirmation()
    ->visible(fn():bool=>$this->record->status==='confirmed'&&$this->record->accountsPayable()->doesntExist())
    ->action(function():void{
     try{
      $titles=app(PurchaseReceiptPayableService::class)->generate($this->record);
      Notification::make()->success()->title('Títulos gerados')->body("Foram gerados {$titles->count()} título(s).")->send();
     }catch(Throwable $exception){
      Notification::make()->danger()->title('Não foi possível gerar os títulos')->body($exception->getMessage())->send();
     }
    }),

   Action::make('open_payables')->label('Abrir Contas a Pagar')
    ->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
    ->visible(fn():bool=>$this->record->accountsPayable()->exists())
    ->url(fn():string=>AccountPayableResource::getUrl('index')),

   DeleteAction::make()->visible(fn():bool=>$this->record->status==='draft'),
  ];
 }
}
