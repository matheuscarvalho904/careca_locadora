<?php
namespace App\Filament\Resources\AccountPayables\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Título')->searchable()->weight('bold'),
            TextColumn::make('supplier.display_name')->label('Fornecedor')->wrap(),
            TextColumn::make('document_number')->label('Documento')->placeholder('—'),
            TextColumn::make('due_at')->label('Vencimento')->date('d/m/Y')->sortable(),
            TextColumn::make('original_value')->label('Original')->money('BRL'),
            TextColumn::make('open_value')->label('Em aberto')->money('BRL'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>match($s){'draft'=>'Rascunho','awaiting_approval'=>'Aguardando aprovação','approved'=>'Aprovado','overdue'=>'Vencido','partially_paid'=>'Parcialmente pago','paid'=>'Pago','cancelled'=>'Cancelado',default=>$s}),
        ])->filters([SelectFilter::make('status')->options(['draft'=>'Rascunho','approved'=>'Aprovado','overdue'=>'Vencido','partially_paid'=>'Parcialmente pago','paid'=>'Pago'])])->recordActions([EditAction::make()->label('Abrir')])->defaultSort('due_at');
    }
}
