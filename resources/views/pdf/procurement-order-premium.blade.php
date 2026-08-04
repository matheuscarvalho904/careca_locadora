<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>{{ $documentTitle }} {{ $order->number }}</title>
@php
    $isPurchase = $documentType === 'purchase_order';

    $companyName = data_get($company, 'trade_name')
        ?: data_get($company, 'legal_name')
        ?: data_get($company, 'name')
        ?: 'CARECA LOCADORA DE VEÍCULOS';

    $companyDocument = data_get($company, 'document')
        ?: data_get($company, 'tax_id')
        ?: data_get($company, 'cnpj')
        ?: '-';

    $branchName = data_get($branch, 'trade_name')
        ?: data_get($branch, 'name')
        ?: 'Matriz';

    $supplierName = data_get($supplier, 'legal_name')
        ?: data_get($supplier, 'trade_name')
        ?: data_get($supplier, 'display_name')
        ?: '-';

    $supplierTrade = data_get($supplier, 'trade_name')
        ?: data_get($supplier, 'display_name')
        ?: '-';

    $supplierDocument = data_get($supplier, 'document')
        ?: data_get($supplier, 'tax_id')
        ?: data_get($supplier, 'cnpj')
        ?: data_get($supplier, 'cpf')
        ?: '-';

    $supplierPhone = data_get($supplierContact, 'phone')
        ?: data_get($supplierContact, 'mobile')
        ?: data_get($supplier, 'phone')
        ?: data_get($supplier, 'mobile')
        ?: '-';

    $supplierEmail = data_get($supplierContact, 'email')
        ?: data_get($supplier, 'email')
        ?: '-';

    $addressLine = collect([
        data_get($supplierAddress, 'street'),
        data_get($supplierAddress, 'number'),
        data_get($supplierAddress, 'complement'),
        data_get($supplierAddress, 'district'),
    ])->filter()->implode(', ');

    $cityLine = collect([
        data_get($supplierAddress, 'city'),
        data_get($supplierAddress, 'state'),
        data_get($supplierAddress, 'postal_code')
            ?: data_get($supplierAddress, 'zip_code'),
    ])->filter()->implode(' - ');
