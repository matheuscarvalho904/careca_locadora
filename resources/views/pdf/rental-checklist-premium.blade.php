<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Checklist {{ $record->number }}</title>
@php
    $conditionLabels = [
        'preexisting' => 'Preexistente',
        'new' => 'Nova',
        'aggravated' => 'Agravada',
        'repaired' => 'Reparada',
    ];

    $damageLabels = [
        'scratch' => 'Arranhão',
        'dent' => 'Amassado',
        'crack' => 'Trinca',
        'broken' => 'Quebrado',
        'missing' => 'Faltando',
        'wear' => 'Desgaste',
        'tire' => 'Pneu danificado',
        'glass' => 'Vidro danificado',
        'internal' => 'Avaria interna',
        'leak' => 'Vazamento',
        'other' => 'Outro',
    ];

    $severityLabels = [
        'light' => 'Leve',
        'medium' => 'Média',
        'serious' => 'Grave',
        'critical' => 'Crítica',
    ];

    $fuelLabels = [
        'empty' => 'Vazio',
        'quarter' => '1/4',
        'half' => '1/2',
        'three_quarters' => '3/4',
        'full' => 'Cheio',
        'not_applicable' => 'Não se aplica',
    ];

    $companyName =
        $record->contract?->company?->trade_name
        ?? $record->contract?->company?->legal_name
        ?? $record->contract?->company?->name
        ?? '-';

    $branchName =
        $record->contract?->branch?->trade_name
        ?? $record->contract?->branch?->name
        ?? '-';
