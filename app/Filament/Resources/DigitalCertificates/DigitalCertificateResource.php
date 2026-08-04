<?php

namespace App\Filament\Resources\DigitalCertificates;

use App\Filament\Resources\DigitalCertificates\Pages\CreateDigitalCertificate;
use App\Filament\Resources\DigitalCertificates\Pages\EditDigitalCertificate;
use App\Filament\Resources\DigitalCertificates\Pages\ListDigitalCertificates;
use App\Models\DigitalCertificate;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DigitalCertificateResource extends Resource
{
    protected static ?string $model = DigitalCertificate::class;
    protected static ?string $modelLabel = 'certificado digital';
    protected static ?string $pluralModelLabel = 'certificados digitais';
    protected static ?string $navigationLabel = 'Certificados digitais';
    protected static string | UnitEnum | null $navigationGroup = 'Administração';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Certificado digital')->columns(3)->schema([
                Select::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'legal_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')->label('Identificação')->required()->maxLength(150),

                Select::make('certificate_type')
                    ->label('Tipo')
                    ->options([
                        'A1' => 'A1 - Arquivo digital',
                        'A3' => 'A3 - Token ou cartão',
                    ])
                    ->default('A1')
                    ->required()
                    ->live(),

                Select::make('environment')->label('Ambiente')->options([
                    'homologation' => 'Homologação',
                    'production' => 'Produção',
                ])->default('production')->required(),

                Select::make('purposes')->label('Finalidades')->multiple()->options([
                    'nfe' => 'NF-e',
                    'nfse' => 'NFS-e',
                    'cte' => 'CT-e',
                    'mdfe' => 'MDF-e',
                    'document_signing' => 'Assinatura de documentos',
                ])->preload(),

                FileUpload::make('file_path')
                    ->label('Arquivo A1 (.pfx ou .p12)')
                    ->disk('local')
                    ->directory('private/certificates')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/x-pkcs12',
                        'application/octet-stream',
                    ])
                    ->visible(fn (Get $get): bool => $get('certificate_type') === 'A1'),

                TextInput::make('certificate_password')
                    ->label('Senha do certificado')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->visible(fn (Get $get): bool => $get('certificate_type') === 'A1'),

                TextInput::make('serial_number')->label('Número de série')->maxLength(180),
                TextInput::make('subject_name')->label('Titular')->maxLength(255),
                TextInput::make('subject_document')->label('CPF/CNPJ do titular')->maxLength(20),
                TextInput::make('issuer_name')->label('Autoridade certificadora')->maxLength(255),
                DatePicker::make('issued_at')->label('Emissão')->native(false)->displayFormat('d/m/Y'),
                DatePicker::make('expires_at')->label('Validade')->native(false)->displayFormat('d/m/Y'),
                TextInput::make('alert_days_before')->label('Alertar antes')->numeric()->suffix('dias')->default(30),
                Toggle::make('is_primary')->label('Certificado principal'),
                Select::make('status')->label('Status')->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'expired' => 'Vencido',
                    'revoked' => 'Revogado',
                ])->default('active')->required(),
                Textarea::make('notes')->label('Observações')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('company.legal_name')->label('Empresa')->searchable(),
            TextColumn::make('name')->label('Certificado')->searchable(),
            TextColumn::make('certificate_type')->label('Tipo')->badge(),
            TextColumn::make('environment')->label('Ambiente')->badge()
                ->formatStateUsing(fn (string $state): string => $state === 'production' ? 'Produção' : 'Homologação'),
            TextColumn::make('expires_at')->label('Validade')->date('d/m/Y')->placeholder('—'),
            IconColumn::make('is_primary')->label('Principal')->boolean(),
            TextColumn::make('status')->label('Status')->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'expired' => 'Vencido',
                    'revoked' => 'Revogado',
                    default => $state,
                }),
        ])->recordActions([
            EditAction::make()->label('Abrir'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalCertificates::route('/'),
            'create' => CreateDigitalCertificate::route('/create'),
            'edit' => EditDigitalCertificate::route('/{record}/edit'),
        ];
    }
}
