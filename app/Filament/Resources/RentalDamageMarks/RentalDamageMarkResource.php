<?php
namespace App\Filament\Resources\RentalDamageMarks;
use App\Filament\Resources\RentalDamageMarks\Pages\ListRentalDamageMarks;
use App\Models\RentalDamageMark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RentalDamageMarkResource extends Resource {
    protected static ?string $model=RentalDamageMark::class;
    protected static ?string $modelLabel='avaria';
    protected static ?string $pluralModelLabel='mapa de avarias';
    protected static ?string $navigationLabel='Mapa de avarias';
    protected static string|UnitEnum|null $navigationGroup='Locações';
    protected static string|BackedEnum|null $navigationIcon='heroicon-o-map';
    protected static ?int $navigationSort=5;

    public static function table(Table $table):Table {
        return $table->columns([
            TextColumn::make('asset.prefix')->label('Prefixo')->searchable(),
            TextColumn::make('asset.name')->label('Ativo')->searchable()->wrap(),
            TextColumn::make('templateView.name')->label('Vista'),
            TextColumn::make('vehicle_part')->label('Parte')->placeholder('—'),
            TextColumn::make('damage_type')->label('Avaria')->badge(),
            TextColumn::make('severity')->label('Gravidade')->badge(),
            TextColumn::make('condition')->label('Condição')->badge(),
            TextColumn::make('estimated_value')->label('Valor estimado')->money('BRL'),
            TextColumn::make('created_at')->label('Registrada em')->dateTime('d/m/Y H:i'),
        ])->filters([
            SelectFilter::make('condition')->label('Condição')->options([
                'preexisting'=>'Preexistente','new'=>'Nova','aggravated'=>'Agravada','repaired'=>'Reparada',
            ]),
            SelectFilter::make('severity')->label('Gravidade')->options([
                'light'=>'Leve','medium'=>'Média','serious'=>'Grave','critical'=>'Crítica',
            ]),
        ])->defaultSort('created_at','desc');
    }

    public static function getPages():array{return ['index'=>ListRentalDamageMarks::route('/')];}
}
