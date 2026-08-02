<?php
namespace App\Filament\Resources\AccountPayables\Schemas;

use App\Models\BusinessPartner;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountPayableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Conta a pagar')->columns(3)->schema([
                TextInput::make('number')->label('Número')->disabled()->dehydrated(false),
                Select::make('supplier_id')->label('Fornecedor')->required()->searchable()->options(fn():array=>BusinessPartner::query()->whereJsonContains('roles','supplier')->orderBy('trade_name')->get()->mapWithKeys(fn($p)=>[$p->id=>$p->display_name])->all()),
                TextInput::make('document_number')->label('Documento')->maxLength(80),
                DatePicker::make('issued_at')->label('Emissão'),
                DatePicker::make('competence_date')->label('Competência'),
                DatePicker::make('due_at')->label('Vencimento')->required(),
                TextInput::make('original_value')->label('Valor original')->numeric()->prefix('R$')->required()->default(0),
                TextInput::make('interest_value')->label('Juros')->numeric()->prefix('R$')->default(0),
                TextInput::make('penalty_value')->label('Multa')->numeric()->prefix('R$')->default(0),
                TextInput::make('discount_value')->label('Desconto')->numeric()->prefix('R$')->default(0),
                Select::make('status')->label('Status')->options(['draft'=>'Rascunho','awaiting_approval'=>'Aguardando aprovação','approved'=>'Aprovado','rejected'=>'Reprovado','overdue'=>'Vencido','partially_paid'=>'Parcialmente pago','paid'=>'Pago','cancelled'=>'Cancelado'])->default('draft'),
                FileUpload::make('attachment_path')->label('Anexo')->directory('accounts-payable/attachments')->visibility('private'),
                Textarea::make('notes')->label('Observações')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }
}
