<?php

namespace App\Filament\Resources\AccountPayables\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class AccountPayablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('supplier.display_name')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('purchaseOrder.number')
                    ->label('OC')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('purchaseReceipt.number')
                    ->label('Recebimento')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('installment_number')
                    ->label('Parcela')
                    ->formatStateUsing(
                        fn (mixed $state, $record): string =>
                            filled($state) && filled($record->installment_count)
                                ? "{$state}/{$record->installment_count}"
                                : '—'
                    )
                    ->alignCenter(),

                TextColumn::make('due_at')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(
                        fn ($record): string =>
                            $record->due_at?->isPast()
                            && ! in_array($record->status, ['paid', 'cancelled'], true)
                                ? 'danger'
                                : 'gray'
                    ),

                TextColumn::make('original_value')
                    ->label('Original')
                    ->money('BRL')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('paid_value')
                    ->label('Pago')
                    ->money('BRL')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('open_value')
                    ->label('Em aberto')
                    ->money('BRL')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('bankAccount.display_name')
                    ->label('Conta do fornecedor')
                    ->placeholder('Não informada')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'draft' => 'Rascunho',
                            'awaiting_approval' => 'Aguardando aprovação',
                            'approved' => 'Aprovado',
                            'overdue' => 'Vencido',
                            'partially_paid' => 'Parcialmente pago',
                            'paid' => 'Pago',
                            'rejected' => 'Reprovado',
                            'cancelled' => 'Cancelado',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'draft' => 'gray',
                            'awaiting_approval' => 'warning',
                            'approved' => 'info',
                            'overdue' => 'danger',
                            'partially_paid' => 'warning',
                            'paid' => 'success',
                            'rejected' => 'danger',
                            'cancelled' => 'gray',
                            default => 'gray',
                        }
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'draft' => 'Rascunho',
                        'awaiting_approval' => 'Aguardando aprovação',
                        'approved' => 'Aprovado',
                        'overdue' => 'Vencido',
                        'partially_paid' => 'Parcialmente pago',
                        'paid' => 'Pago',
                        'rejected' => 'Reprovado',
                        'cancelled' => 'Cancelado',
                    ]),

                SelectFilter::make('supplier_id')
                    ->label('Fornecedor')
                    ->relationship('supplier', 'trade_name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('purchase_order_id')
                    ->label('Ordem de Compra')
                    ->relationship('purchaseOrder', 'number')
                    ->searchable()
                    ->preload(),

                Filter::make('due_period')
                    ->label('Período de vencimento')
                    ->schema([
                        DatePicker::make('due_from')
                            ->label('Vencimento inicial'),
                        DatePicker::make('due_until')
                            ->label('Vencimento final'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'] ?? null,
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('due_at', '>=', $date)
                            )
                            ->when(
                                $data['due_until'] ?? null,
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('due_at', '<=', $date)
                            );
                    }),

                Filter::make('overdue')
                    ->label('Somente vencidas')
                    ->query(
                        fn (Builder $query): Builder =>
                            $query
                                ->whereDate('due_at', '<', today())
                                ->whereNotIn('status', ['paid', 'cancelled'])
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Abrir'),
            ])
            ->defaultSort('due_at')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
