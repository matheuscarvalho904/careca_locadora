<x-filament-panels::page>
    <style>
        .rental-calendar{--panel:#17191d;--panel2:#121418;--border:rgba(255,255,255,.09);--text:#f8fafc;--muted:#9ca3af}
        .rental-calendar__toolbar{display:flex;align-items:end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
        .rental-calendar__label{display:block;margin-bottom:.45rem;color:var(--text);font-size:.9rem;font-weight:700}
        .rental-calendar__input{width:230px;min-height:42px;border:1px solid var(--border);border-radius:.75rem;background:var(--panel);color:var(--text);padding:.65rem .8rem;color-scheme:dark}
        .rental-calendar__period{margin-top:.4rem;color:var(--muted);font-size:.82rem}
        .rental-calendar__legend{display:flex;gap:.5rem;flex-wrap:wrap}
        .rental-calendar__legend-item{display:inline-flex;align-items:center;gap:.4rem;border:1px solid var(--border);border-radius:999px;background:var(--panel);padding:.42rem .65rem;color:var(--muted);font-size:.74rem}
        .rental-calendar__dot{width:.55rem;height:.55rem;border-radius:999px}
        .dot-free{background:#10b981}.dot-reserved{background:#f59e0b}.dot-rented{background:#3b82f6}.dot-maintenance{background:#ef4444}.dot-blocked{background:#9ca3af}
        .rental-calendar__shell{overflow:hidden;border:1px solid var(--border);border-radius:1rem;background:var(--panel);box-shadow:0 18px 45px rgba(0,0,0,.16)}
        .rental-calendar__scroll{overflow-x:auto;scrollbar-width:thin;scrollbar-color:#4b5563 transparent}
        .rental-calendar__table{width:100%;min-width:1420px;border-collapse:separate;border-spacing:0;table-layout:fixed}
        .rental-calendar__table th,.rental-calendar__table td{border-right:1px solid var(--border);border-bottom:1px solid var(--border)}
        .rental-calendar__table tr:last-child td{border-bottom:0}
        .rental-calendar__asset-head,.rental-calendar__asset-cell{position:sticky;left:0;z-index:4;width:250px;min-width:250px;max-width:250px;background:var(--panel2)}
        .rental-calendar__asset-head{z-index:6;padding:.9rem 1rem;color:var(--text);text-align:left;font-size:.78rem;text-transform:uppercase}
        .rental-calendar__day-head{width:82px;min-width:82px;padding:.72rem .35rem;background:var(--panel2);text-align:center}
        .rental-calendar__day-number{color:var(--text);font-size:.82rem;font-weight:800}
        .rental-calendar__weekday{margin-top:.16rem;color:var(--muted);font-size:.68rem;text-transform:uppercase}
        .rental-calendar__asset-cell{padding:.82rem 1rem}
        .rental-calendar__asset-prefix{display:inline-flex;border:1px solid rgba(245,158,11,.35);border-radius:.42rem;background:rgba(245,158,11,.10);color:#fbbf24;padding:.18rem .42rem;font-size:.72rem;font-weight:900}
        .rental-calendar__asset-name{margin-top:.46rem;color:var(--text);font-size:.82rem;font-weight:700;line-height:1.28;white-space:normal;word-break:break-word}
        .rental-calendar__cell{height:78px;padding:.34rem;background:var(--panel);text-align:center;vertical-align:middle}
        .rental-calendar__slot{display:flex;min-height:57px;align-items:center;justify-content:center;border:1px solid transparent;border-radius:.68rem;padding:.35rem .3rem;font-size:.68rem;font-weight:800;line-height:1.15;text-align:center;overflow:hidden;word-break:break-word}
        .slot-free{border-color:rgba(16,185,129,.30);background:rgba(16,185,129,.12);color:#6ee7b7}
        .slot-reserved{border-color:rgba(245,158,11,.42);background:rgba(245,158,11,.18);color:#fcd34d}
        .slot-rented{border-color:rgba(59,130,246,.42);background:rgba(59,130,246,.18);color:#93c5fd}
        .slot-maintenance{border-color:rgba(239,68,68,.42);background:rgba(239,68,68,.18);color:#fca5a5}
        .slot-blocked{border-color:rgba(156,163,175,.38);background:rgba(107,114,128,.22);color:#d1d5db}
        .rental-calendar__empty{padding:3.5rem 1.5rem;color:var(--muted);text-align:center}
    </style>

    <div class="rental-calendar">
        <div class="rental-calendar__toolbar">
            <div>
                <label class="rental-calendar__label">Início da agenda</label>
                <input type="date" wire:model.live="startDate" class="rental-calendar__input" />
                <div class="rental-calendar__period">Visão dos próximos 14 dias.</div>
            </div>

            <div class="rental-calendar__legend">
                <span class="rental-calendar__legend-item"><span class="rental-calendar__dot dot-free"></span>Livre</span>
                <span class="rental-calendar__legend-item"><span class="rental-calendar__dot dot-reserved"></span>Reservado</span>
                <span class="rental-calendar__legend-item"><span class="rental-calendar__dot dot-rented"></span>Em locação</span>
                <span class="rental-calendar__legend-item"><span class="rental-calendar__dot dot-maintenance"></span>Manutenção</span>
                <span class="rental-calendar__legend-item"><span class="rental-calendar__dot dot-blocked"></span>Indisponível</span>
            </div>
        </div>

        <div class="rental-calendar__shell">
            <div class="rental-calendar__scroll">
                <table class="rental-calendar__table">
                    <thead>
                        <tr>
                            <th class="rental-calendar__asset-head">Ativo</th>
                            @foreach ($this->days as $day)
                                <th class="rental-calendar__day-head">
                                    <div class="rental-calendar__day-number">{{ $day->format('d/m') }}</div>
                                    <div class="rental-calendar__weekday">{{ $day->locale('pt_BR')->translatedFormat('D') }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->schedule as $assetId => $items)
                            @php
                                $asset = $items->first()?->asset;
                            @endphp
                            <tr>
                                <td class="rental-calendar__asset-cell">
                                    <span class="rental-calendar__asset-prefix">{{ $asset?->prefix ?: 'SEM PREFIXO' }}</span>
                                    <div class="rental-calendar__asset-name">{{ $asset?->name ?: 'Ativo não identificado' }}</div>
                                </td>
                                @foreach ($this->days as $day)
                                    @php
                                        $dayStart = $day->copy()->startOfDay();
                                        $dayEnd = $day->copy()->endOfDay();
                                        $booking = $items->first(function ($item) use ($dayStart, $dayEnd) {
                                            return $item->starts_at->lt($dayEnd)
                                                && $item->ends_at->gt($dayStart);
                                        });
                                        $status = $booking?->reservation?->status;
                                        $slotType = match ($status) {
                                            'active', 'in_rental', 'rented' => 'rented',
                                            'maintenance' => 'maintenance',
                                            'blocked', 'unavailable', 'cancelled' => 'blocked',
                                            default => $booking ? 'reserved' : 'free',
                                        };
                                        $statusLabel = match ($slotType) {
                                            'rented' => 'Em locação',
                                            'maintenance' => 'Manutenção',
                                            'blocked' => 'Indisponível',
                                            'reserved' => $booking?->reservation?->number ?: 'Reservado',
                                            default => 'Livre',
                                        };
                                        $tooltip = $booking
                                            ? implode(' | ', array_filter([
                                                $booking->reservation?->number,
                                                $booking->reservation?->customer?->display_name,
                                                $booking->starts_at?->format('d/m/Y H:i'),
                                                $booking->ends_at?->format('d/m/Y H:i'),
                                            ]))
                                            : 'Ativo livre neste dia';
                                    @endphp
                                    <td class="rental-calendar__cell">
                                        <div class="rental-calendar__slot slot-{{ $slotType }}" title="{{ $tooltip }}">
                                            {{ $statusLabel }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="rental-calendar__empty">
                                    Nenhum ativo encontrado para o período selecionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
