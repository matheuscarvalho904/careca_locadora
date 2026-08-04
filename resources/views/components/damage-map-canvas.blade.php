@props([
    'item',
    'template',
    'marks' => collect(),
    'legacyMarks' => collect(),
    'mode' => 'delivery',
])

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
@endphp

@once
<style>
    [x-cloak] { display: none !important; }

    .dmg-shell {
        display: grid;
        gap: 1.25rem;
    }

    .dmg-card {
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 18px;
        background: #17181c;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,.18);
    }

    .dmg-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .dmg-title {
        margin: 0;
        color: #fff;
        font-size: 20px;
        font-weight: 700;
    }

    .dmg-subtitle {
        margin: 4px 0 0;
        color: #9ca3af;
        font-size: 14px;
    }

    .dmg-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dmg-badge {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
    }

    .dmg-badge--amber { background: rgba(245,158,11,.18); color: #fcd34d; }
    .dmg-badge--red { background: rgba(239,68,68,.18); color: #fca5a5; }
    .dmg-badge--orange { background: rgba(249,115,22,.18); color: #fdba74; }
    .dmg-badge--green { background: rgba(34,197,94,.18); color: #86efac; }

    .dmg-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .dmg-view-card {
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 16px;
        background: #101116;
        padding: 12px;
    }

    .dmg-view-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .dmg-view-name {
        color: #f3f4f6;
        font-size: 14px;
        font-weight: 700;
    }

    .dmg-count {
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        color: #d1d5db;
        padding: 5px 9px;
        font-size: 12px;
    }

    .dmg-canvas {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 14px;
        background: #f8fafc;
        aspect-ratio: 16 / 9;
        cursor: crosshair;
    }

    .dmg-canvas img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 12px;
        user-select: none;
    }

    .dmg-help {
        margin: 9px 0 0;
        color: #9ca3af;
        font-size: 12px;
    }

    .dmg-marker {
        position: absolute;
        z-index: 30;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 29px;
        height: 29px;
        transform: translate(-50%, -50%);
        border: 2px solid #fff;
        border-radius: 999px;
        color: #fff;
        font-size: 19px;
        font-weight: 900;
        line-height: 1;
        box-shadow: 0 6px 16px rgba(0,0,0,.38);
        cursor: pointer;
    }

    .dmg-marker--amber { background: #f59e0b; }
    .dmg-marker--red { background: #dc2626; }
    .dmg-marker--orange { background: #f97316; }
    .dmg-marker--green { background: #16a34a; }

    .dmg-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(0,0,0,.78);
        backdrop-filter: blur(5px);
    }

    .dmg-modal {
        width: min(760px, 100%);
        max-height: 92vh;
        overflow: auto;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 20px;
        background: #17181c;
        box-shadow: 0 24px 80px rgba(0,0,0,.55);
    }

    .dmg-modal-head {
        position: sticky;
        top: 0;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        border-bottom: 1px solid rgba(255,255,255,.10);
        background: #17181c;
    }

    .dmg-modal-title {
        margin: 0;
        color: #fff;
        font-size: 21px;
        font-weight: 800;
    }

    .dmg-modal-meta {
        margin: 5px 0 0;
        color: #9ca3af;
        font-size: 13px;
    }

    .dmg-close {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 10px;
        background: rgba(255,255,255,.07);
        color: #d1d5db;
        font-size: 20px;
        cursor: pointer;
    }

    .dmg-close:hover { background: rgba(255,255,255,.12); }

    .dmg-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        padding: 24px;
    }

    .dmg-field {
        display: grid;
        gap: 7px;
    }

    .dmg-field--full { grid-column: 1 / -1; }

    .dmg-label {
        color: #f3f4f6;
        font-size: 14px;
        font-weight: 700;
    }

    .dmg-input,
    .dmg-select,
    .dmg-textarea {
        box-sizing: border-box;
        width: 100%;
        min-height: 46px;
        border: 1px solid #4b5563 !important;
        border-radius: 12px !important;
        background: #0f1115 !important;
        color: #fff !important;
        padding: 10px 13px !important;
        font: inherit;
        outline: none;
        box-shadow: none !important;
    }

    .dmg-select {
        appearance: auto;
        color-scheme: dark;
        cursor: pointer;
    }

    .dmg-select option {
        background: #111318 !important;
        color: #fff !important;
    }

    .dmg-input::placeholder,
    .dmg-textarea::placeholder {
        color: #6b7280 !important;
    }

    .dmg-input:focus,
    .dmg-select:focus,
    .dmg-textarea:focus {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245,158,11,.18) !important;
    }

    .dmg-textarea {
        min-height: 112px;
        resize: vertical;
    }

    .dmg-modal-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 12px;
        padding: 17px 24px;
        border-top: 1px solid rgba(255,255,255,.10);
    }

    .dmg-btn {
        min-height: 42px;
        border: 0;
        border-radius: 11px;
        padding: 10px 17px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .dmg-btn--secondary {
        background: rgba(255,255,255,.07);
        color: #e5e7eb;
    }

    .dmg-btn--primary {
        background: #f59e0b;
        color: #111827;
    }

    .dmg-btn--primary:hover { background: #fbbf24; }

    @media (max-width: 900px) {
        .dmg-grid,
        .dmg-form {
            grid-template-columns: 1fr;
        }

        .dmg-field--full { grid-column: auto; }
    }

    @media (max-width: 560px) {
        .dmg-card { padding: 14px; }
        .dmg-modal-backdrop { padding: 8px; }
        .dmg-modal-head,
        .dmg-form,
        .dmg-modal-actions { padding-left: 16px; padding-right: 16px; }
        .dmg-modal-actions .dmg-btn { width: 100%; }
    }
</style>
@endonce

<div
    x-data="{
        open: false,
        itemId: @js($item->id),
        viewId: null,
        viewName: '',
        x: 0,
        y: 0,
        condition: @js($mode === 'return' ? 'new' : 'preexisting'),
        damageType: 'scratch',
        severity: 'light',
        vehiclePart: '',
        description: '',
        estimatedValue: 0,

        choose(event, viewId, viewName) {
            const box = event.currentTarget.getBoundingClientRect()
            this.x = Math.max(0, Math.min(100, ((event.clientX - box.left) / box.width) * 100))
            this.y = Math.max(0, Math.min(100, ((event.clientY - box.top) / box.height) * 100))
            this.viewId = viewId
            this.viewName = viewName
            this.open = true
        },

        submit() {
            $wire.addMark({
                item_id: this.itemId,
                template_view_id: this.viewId,
                position_x: Number(this.x.toFixed(4)),
                position_y: Number(this.y.toFixed(4)),
                condition: this.condition,
                damage_type: this.damageType,
                severity: this.severity,
                vehicle_part: this.vehiclePart,
                description: this.description,
                estimated_value: Number(this.estimatedValue || 0),
            })

            this.open = false
            this.vehiclePart = ''
            this.description = ''
            this.estimatedValue = 0
        },
    }"
    class="dmg-shell"
>
    <section class="dmg-card">
        <div class="dmg-header">
            <div>
                <h2 class="dmg-title">
                    {{ $item->asset?->prefix }} — {{ $item->asset?->name }}
                </h2>
                <p class="dmg-subtitle">
                    {{ $item->asset?->category?->name ?? 'Categoria não informada' }}
                </p>
            </div>

            <div class="dmg-legend">
                <span class="dmg-badge dmg-badge--amber">X amarelo — Preexistente</span>
                <span class="dmg-badge dmg-badge--red">X vermelho — Nova</span>
                <span class="dmg-badge dmg-badge--orange">X laranja — Agravada</span>
                <span class="dmg-badge dmg-badge--green">X verde — Reparada</span>
            </div>
        </div>

        @if (! $template)
            <div class="dmg-card">
                Nenhum diagrama de inspeção foi encontrado para esta categoria de ativo.
            </div>
        @else
            <div class="dmg-grid">
                @foreach ($template->views as $view)
                    @php
                        $currentMarks = $marks->where('template_view_id', $view->id);
                        $previousMarks = $legacyMarks->where('template_view_id', $view->id);
                    @endphp

                    <article class="dmg-view-card">
                        <div class="dmg-view-head">
                            <span class="dmg-view-name">{{ $view->name }}</span>
                            <span class="dmg-count">
                                {{ $currentMarks->count() + $previousMarks->count() }} marcação(ões)
                            </span>
                        </div>

                        <div
                            class="dmg-canvas"
                            x-on:click="choose($event, @js($view->id), @js($view->name))"
                        >
                            <img
                                src="{{ asset($view->image_path) }}"
                                alt="{{ $view->name }}"
                                draggable="false"
                            />

                            @foreach ($previousMarks as $mark)
                                <span
                                    class="dmg-marker dmg-marker--amber"
                                    style="left: {{ $mark->position_x }}%; top: {{ $mark->position_y }}%;"
                                    title="Preexistente: {{ $mark->description ?: ($damageLabels[$mark->damage_type] ?? $mark->damage_type) }}"
                                >×</span>
                            @endforeach

                            @foreach ($currentMarks as $mark)
                                @php
                                    $markerClass = match ($mark->condition) {
                                        'new' => 'dmg-marker--red',
                                        'aggravated' => 'dmg-marker--orange',
                                        'repaired' => 'dmg-marker--green',
                                        default => 'dmg-marker--amber',
                                    };

                                    $conditionLabel = $conditionLabels[$mark->condition] ?? $mark->condition;
                                    $damageLabel = $damageLabels[$mark->damage_type] ?? $mark->damage_type;
                                @endphp

                                <button
                                    type="button"
                                    class="dmg-marker {{ $markerClass }}"
                                    style="left: {{ $mark->position_x }}%; top: {{ $mark->position_y }}%;"
                                    title="{{ $conditionLabel }} — {{ $damageLabel }} — clique para remover"
                                    x-on:click.stop="
                                        if (confirm('Remover esta marcação de avaria?')) {
                                            $wire.deleteMark(@js($mark->id))
                                        }
                                    "
                                >×</button>
                            @endforeach
                        </div>

                        <p class="dmg-help">Clique ou toque diretamente no local da avaria.</p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div
        x-cloak
        x-show="open"
        class="dmg-modal-backdrop"
        x-on:keydown.escape.window="open = false"
    >
        <div class="dmg-modal" x-on:click.stop>
            <div class="dmg-modal-head">
                <div>
                    <h3 class="dmg-modal-title">Registrar avaria</h3>
                    <p class="dmg-modal-meta">
                        Vista:
                        <strong x-text="viewName"></strong>
                        · Posição:
                        <strong>
                            <span x-text="x.toFixed(2)"></span>% ×
                            <span x-text="y.toFixed(2)"></span>%
                        </strong>
                    </p>
                </div>

                <button type="button" class="dmg-close" x-on:click="open = false">✕</button>
            </div>

            <div class="dmg-form">
                @if ($mode === 'return')
                    <label class="dmg-field">
                        <span class="dmg-label">Condição</span>
                        <select x-model="condition" class="dmg-select">
                            <option value="new">Nova</option>
                            <option value="aggravated">Agravada</option>
                            <option value="repaired">Reparada</option>
                        </select>
                    </label>
                @endif

                <label class="dmg-field">
                    <span class="dmg-label">Tipo da avaria</span>
                    <select x-model="damageType" class="dmg-select">
                        <option value="scratch">Arranhão</option>
                        <option value="dent">Amassado</option>
                        <option value="crack">Trinca</option>
                        <option value="broken">Quebrado</option>
                        <option value="missing">Faltando</option>
                        <option value="wear">Desgaste</option>
                        <option value="tire">Pneu danificado</option>
                        <option value="glass">Vidro danificado</option>
                        <option value="internal">Avaria interna</option>
                        <option value="leak">Vazamento</option>
                        <option value="other">Outro</option>
                    </select>
                </label>

                <label class="dmg-field">
                    <span class="dmg-label">Gravidade</span>
                    <select x-model="severity" class="dmg-select">
                        <option value="light">Leve</option>
                        <option value="medium">Média</option>
                        <option value="serious">Grave</option>
                        <option value="critical">Crítica</option>
                    </select>
                </label>

                <label class="dmg-field">
                    <span class="dmg-label">Parte do veículo</span>
                    <input
                        x-model="vehiclePart"
                        class="dmg-input"
                        placeholder="Ex.: porta dianteira direita"
                    />
                </label>

                @if ($mode === 'return')
                    <label class="dmg-field">
                        <span class="dmg-label">Valor estimado</span>
                        <input
                            x-model.number="estimatedValue"
                            type="number"
                            min="0"
                            step="0.01"
                            class="dmg-input"
                        />
                    </label>
                @endif

                <label class="dmg-field dmg-field--full">
                    <span class="dmg-label">Descrição</span>
                    <textarea
                        x-model="description"
                        class="dmg-textarea"
                        placeholder="Descreva o tamanho, extensão e demais detalhes da avaria."
                    ></textarea>
                </label>
            </div>

            <div class="dmg-modal-actions">
                <button
                    type="button"
                    class="dmg-btn dmg-btn--secondary"
                    x-on:click="open = false"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="dmg-btn dmg-btn--primary"
                    x-on:click="submit()"
                >
                    Salvar avaria
                </button>
            </div>
        </div>
    </div>
</div>
