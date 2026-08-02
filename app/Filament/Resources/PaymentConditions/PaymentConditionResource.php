<?php

namespace App\Filament\Resources\PaymentConditions;

use App\Filament\Resources\PaymentConditions\Pages\CreatePaymentCondition;
use App\Filament\Resources\PaymentConditions\Pages\EditPaymentCondition;
use App\Filament\Resources\PaymentConditions\Pages\ListPaymentConditions;
use App\Models\PaymentCondition;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PaymentConditionResource extends Resource
{
    protected static ?string $model = PaymentCondition::class;
    protected static ?string $modelLabel = 'condição de pagamento';
    protected static ?string $pluralModelLabel = 'condições de pagamento';
    protected static ?string $navigationLabel = 'Condições de pagamento';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Condição de pagamento')->columns(3)->schema([
                TextInput::make('code')->label('Código')->required(),
                TextInput::make('name')->label('Nome')->required(),
                TextInput::make('installments')->label('Parcelas')->numeric()->minValue(1)->default(1),
                TextInput::make('first_due_days')->label('Primeiro vencimento')->numeric()->suffix('dias')->default(0),
                TextInput::make('interval_days')->label('Intervalo')->numeric()->suffix('dias')->default(30),
                Toggle::make('requires_down_payment')->label('Exige entrada'),
                TextInput::make('down_payment_percent')->label('Percentual de entrada')->numeric()->suffix('%')->default(0),
                Select::make('status')->label('Status')->options([
                    'active' => 'Ativa',
                    'inactive' => 'Inativa',
                ])->default('active')->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable(),
            TextColumn::make('name')->label('Condição')->searchable(),
            TextColumn::make('installments')->label('Parcelas'),
            TextColumn::make('first_due_days')->label('1º vencimento')->suffix(' dias'),
            TextColumn::make('interval_days')->label('Intervalo')->suffix(' dias'),
            IconColumn::make('requires_down_payment')->label('Entrada')->boolean(),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativa' : 'Inativa')
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentConditions::route('/'),
            'create' => CreatePaymentCondition::route('/create'),
            'edit' => EditPaymentCondition::route('/{record}/edit'),
        ];
    }
}
