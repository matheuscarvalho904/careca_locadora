<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fatura de Locação {{ $invoice->number }}</title>
    <style>
        @page {
            margin: 18mm 14mm 20mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #17191d;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.35;
        }

        .header {
            width: 100%;
            background: #08090b;
            color: #ffffff;
            border-bottom: 5px solid #e30620;
            padding: 17px 20px 15px 20px;
        }

        .header-table,
        .info-table,
        .summary-table,
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo {
            width: 155px;
            max-height: 95px;
        }

        .invoice-title {
            text-align: right;
            vertical-align: middle;
        }

        .invoice-title h1 {
            margin: 0;
            font-size: 23px;
            letter-spacing: .5px;
        }

        .invoice-number {
            margin-top: 7px;
            color: #ff1b35;
            font-size: 14px;
            font-weight: bold;
        }

        .document-note {
            margin-top: 7px;
            color: #bfc4cc;
            font-size: 7.5px;
        }

        .section {
            margin-top: 17px;
        }

        .section-title {
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .section-title:after {
            display: block;
            width: 72px;
            height: 3px;
            margin-top: 4px;
            background: #e30620;
            content: "";
        }

        .info-card {
            width: 49%;
            vertical-align: top;
            border: 1px solid #d7d9dd;
            padding: 10px 12px;
        }

        .info-spacer {
            width: 2%;
        }

        .label {
            color: #6b7280;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            margin-top: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .secondary {
            margin-top: 3px;
            color: #4b5563;
            font-size: 8px;
        }

        .meta-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .meta-table td {
            width: 25%;
            border: 1px solid #d7d9dd;
            padding: 7px 9px;
            vertical-align: top;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            background: #17191d;
            color: #ffffff;
            padding: 8px 7px;
            font-size: 7.5px;
            text-align: left;
        }

        .items td {
            border: 1px solid #d7d9dd;
            padding: 7px;
            vertical-align: top;
        }

        .items .number {
            text-align: right;
            white-space: nowrap;
        }

        .asset {
            margin-top: 2px;
            color: #6b7280;
            font-size: 7.5px;
        }

        .totals-wrap {
            width: 100%;
            margin-top: 12px;
        }

        .notes-box {
            width: 57%;
            vertical-align: top;
            padding-right: 14px;
        }

        .totals-box {
            width: 43%;
            background: #f3f4f6;
            padding: 10px 12px;
            vertical-align: top;
        }

        .summary-table td {
            padding: 4px 0;
        }

        .summary-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .summary-total td {
            border-top: 2px solid #e30620;
            color: #e30620;
            padding-top: 8px;
            font-size: 11px;
            font-weight: bold;
        }

        .payment-table th {
            background: #f3f4f6;
            border: 1px solid #d7d9dd;
            padding: 7px;
            text-align: left;
        }

        .payment-table td {
            border: 1px solid #d7d9dd;
            padding: 7px;
        }

        .payment-table .number {
            text-align: right;
        }

        .warning {
            margin-top: 13px;
            border-left: 4px solid #e30620;
            background: #fff3f4;
            color: #a20a1b;
            padding: 8px 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .validation {
            margin-top: 11px;
            color: #6b7280;
            font-size: 7px;
        }

        .footer {
            position: fixed;
            right: -14mm;
            bottom: -20mm;
            left: -14mm;
            height: 14mm;
            background: #08090b;
            color: #ffffff;
            padding: 5mm 14mm 0 14mm;
            font-size: 7px;
        }

        .footer-right {
            float: right;
            color: #bfc4cc;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    @if ($logo)
                        <img src="{{ $logo }}" class="logo" alt="Careca Locadora">
                    @else
                        <strong style="font-size: 20px;">CARECA LOCADORA</strong>
                    @endif
                </td>
                <td class="invoice-title">
                    <h1>FATURA DE LOCAÇÃO</h1>
                    <div class="invoice-number">{{ $invoice->number }}</div>
                    <div class="document-note">
                        Documento financeiro de locação - não é documento fiscal
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="info-table">
            <tr>
                <td class="info-card">
                    <div class="label">Locadora</div>
                    <div class="value">{{ $organization['name'] }}</div>
                    @if ($organization['document'])
                        <div class="secondary">CPF/CNPJ: {{ $organization['document'] }}</div>
                    @endif
                    @if ($organization['address'])
                        <div class="secondary">{{ $organization['address'] }}</div>
                    @endif
                    @if ($organization['phone'] || $organization['email'])
                        <div class="secondary">
                            {{ collect([$organization['phone'], $organization['email']])->filter()->implode(' | ') }}
                        </div>
                    @endif
                </td>
                <td class="info-spacer"></td>
                <td class="info-card">
                    <div class="label">Cliente</div>
                    <div class="value">{{ $customer['name'] }}</div>
                    @if ($customer['legal_name'] && $customer['legal_name'] !== $customer['name'])
                        <div class="secondary">{{ $customer['legal_name'] }}</div>
                    @endif
                    @if ($customer['document'])
                        <div class="secondary">CPF/CNPJ: {{ $customer['document'] }}</div>
                    @endif
                    @if ($customer['address'])
                        <div class="secondary">{{ $customer['address'] }}</div>
                    @endif
                    @if ($customer['phone'] || $customer['email'])
                        <div class="secondary">
                            {{ collect([$customer['phone'], $customer['email']])->filter()->implode(' | ') }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <table class="meta-table">
            <tr>
                <td>
                    <div class="label">Contrato</div>
                    <div class="value">{{ $invoice->contract?->number ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Reserva</div>
                    <div class="value">{{ $invoice->contract?->reservation?->number ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Emissão</div>
                    <div class="value">{{ $invoice->issued_at?->format('d/m/Y') ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Vencimento</div>
                    <div class="value">{{ $invoice->due_at?->format('d/m/Y') ?? '-' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Competência</div>
                    <div class="value">{{ $invoice->competence_date?->format('m/Y') ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">Período da locação</div>
                    <div class="value">
                        {{ $invoice->contract?->starts_at?->format('d/m/Y H:i') ?? '-' }}
                        até
                        {{ $invoice->contract?->ends_at?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                </td>
                <td>
                    <div class="label">Status</div>
                    <div class="value">
                        {{ match ($invoice->status) {
                            'draft' => 'Rascunho',
                            'issued' => 'Emitida',
                            'partially_paid' => 'Parcialmente recebida',
                            'paid' => 'Recebida',
                            'cancelled' => 'Cancelada',
                            default => $invoice->status,
                        } }}
                    </div>
                </td>
                <td>
                    <div class="label">Saldo em aberto</div>
                    <div class="value">R$ {{ number_format((float) $invoice->open_value, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Itens da fatura</div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 48%;">Descrição</th>
                    <th style="width: 10%;">Qtd.</th>
                    <th style="width: 14%;">Unitário</th>
                    <th style="width: 13%;">Desconto</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if ($item->asset)
                                <div class="asset">
                                    {{ $item->asset->prefix }} - {{ $item->asset->name }}
                                </div>
                            @endif
                        </td>
                        <td class="number">{{ number_format((float) $item->quantity, 3, ',', '.') }}</td>
                        <td class="number">R$ {{ number_format((float) $item->unit_value, 2, ',', '.') }}</td>
                        <td class="number">R$ {{ number_format((float) $item->discount_value, 2, ',', '.') }}</td>
                        <td class="number"><strong>R$ {{ number_format((float) $item->total_value, 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-wrap">
            <tr>
                <td class="notes-box">
                    <div class="label">Observações</div>
                    <div style="margin-top: 5px;">
                        {{ $invoice->notes ?: 'Fatura referente à locação descrita no contrato vinculado.' }}
                    </div>
                </td>
                <td class="totals-box">
                    <table class="summary-table">
                        <tr>
                            <td>Subtotal</td>
                            <td class="amount">R$ {{ number_format((float) $invoice->subtotal, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Desconto</td>
                            <td class="amount">R$ {{ number_format((float) $invoice->discount_value, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Acréscimos</td>
                            <td class="amount">R$ {{ number_format((float) $invoice->additional_value, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="summary-total">
                            <td>TOTAL</td>
                            <td class="amount">R$ {{ number_format((float) $invoice->total_value, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>


    @if ($bank)
        <div class="section"><div class="section-title">DADOS PARA PAGAMENTO</div><table class="payment-table"><tbody><tr><th>Banco</th><td>{{ collect([$bank['bank_code'] ?? null, $bank['bank_short_name'] ?? $bank['bank_name'] ?? null])->filter()->implode(' - ') }}</td><th>Agência</th><td>{{ collect([$bank['agency'] ?? null, $bank['agency_digit'] ?? null])->filter()->implode('-') ?: '-' }}</td></tr><tr><th>Conta</th><td>{{ collect([$bank['account_number'] ?? null, $bank['account_digit'] ?? null])->filter()->implode('-') ?: '-' }}</td><th>PIX</th><td><strong>{{ $bank['pix_key'] ?? '-' }}</strong></td></tr></tbody></table></div>
    @endif
    @if ($invoice->receivables->isNotEmpty())
        <div class="section">
            <div class="section-title">Condições de pagamento</div>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Parcela</th>
                        <th>Vencimento</th>
                        <th>Forma</th>
                        <th>Valor</th>
                        <th>Em aberto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->receivables as $receivable)
                        <tr>
                            <td>{{ $receivable->number }}</td>
                            <td>{{ $receivable->installment_number }}/{{ $receivable->installments_count }}</td>
                            <td>{{ $receivable->due_at?->format('d/m/Y') }}</td>
                            <td>{{ strtoupper($receivable->payment_method ?: 'A DEFINIR') }}</td>
                            <td class="number">R$ {{ number_format((float) $receivable->original_value, 2, ',', '.') }}</td>
                            <td class="number">R$ {{ number_format((float) $receivable->open_value, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="warning">
        Esta FATURA DE LOCAÇÃO é um documento de cobrança e controle financeiro.
        Não substitui documento fiscal quando sua emissão for legalmente exigida.
    </div>

    <div class="validation">
        Código de validação: {{ $validationCode }} |
        Documento gerado eletronicamente em {{ now()->format('d/m/Y H:i') }}.
    </div>

    <div class="footer">
        <strong>{{ $organization['name'] }}</strong>
        <span class="footer-right">{{ $invoice->number }} | Careca Locadora</span>
    </div>
</body>
</html>
