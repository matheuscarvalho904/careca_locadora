<?php
namespace App\Filament\Resources\FinancialPayments\Tables;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class FinancialPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('number')->label('Pagamento')->searchable()->weight('bold'),
            TextColumn::make('payable.number')->label('Conta'),
            TextColumn::make('supplier.display_name')->label('Fornecedor')->wrap(),
            TextColumn::make('paid_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
            TextColumn::make('payment_method')->label('Forma')->badge(),
            TextColumn::make('total_paid')->label('Valor')->money('BRL'),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(string $s)=>$s==='confirmed'?'Confirmado':'Estornado'),
        ])->recordActions([EditAction::make()->label('Abrir')])->defaultSort('paid_at','desc');
    }
}