@endphp
<style>
    @page { margin: 18px 22px 28px; }
    body { font-family: DejaVu Sans, sans-serif; color:#1f2937; font-size:8.7px; }
    .header { border-bottom:3px solid #c41e2a; padding-bottom:9px; margin-bottom:10px; }
    .header-table,.clean { width:100%; border-collapse:collapse; }
    .header-table td,.clean td { border:0; padding:0; }
    .logo { width:145px; max-height:55px; object-fit:contain; }
    .brand { font-size:16px; font-weight:bold; color:#14181c; }
    .doc-title { font-size:19px; font-weight:bold; text-align:right; color:#14181c; }
    .doc-number { text-align:right; font-size:12px; margin-top:3px; }
    .status { display:inline-block; padding:4px 8px; border-radius:12px; font-weight:bold; font-size:8px; }
    .status-green { background:#dcfce7; color:#166534; }
    .status-blue { background:#dbeafe; color:#1d4ed8; }
    .status-amber { background:#fef3c7; color:#92400e; }
    .status-red { background:#fee2e2; color:#991b1b; }
    .status-gray { background:#e5e7eb; color:#374151; }
    .watermark { position:fixed; top:42%; left:9%; transform:rotate(-28deg); font-size:46px; color:rgba(107,114,128,.11); font-weight:bold; z-index:-1; }
    .box { border:1px solid #d1d5db; border-radius:5px; padding:7px; margin-bottom:8px; page-break-inside:avoid; }
    .section { font-size:10.5px; font-weight:bold; color:#111827; margin-bottom:5px; border-left:3px solid #c41e2a; padding-left:6px; }
    table.data { width:100%; border-collapse:collapse; }
    table.data th,table.data td { border:1px solid #d1d5db; padding:4px; vertical-align:top; }
    table.data th { background:#f3f4f6; text-align:left; font-weight:bold; }
    .items th { background:#14181c !important; color:#fff; }
    .right { text-align:right; }
    .center { text-align:center; }
    .muted { color:#6b7280; }
    .small { font-size:7.5px; }
    .application { line-height:1.4; }
    .total-box { width:48%; margin-left:auto; }
    .grand-total th,.grand-total td { background:#fff1f2 !important; color:#991b1b; font-size:11px; font-weight:bold; }
    .signatures { margin-top:20px; }
    .signature-line { height:45px; border-bottom:1px solid #374151; }
    .footer { position:fixed; left:22px; right:22px; bottom:9px; border-top:1px solid #d1d5db; padding-top:4px; color:#6b7280; font-size:6.8px; }
</style>
</head>
<body>
@if (in_array($order->status, ['draft', 'awaiting_approval'], true))
    <div class="watermark">{{ $statusLabel }}</div>
@endif

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width:42%">
                @if ($logo)
                    <img src="{{ $logo }}" class="logo">
                @else
                    <div class="brand">CARECA LOCADORA</div>
                @endif
                <div class="small muted">{{ $companyName }} · {{ $branchName }}</div>
            </td>
            <td>
                <div class="doc-title">{{ $documentTitle }}</div>
                <div class="doc-number">{{ $order->number }}</div>
                <div style="text-align:right;margin-top:5px">
                    <span class="status {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <div class="section">Dados do documento</div>
    <table class="data">
        <tr>
            <th>Empresa</th><td>{{ $companyName }}</td>
            <th>Filial</th><td>{{ $branchName }}</td>
        </tr>
        <tr>
            <th>CNPJ/CPF</th><td>{{ $companyDocument }}</td>
            <th>Emissão</th><td>{{ $order->issued_at?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Origem</th><td>{{ $originLabel }}</td>
            <th>{{ $isPurchase ? 'Previsão de entrega' : 'Previsão de execução' }}</th>
            <td>{{ $expectedDate?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        @if ($isPurchase)
            <tr>
                <th>Local de entrega</th>
                <td colspan="3">{{ $order->delivery_location ?: '-' }}</td>
            </tr>
        @endif
    </table>
</div>

<div class="box">
    <div class="section">{{ $isPurchase ? 'Fornecedor' : 'Prestador de serviço' }}</div>
    <table class="data">
        <tr>
            <th>Razão social</th><td>{{ $supplierName }}</td>
            <th>Nome fantasia</th><td>{{ $supplierTrade }}</td>
        </tr>
        <tr>
            <th>CNPJ/CPF</th><td>{{ $supplierDocument }}</td>
            <th>Telefone</th><td>{{ $supplierPhone }}</td>
        </tr>
        <tr>
            <th>E-mail</th><td>{{ $supplierEmail }}</td>
            <th>Endereço</th><td>{{ $addressLine ?: '-' }}</td>
        </tr>
        <tr>
            <th>Cidade/UF/CEP</th><td colspan="3">{{ $cityLine ?: '-' }}</td>
        </tr>
    </table>
</div>

<div class="box">
    <div class="section">{{ $isPurchase ? 'Produtos e aplicações' : 'Serviços e aplicações' }}</div>
    <table class="data items">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:11%">Código</th>
                <th>Descrição</th>
                <th style="width:23%">Aplicação</th>
                <th style="width:6%">Un.</th>
                <th style="width:8%" class="right">Qtd.</th>
                <th style="width:11%" class="right">Unitário</th>
                <th style="width:10%" class="right">Desconto</th>
                <th style="width:12%" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                @php
                    $assetName = collect([
                        data_get($item, 'asset.prefix'),
                        data_get($item, 'asset.name'),
                        data_get($item, 'asset.plate'),
                    ])->filter()->implode(' · ');

                    $application = match ($item->application_type) {
                        'asset' => $assetName ?: 'Ativo não informado',
                        'stock' => collect([
                            data_get($item, 'warehouse.code'),
                            data_get($item, 'warehouse.name'),
                        ])->filter()->implode(' - ') ?: 'Estoque',
                        'application_center' => collect([
                            data_get($item, 'applicationCenter.code'),
                            data_get($item, 'applicationCenter.name'),
                        ])->filter()->implode(' - ') ?: 'Centro de aplicação',
                        default => 'Consumo/aplicação direta',
                    };

                    $meter = $item->meter_reading !== null
                        ? number_format((float) $item->meter_reading, 2, ',', '.')
                            . ($item->meter_type === 'hourmeter' ? ' h' : ' km')
                        : null;

                    $description = $isPurchase
                        ? (data_get($item, 'product.name') ?: 'Produto')
                        : ($item->service_description ?: 'Serviço');

                    $code = $isPurchase
                        ? (data_get($item, 'product.code') ?: '-')
                        : ($item->service_code ?: '-');

                    $unit = $isPurchase
                        ? (data_get($item, 'product.unit.symbol')
                            ?: data_get($item, 'product.unit.code')
                            ?: 'UN')
                        : (data_get($item, 'unit.symbol')
                            ?: data_get($item, 'unit.code')
                            ?: 'UN');
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $code }}</td>
                    <td>
                        <strong>{{ $description }}</strong>
                        @if ($item->notes)
                            <div class="small muted">{{ $item->notes }}</div>
                        @endif
                    </td>
                    <td class="application">
                        {{ $application }}
                        @if ($meter)
                            <div class="small">Leitura: {{ $meter }}</div>
                        @endif
                        @if ($item->costCenter)
                            <div class="small">
                                CC: {{ data_get($item, 'costCenter.code') }}
                                {{ data_get($item, 'costCenter.name') }}
                            </div>
                        @endif
                    </td>
                    <td class="center">{{ $unit }}</td>
                    <td class="right">{{ number_format((float) $item->quantity, 4, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format((float) $item->unit_value, 4, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format((float) $item->discount_value, 2, ',', '.') }}</td>
                    <td class="right"><strong>R$ {{ number_format((float) $item->total_value, 2, ',', '.') }}</strong></td>
                </tr>
                @if (! $isPurchase && ($item->purpose || $item->financial_category || $item->economic_result))
                    <tr>
                        <td></td>
                        <td colspan="8" class="small">
                            @if ($item->purpose)<strong>Finalidade:</strong> {{ $item->purpose }} · @endif
                            @if ($item->financial_category)<strong>Categoria financeira:</strong> {{ $item->financial_category }} · @endif
                            @if ($item->economic_result)<strong>Resultado econômico:</strong> {{ $item->economic_result }}@endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<table class="clean">
    <tr>
        <td style="width:49%;vertical-align:top;padding-right:8px">
            <div class="box">
                <div class="section">Condições comerciais</div>
                <table class="data">
                    <tr><th>Forma de pagamento</th><td>{{ $paymentMethodLabel }}</td></tr>
                    <tr><th>Condição</th><td>{{ $order->paymentCondition?->name ?? '-' }}</td></tr>
                    <tr><th>Parcelas</th><td>{{ $order->installments ?? 1 }}</td></tr>
                    <tr><th>Primeiro vencimento</th><td>{{ $order->first_due_date?->format('d/m/Y') ?? '-' }}</td></tr>
                </table>

                @if ($installments !== [])
                    <table class="data" style="margin-top:5px">
                        <tr><th>Parcela</th><th>Vencimento</th><th class="right">Valor</th></tr>
                        @foreach ($installments as $installment)
                            <tr>
                                <td>{{ $installment['number'] }}/{{ count($installments) }}</td>
                                <td>{{ $installment['due_date']->format('d/m/Y') }}</td>
                                <td class="right">R$ {{ number_format($installment['amount'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        </td>
        <td style="vertical-align:top">
            <div class="box">
                <div class="section">Resumo financeiro</div>
                <table class="data total-box">
                    <tr><th>Subtotal</th><td class="right">R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</td></tr>
                    <tr><th>Desconto</th><td class="right">R$ {{ number_format((float) $order->discount_value, 2, ',', '.') }}</td></tr>
                    @if ($isPurchase)
                        <tr><th>Frete</th><td class="right">R$ {{ number_format((float) $order->freight_value, 2, ',', '.') }}</td></tr>
                    @endif
                    <tr><th>Outras despesas</th><td class="right">R$ {{ number_format((float) $order->additional_value, 2, ',', '.') }}</td></tr>
                    <tr class="grand-total"><th>TOTAL</th><td class="right">R$ {{ number_format((float) $order->total_value, 2, ',', '.') }}</td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

@if ($order->supplier_notes || $order->internal_notes)
<div class="box">
    <div class="section">Observações</div>
    @if ($order->supplier_notes)
        <p><strong>Ao fornecedor:</strong> {{ $order->supplier_notes }}</p>
    @endif
    @if ($order->internal_notes)
        <p><strong>Internas:</strong> {{ $order->internal_notes }}</p>
    @endif
</div>
@endif

<div class="box signatures">
    <div class="section">Autorizações e assinaturas</div>
    <table class="clean">
        <tr>
            <td style="width:33%;padding-right:10px">
                <div class="signature-line"></div>
                <div class="center">Solicitante/Comprador</div>
            </td>
            <td style="width:33%;padding-right:10px">
                <div class="signature-line"></div>
                <div class="center">Aprovador</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="center">{{ $isPurchase ? 'Fornecedor' : 'Prestador' }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Documento gerado pelo Careca Locadora ERP em
    {{ $generatedAt->format('d/m/Y H:i:s') }} ·
    Hash SHA-256: {{ $documentHash }}
</div>
</body>
</html>
