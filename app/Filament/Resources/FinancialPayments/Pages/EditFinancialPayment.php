<?php
namespace App\Filament\Resources\FinancialPayments\Pages;
use App\Filament\Resources\FinancialPayments\FinancialPaymentResource;
use App\Services\Finance\PaymentService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
class EditFinancialPayment extends EditRecord
{
    protected static string $resource=FinancialPaymentResource::class;
    protected function getHeaderActions(): array
    {
        return [Action::make('reverse')->label('Estornar pagamento')->color('danger')->requiresConfirmation()->visible(fn()=>$this->record->status==='confirmed')->schema([Textarea::make('reason')->label('Motivo do estorno')->required()])->action(function(array $data){ app(PaymentService::class)->reverse($this->record,$data['reason']); Notification::make()->success()->title('Pagamento estornado')->send(); $this->redirect(FinancialPaymentResource::getUrl('edit',['record'=>$this->record])); })];
    }
}