@endphp
<style>
    @page { margin: 22px 26px; }
    body { font-family: DejaVu Sans, sans-serif; color: #20242a; font-size: 9.5px; }
    .header { border-bottom: 3px solid #f59e0b; padding-bottom: 10px; margin-bottom: 12px; }
    .logo { width: 155px; max-height: 58px; object-fit: contain; }
    .title { font-size: 19px; font-weight: bold; margin: 0; }
    .subtitle { color: #666; margin-top: 4px; }
    .box { border: 1px solid #d7dbe0; border-radius: 7px; padding: 9px; margin-bottom: 10px; page-break-inside: avoid; }
    .section-title { font-size: 12.5px; font-weight: bold; color: #111827; margin-bottom: 7px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d7dbe0; padding: 4px 5px; vertical-align: top; }
    th { background: #f1f3f5; text-align: left; }
    .two { width: 100%; }
    .two td { width: 50%; border: 0; padding: 0 5px 0 0; }
    .map { position: relative; height: 168px; border: 1px solid #d7dbe0; background: #f8fafc; overflow: hidden; }
    .map img { position: absolute; left: 0; top: 0; width: 100%; height: 168px; }
    .map-single { width: 50%; margin: 0 auto; }
    .marker { position: absolute; width: 17px; height: 17px; margin-left: -8px; margin-top: -8px; border-radius: 50%; color: white; font-weight: bold; text-align: center; line-height: 17px; font-size: 13px; }
    .amber { background: #f59e0b; }
    .red { background: #dc2626; }
    .orange { background: #f97316; }
    .green { background: #16a34a; }
    .photo-grid { width: 100%; table-layout: fixed; }
    .photo-cell { width: 25%; padding: 4px; border: 0; }
    .photo-card { border: 1px solid #d7dbe0; border-radius: 5px; padding: 4px; page-break-inside: avoid; }
    .photo { width: 100%; height: 105px; object-fit: cover; border: 1px solid #e5e7eb; }
    .photo-caption { margin-top: 4px; font-size: 7.8px; line-height: 1.35; color: #4b5563; }
    .empty-evidence { padding: 12px; text-align: center; color: #6b7280; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 5px; }
    .signature { height: 92px; text-align: center; border-bottom: 1px solid #333; }
    .signature img { max-width: 210px; max-height: 78px; }
    .small { font-size: 7.7px; color: #666; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="header">
    <table style="border:0">
        <tr>
            <td style="border:0;width:35%">
                @if ($logo)
                    <img class="logo" src="{{ $logo }}">
                @endif
            </td>
            <td style="border:0;text-align:right">
                <div class="title">
                    {{ $type === 'return' ? 'CHECKLIST DE DEVOLUÇÃO' : 'CHECKLIST DE ENTREGA' }}
                </div>
                <div class="subtitle">{{ $record->number }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <div class="section-title">Identificação</div>
    <table>
        <tr>
            <th>Contrato</th>
            <td>{{ $record->contract?->number ?? '-' }}</td>
            <th>Cliente</th>
            <td>{{ $record->contract?->customer?->display_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Empresa</th>
            <td>{{ $companyName }}</td>
            <th>Filial</th>
            <td>{{ $branchName }}</td>
        </tr>
        <tr>
            <th>Data prevista</th>
            <td>{{ $record->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
            <th>Data realizada</th>
            <td>{{ ($type === 'return' ? $record->returned_at : $record->delivered_at)?->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    </table>
</div>

@foreach ($items as $itemIndex => $item)
    @if ($itemIndex > 0)
        <div class="page-break"></div>
    @endif

    <div class="box">
        <div class="section-title">
            Ativo {{ $item->asset?->prefix }} - {{ $item->asset?->name }}
        </div>

        <table>
            <tr>
                <th>Categoria</th>
                <td>{{ $item->asset?->category?->name ?? '-' }}</td>
                <th>Placa</th>
                <td>{{ $item->asset?->plate ?? '-' }}</td>
            </tr>

            @if ($type === 'delivery')
                <tr>
                    <th>Hodômetro</th>
                    <td>{{ $item->odometer !== null ? number_format((float) $item->odometer, 2, ',', '.') : '-' }}</td>
                    <th>Horímetro</th>
                    <td>{{ $item->hourmeter !== null ? number_format((float) $item->hourmeter, 2, ',', '.') : '-' }}</td>
                </tr>
                <tr>
                    <th>Combustível</th>
                    <td>{{ $fuelLabels[$item->fuel_level] ?? 'Não informado' }}</td>
                    <th>Observações</th>
                    <td>{{ $item->accessories_notes ?? '-' }}</td>
                </tr>
            @else
                <tr>
                    <th>KM entrega / devolução</th>
                    <td>{{ $item->initial_odometer !== null ? number_format((float) $item->initial_odometer, 2, ',', '.') : '-' }} / {{ $item->final_odometer !== null ? number_format((float) $item->final_odometer, 2, ',', '.') : '-' }}</td>
                    <th>Horímetro entrega / devolução</th>
                    <td>{{ $item->initial_hourmeter !== null ? number_format((float) $item->initial_hourmeter, 2, ',', '.') : '-' }} / {{ $item->final_hourmeter !== null ? number_format((float) $item->final_hourmeter, 2, ',', '.') : '-' }}</td>
                </tr>
                <tr>
                    <th>Combustível entrega / devolução</th>
                    <td>{{ $fuelLabels[$item->initial_fuel_level] ?? 'Não informado' }} / {{ $fuelLabels[$item->final_fuel_level] ?? 'Não informado' }}</td>
                    <th>Total adicional</th>
                    <td>R$ {{ number_format((float) $item->total_charge_value, 2, ',', '.') }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="box">
        <div class="section-title">Checklist técnico</div>
        <table>
            <tr>
                <th>Lataria/estrutura</th><td>{{ $item->body_ok ? 'OK' : 'NÃO CONFORME' }}</td>
                <th>Pneus/rodagem</th><td>{{ $item->tires_ok ? 'OK' : 'NÃO CONFORME' }}</td>
            </tr>
            <tr>
                <th>Iluminação</th><td>{{ $item->lights_ok ? 'OK' : 'NÃO CONFORME' }}</td>
                <th>Vidros/espelhos</th><td>{{ $item->glass_ok ? 'OK' : 'NÃO CONFORME' }}</td>
            </tr>
            <tr>
                <th>Documentos</th><td>{{ $item->documents_ok ? 'OK' : 'NÃO CONFORME' }}</td>
                <th>Acessórios</th><td>{{ $item->accessories_ok ? 'OK' : 'NÃO CONFORME' }}</td>
            </tr>
            <tr>
                <th>Limpeza</th><td>{{ $item->cleanliness_ok ? 'OK' : 'NÃO CONFORME' }}</td>
                <th>Chave / Manual</th>
                <td>
                    {{ ($type === 'delivery' ? $item->primary_key_delivered : $item->primary_key_returned) ? 'Chave OK' : 'Chave pendente' }}
                    /
                    {{ ($type === 'delivery' ? $item->manual_delivered : $item->manual_returned) ? 'Manual OK' : 'Manual pendente' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="section-title">Mapa visual de avarias</div>
        @php $views = $maps[$item->id]['views'] ?? []; @endphp

        @foreach (array_chunk($views, 2) as $viewRow)
            @if (count($viewRow) === 1)
                @php $view = $viewRow[0]; @endphp
                <div class="map-single">
                    <strong>{{ $view['name'] }}</strong>
                    <div class="map">
                        @if ($view['image']) <img src="{{ $view['image'] }}"> @endif
                        @foreach ($view['previous_marks'] as $mark)
                            <span class="marker amber" style="left:{{ $mark->position_x }}%;top:{{ $mark->position_y }}%">×</span>
                        @endforeach
                        @foreach ($view['current_marks'] as $mark)
                            @php
                                $markerClass = match ($mark->condition) {
                                    'new' => 'red',
                                    'aggravated' => 'orange',
                                    'repaired' => 'green',
                                    default => 'amber',
                                };
                            @endphp
                            <span class="marker {{ $markerClass }}" style="left:{{ $mark->position_x }}%;top:{{ $mark->position_y }}%">×</span>
                        @endforeach
                    </div>
                </div>
            @else
                <table class="two" style="margin-bottom:8px">
                    <tr>
                        @foreach ($viewRow as $view)
                            <td>
                                <strong>{{ $view['name'] }}</strong>
                                <div class="map">
                                    @if ($view['image']) <img src="{{ $view['image'] }}"> @endif
                                    @foreach ($view['previous_marks'] as $mark)
                                        <span class="marker amber" style="left:{{ $mark->position_x }}%;top:{{ $mark->position_y }}%">×</span>
                                    @endforeach
                                    @foreach ($view['current_marks'] as $mark)
                                        @php
                                            $markerClass = match ($mark->condition) {
                                                'new' => 'red',
                                                'aggravated' => 'orange',
                                                'repaired' => 'green',
                                                default => 'amber',
                                            };
                                        @endphp
                                        <span class="marker {{ $markerClass }}" style="left:{{ $mark->position_x }}%;top:{{ $mark->position_y }}%">×</span>
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endif
        @endforeach
    </div>

    <div class="box">
        <div class="section-title">Relação de avarias</div>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Condição</th><th>Tipo</th><th>Gravidade</th><th>Parte</th><th>Vista</th><th>Valor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($item->damageMarks as $index => $mark)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $conditionLabels[$mark->condition] ?? $mark->condition }}</td>
                        <td>{{ $damageLabels[$mark->damage_type] ?? $mark->damage_type }}</td>
                        <td>{{ $severityLabels[$mark->severity] ?? $mark->severity }}</td>
                        <td>{{ $mark->vehicle_part ?? '-' }}</td>
                        <td>{{ $mark->templateView?->name ?? '-' }}</td>
                        <td>R$ {{ number_format((float) $mark->estimated_value, 2, ',', '.') }}</td>
                    </tr>
                    @if ($mark->description)
                        <tr><td colspan="7"><strong>Descrição:</strong> {{ $mark->description }}</td></tr>
                    @endif
                @empty
                    <tr><td colspan="7">Nenhuma avaria registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        $damagePhotos = [];

        foreach ($item->damageMarks as $mark) {
            foreach ($mark->photos as $photo) {
                $damagePhotos[] = [
                    'mark' => $mark,
                    'data' => app(
                        \App\Services\Rentals\ChecklistDocumentService::class
                    )->privateDataUri($photo->file_path),
                ];
            }
        }
    @endphp

    <div class="box">
        <div class="section-title">Fotos das avarias</div>

        @if ($damagePhotos !== [])
            <table class="photo-grid">
                @foreach (array_chunk($damagePhotos, 4) as $photoRow)
                    <tr>
                        @foreach ($photoRow as $photo)
                            <td class="photo-cell">
                                <div class="photo-card">
                                    @if ($photo['data'])
                                        <img
                                            class="photo"
                                            src="{{ $photo['data'] }}"
                                            alt="Foto da avaria"
                                        >
                                    @endif

                                    <div class="photo-caption">
                                        <strong>
                                            {{ $damageLabels[$photo['mark']->damage_type]
                                                ?? $photo['mark']->damage_type }}
                                        </strong><br>
                                        {{ $photo['mark']->vehicle_part
                                            ?: 'Parte não informada' }}<br>
                                        {{ $conditionLabels[$photo['mark']->condition]
                                            ?? $photo['mark']->condition }}
                                        ·
                                        {{ $severityLabels[$photo['mark']->severity]
                                            ?? $photo['mark']->severity }}
                                    </div>
                                </div>
                            </td>
                        @endforeach

                        @for ($empty = count($photoRow); $empty < 4; $empty++)
                            <td class="photo-cell"></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-evidence">
                Nenhuma fotografia registrada para esta vistoria.
            </div>
        @endif
    </div>
@endforeach

@if ($type === 'return')
    <div class="box">
        <div class="section-title">Resumo financeiro da devolução</div>
        <table>
            <tr>
                <th>Avarias</th><td>R$ {{ number_format((float) $record->damage_value, 2, ',', '.') }}</td>
                <th>Combustível</th><td>R$ {{ number_format((float) $record->fuel_value, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Limpeza</th><td>R$ {{ number_format((float) $record->cleaning_value, 2, ',', '.') }}</td>
                <th>KM / tempo excedente</th><td>R$ {{ number_format((float) $record->mileage_value + (float) $record->extra_time_value, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Itens faltantes / outros</th><td>R$ {{ number_format((float) $record->missing_accessories_value + (float) $record->other_value, 2, ',', '.') }}</td>
                <th>Total adicional</th><td><strong>R$ {{ number_format((float) $record->total_charge_value, 2, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>
@endif

<div class="box">
    <div class="section-title">Ciência e assinaturas</div>
    <p>
        Declaro que acompanhei a vistoria e estou ciente das condições do ativo,
        dos itens conferidos e das avarias registradas neste documento.
    </p>

    <table class="two" style="margin-top:18px">
        <tr>
            <td>
                <div class="signature">
                    @if ($customer_signature) <img src="{{ $customer_signature }}"> @endif
                </div>
                <div style="text-align:center">
                    {{ $record->customer_signer_name ?: 'Assinatura do cliente' }}
                </div>
            </td>
            <td>
                <div class="signature">
                    @if ($employee_signature) <img src="{{ $employee_signature }}"> @endif
                </div>
                <div style="text-align:center">
                    {{ $record->employee_signer_name ?: 'Assinatura do responsável' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="small" style="margin-top:15px">
        Gerado em {{ $generated_at->format('d/m/Y H:i:s') }}.
        Código de integridade: {{ $document_hash }}.
    </div>
</div>
</body>
</html>
