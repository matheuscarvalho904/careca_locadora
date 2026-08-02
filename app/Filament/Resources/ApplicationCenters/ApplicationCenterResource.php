<?php

namespace App\Filament\Resources\ApplicationCenters;

use App\Filament\Resources\ApplicationCenters\Pages\CreateApplicationCenter;
use App\Filament\Resources\ApplicationCenters\Pages\EditApplicationCenter;
use App\Filament\Resources\ApplicationCenters\Pages\ListApplicationCenters;
use App\Models\ApplicationCenter;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ApplicationCenterResource extends Resource
{
    protected static ?string $model = ApplicationCenter::class;
    protected static ?string $modelLabel = 'centro de aplicação';
    protected static ?string $pluralModelLabel = 'centros de aplicação';
    protected static ?string $navigationLabel = 'Centros de aplicação';
    protected static string | UnitEnum | null $navigationGroup = 'Compras e Serviços';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Centro de aplicação')->columns(3)->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(40),
                TextInput::make('name')->label('Nome')->required()->maxLength(150),
                Select::make('type')->label('Tipo')->options([
                    'building' => 'Despesa predial',
                    'administrative' => 'Administrativo',
                    'office' => 'Escritório',
                    'yard' => 'Pátio',
                    'workshop' => 'Oficina',
                    'work' => 'Obra',
                    'branch' => 'Filial',
                    'general' => 'Geral',
                ])->default('general')->required(),
                Select::make('company_id')->label('Empresa')->relationship('company', 'legal_name')->searchable(),
                Select::make('branch_id')->label('Filial')->relationship('branch', 'legal_name')->searchable(),
                Select::make('cost_center_id')->label('Centro de custo')->relationship('costCenter', 'name')->searchable(),
                Select::make('department_id')->label('Departamento')->relationship('department', 'name')->searchable(),
                Select::make('status')->label('Status')->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                ])->default('active')->required(),
                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable(),
            TextColumn::make('name')->label('Nome')->searchable()->sortable(),
            TextColumn::make('type')->label('Tipo')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'building' => 'Despesa predial',
                    'administrative' => 'Administrativo',
                    'office' => 'Escritório',
                    'yard' => 'Pátio',
                    'workshop' => 'Oficina',
                    'work' => 'Obra',
                    'branch' => 'Filial',
                    default => 'Geral',
                }),
            TextColumn::make('costCenter.name')->label('Centro de custo')->placeholder('—'),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Ativo' : 'Inativo')
                ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplicationCenters::route('/'),
            'create' => CreateApplicationCenter::route('/create'),
            'edit' => EditApplicationCenter::route('/{record}/edit'),
        ];
    }
}
