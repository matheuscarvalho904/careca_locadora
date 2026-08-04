<?php

$file = dirname(__DIR__) . '/app/Filament/Resources/BusinessPartners/Schemas/BusinessPartnerForm.php';
$content = file_get_contents($file);

$replacements = [
    'IdentificaÃ§Ã£o' => 'Identificação',
    'classificaÃ§Ã£o' => 'classificação',
    'papÃ©is' => 'papéis',
    'CÃ³digo' => 'Código',
    'jurÃ­dica' => 'jurídica',
    'fÃ­sica' => 'física',
    'RazÃ£o' => 'Razão',
    'DocumentaÃ§Ã£o' => 'Documentação',
    'inscriÃ§Ãµes' => 'inscrições',
    'InscriÃ§Ã£o' => 'Inscrição',
    'SituaÃ§Ã£o' => 'Situação',
    'crÃ©dito' => 'crédito',
    'condiÃ§Ã£o' => 'condição',
    'CondiÃ§Ã£o' => 'Condição',
    'EndereÃ§os' => 'Endereços',
    'EndereÃ§o' => 'Endereço',
    'CobranÃ§a' => 'Cobrança',
    'CorrespondÃªncia' => 'Correspondência',
    'IdentificaÃ§Ã£o' => 'Identificação',
    'NÃºmero' => 'Número',
    'ResponsÃ¡veis' => 'Responsáveis',
    'funÃ§Ã£o' => 'função',
    'ObservaÃ§Ãµes' => 'Observações',
    'InformaÃ§Ãµes' => 'Informações',
    'MÃ©dio' => 'Médio',
    'AmapÃ¡' => 'Amapá',
    'CearÃ¡' => 'Ceará',
    'EspÃ­rito' => 'Espírito',
    'GoiÃ¡s' => 'Goiás',
    'MaranhÃ£o' => 'Maranhão',
    'ParÃ¡' => 'Pará',
    'ParaÃ­ba' => 'Paraíba',
    'PiauÃ­' => 'Piauí',
    'RondÃ´nia' => 'Rondônia',
    'SÃ£o' => 'São',
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

if (! str_contains($content, "Repeater::make('bankAccounts')")) {
    $marker = "                Section::make('6. Informações adicionais')";
    $section = <<<'PHP'
                Section::make('6. Dados bancários')
                    ->description('Contas para pagamentos ao fornecedor ou prestador de serviços.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('bankAccounts')
                            ->label('')
                            ->relationship()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                $state['description']
                                ?? $state['pix_key']
                                ?? 'Conta bancária'
                            )
                            ->columns(PremiumFormLayout::repeater())
                            ->schema([
                                Hidden::make('owner_type')->default('business_partner'),

                                Select::make('bank_id')
                                    ->label('Banco')
                                    ->relationship('bank', 'name')
                                    ->searchable(['code', 'name', 'short_name'])
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record): string => "{$record->code} - {$record->name}"
                                    )
                                    ->required(),

                                TextInput::make('description')
                                    ->label('Identificação')
                                    ->maxLength(120),

                                TextInput::make('agency')->label('Agência')->maxLength(30),
                                TextInput::make('agency_digit')->label('Dígito da agência')->maxLength(10),
                                TextInput::make('account_number')->label('Conta')->maxLength(40),
                                TextInput::make('account_digit')->label('Dígito da conta')->maxLength(10),

                                Select::make('account_type')
                                    ->label('Tipo de conta')
                                    ->options([
                                        'checking'=>'Conta corrente',
                                        'savings'=>'Conta poupança',
                                        'payment'=>'Conta de pagamento',
                                        'other'=>'Outra',
                                    ])
                                    ->default('checking')
                                    ->required(),

                                TextInput::make('holder_name')->label('Titular')->required()->maxLength(180),
                                TextInput::make('holder_document')->label('CPF/CNPJ do titular')->maxLength(20),

                                Select::make('pix_key_type')
                                    ->label('Tipo da chave PIX')
                                    ->options([
                                        'document'=>'CPF/CNPJ',
                                        'email'=>'E-mail',
                                        'phone'=>'Telefone',
                                        'random'=>'Chave aleatória',
                                    ]),

                                TextInput::make('pix_key')->label('Chave PIX')->maxLength(255)->columnSpan(2),
                                Toggle::make('is_primary')->label('Conta principal'),
                                Toggle::make('use_for_payments')->label('Usar para pagamentos')->default(true),
                                Select::make('status')->label('Status')->options([
                                    'active'=>'Ativa',
                                    'inactive'=>'Inativa',
                                ])->default('active')->required(),
                            ])
                            ->columnSpanFull(),
                    ]),

PHP;
    $content = str_replace($marker, $section . "                Section::make('7. Informações adicionais')", $content);
}

file_put_contents($file, $content);
