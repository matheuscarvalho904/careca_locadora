<?php

$root = dirname(__DIR__);

$files = [
    $root . '/app/Filament/Resources/PurchaseOrders/PurchaseOrderResource.php',
    $root . '/app/Filament/Resources/ServiceOrders/ServiceOrderResource.php',
];

foreach ($files as $file) {
    $content = file_get_contents($file);

    $old = <<<'PHP'
->orderBy('display_name')
                                ->limit(100)
                                ->pluck('display_name', 'id')
                                ->all()
PHP;

    $new = <<<'PHP'
->orderBy('legal_name')
                                ->limit(100)
                                ->get()
                                ->mapWithKeys(fn ($partner): array => [
                                    $partner->id => $partner->display_name,
                                ])
                                ->all()
PHP;

    $content = str_replace($old, $new, $content);

    $oldPayment = <<<'PHP'
TextInput::make('payment_condition')
                            ->label('Condição de pagamento')
                            ->placeholder('Ex.: 30/60, 28 dias, entrada + 30')
                            ->maxLength(120),
PHP;

    $newPayment = <<<'PHP'
Select::make('payment_condition_id')
                            ->label('Condição de pagamento')
                            ->relationship('paymentCondition', 'name')
                            ->searchable(['code', 'name'])
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn ($record): string =>
                                    "{$record->code} - {$record->name}"
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $condition = \App\Models\PaymentCondition::find($state);

                                if (! $condition) {
                                    return;
                                }

                                $set('installments', $condition->installments);
                                $set('installment_interval_days', $condition->interval_days);
                                $set(
                                    'first_due_date',
                                    now()->addDays($condition->first_due_days)->toDateString()
                                );
                            }),
PHP;

    $content = str_replace($oldPayment, $newPayment, $content);

    $content = str_replace(
        "TextColumn::make('payment_condition')->label('Condição')->placeholder('—'),",
        "TextColumn::make('paymentCondition.name')->label('Condição')->placeholder('—'),",
        $content
    );

    $statusColumn = "TextColumn::make('status')->label('Status')->badge(),";

    $purchaseStatus = <<<'PHP'
TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'awaiting_approval' => 'Aguardando aprovação',
                    'approved' => 'Aprovada',
                    'sent' => 'Enviada',
                    'partially_received' => 'Recebida parcialmente',
                    'received' => 'Recebida',
                    'finished' => 'Finalizada',
                    'cancelled' => 'Cancelada',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'awaiting_approval' => 'warning',
                    'approved', 'received', 'finished' => 'success',
                    'sent', 'partially_received' => 'info',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
PHP;

    $serviceStatus = <<<'PHP'
TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'draft' => 'Rascunho',
                    'awaiting_approval' => 'Aguardando aprovação',
                    'approved' => 'Aprovada',
                    'in_execution' => 'Em execução',
                    'completed' => 'Concluída',
                    'cancelled' => 'Cancelada',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'awaiting_approval' => 'warning',
                    'approved', 'completed' => 'success',
                    'in_execution' => 'info',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
PHP;

    $content = str_replace(
        $statusColumn,
        str_contains($file, 'PurchaseOrders')
            ? $purchaseStatus
            : $serviceStatus,
        $content
    );

    file_put_contents($file, $content);
}
