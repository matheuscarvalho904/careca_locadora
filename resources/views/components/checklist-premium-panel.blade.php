@props([
    'record',
    'mode',
])

@php
    $isReturn = $mode === 'return';
    $pdfRoute = $isReturn
        ? route('rental-returns.checklist-pdf', ['rentalReturn' => $record])
        : route('rental-deliveries.checklist-pdf', ['delivery' => $record]);

    $conditionLabels = [
        'preexisting' => 'Preexistente',
        'new' => 'Nova',
        'aggravated' => 'Agravada',
        'repaired' => 'Reparada',
    ];

    $severityLabels = [
        'light' => 'Leve',
        'medium' => 'Média',
        'serious' => 'Grave',
        'critical' => 'Crítica',
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
    .cp-shell{display:grid;gap:20px}
    .cp-card{border:1px solid rgba(255,255,255,.1);border-radius:18px;background:#17181c;padding:20px}
    .cp-head{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px}
    .cp-title{color:#fff;font-size:21px;font-weight:800}
    .cp-muted{color:#9ca3af;font-size:13px}
    .cp-actions{display:flex;flex-wrap:wrap;gap:10px}
    .cp-btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:11px;padding:10px 15px;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer}
    .cp-btn-primary{background:#f59e0b;color:#111827}
    .cp-btn-dark{background:rgba(255,255,255,.08);color:#f3f4f6}
    .cp-item{border:1px solid rgba(255,255,255,.1);border-radius:16px;background:#101116;padding:16px}
    .cp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .cp-mark{border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px;background:#17181c}
    .cp-mark-title{display:flex;justify-content:space-between;gap:10px;color:#fff;font-weight:750}
    .cp-pills{display:flex;flex-wrap:wrap;gap:7px;margin-top:9px}
    .cp-pill{border-radius:999px;padding:5px 9px;background:rgba(255,255,255,.08);color:#d1d5db;font-size:12px}
    .cp-gallery{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:12px}
    .cp-photo{position:relative;overflow:hidden;border-radius:10px;background:#0b0c10;aspect-ratio:1}
    .cp-photo img{width:100%;height:100%;object-fit:cover}
    .cp-photo button{position:absolute;right:5px;top:5px;width:27px;height:27px;border:0;border-radius:999px;background:#dc2626;color:#fff;cursor:pointer}
    .cp-upload{margin-top:12px;border:1px dashed #4b5563;border-radius:12px;padding:12px;color:#d1d5db}
    .cp-upload input{display:block;width:100%;color:#d1d5db}
    .cp-signatures{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .cp-signature{border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:15px;background:#101116}
    .cp-canvas{display:block;width:100%;height:190px;border-radius:12px;background:#fff;touch-action:none}
    .cp-signature-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:10px}
    @media(max-width:850px){.cp-grid,.cp-signatures{grid-template-columns:1fr}.cp-gallery{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endonce

<div class="cp-shell">
    <section class="cp-card">
        <div class="cp-head">
            <div>
                <div class="cp-title">
                    {{ $isReturn ? 'Checklist de devolução' : 'Checklist de entrega' }}
                    - {{ $record->number }}
                </div>
                <div class="cp-muted">
                    Contrato {{ $record->contract?->number ?? '-' }}
                    · Cliente {{ $record->contract?->customer?->display_name ?? '-' }}
                </div>
            </div>

            <div class="cp-actions">
                <a
                    class="cp-btn cp-btn-primary"
                    href="{{ $pdfRoute }}"
                    target="_blank"
                >
                    Visualizar PDF
                </a>

                <a
                    class="cp-btn cp-btn-dark"
                    href="{{ $pdfRoute }}"
                    download
                >
                    Baixar PDF
                </a>

                <button
                    type="button"
                    class="cp-btn cp-btn-dark"
                    onclick="window.open(@js('https://wa.me/?text=' . rawurlencode('Checklist ' . $record->number . ': ' . $pdfRoute)), '_blank')"
                >
                    Compartilhar WhatsApp
                </button>
            </div>
        </div>
    </section>

    @foreach ($record->items as $item)
        <section class="cp-item">
            <div class="cp-title" style="font-size:18px">
                {{ $item->asset?->prefix }} - {{ $item->asset?->name }}
            </div>
            <div class="cp-muted">
                {{ $item->asset?->category?->name ?? 'Categoria não informada' }}
            </div>

            <div class="cp-grid" style="margin-top:15px">
                @forelse ($item->damageMarks as $mark)
                    <article class="cp-mark">
                        <div class="cp-mark-title">
                            <span>
                                {{ $damageLabels[$mark->damage_type] ?? $mark->damage_type }}
                            </span>
                            <span>
                                R$ {{ number_format((float) $mark->estimated_value, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="cp-pills">
                            <span class="cp-pill">
                                {{ $conditionLabels[$mark->condition] ?? $mark->condition }}
                            </span>
                            <span class="cp-pill">
                                {{ $severityLabels[$mark->severity] ?? $mark->severity }}
                            </span>
                            <span class="cp-pill">
                                {{ $mark->vehicle_part ?: 'Parte não informada' }}
                            </span>
                            <span class="cp-pill">
                                {{ $mark->templateView?->name ?? 'Vista não informada' }}
                            </span>
                        </div>

                        @if ($mark->description)
                            <p class="cp-muted" style="margin-top:10px">
                                {{ $mark->description }}
                            </p>
                        @endif

                        <div class="cp-gallery">
                            @foreach ($mark->photos as $photo)
                                <div class="cp-photo">
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('local')->temporaryUrl($photo->file_path, now()->addMinutes(10)) }}"
                                        alt="Foto da avaria"
                                    />
                                    <button
                                        type="button"
                                        title="Excluir foto"
                                        wire:click="deleteDamagePhoto(@js($photo->id))"
                                        wire:confirm="Excluir esta foto?"
                                    >
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="cp-upload">
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                capture="environment"
                                wire:model="damagePhotoUploads.{{ $mark->id }}"
                            />

                            <button
                                type="button"
                                class="cp-btn cp-btn-primary"
                                style="margin-top:10px"
                                wire:click="saveDamagePhotos(@js($mark->id))"
                                wire:loading.attr="disabled"
                            >
                                Salvar fotos
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="cp-muted">
                        Nenhuma avaria registrada para este ativo.
                    </div>
                @endforelse
            </div>
        </section>
    @endforeach

    <section class="cp-card">
        <div class="cp-title" style="margin-bottom:15px">Assinaturas</div>

        <div class="cp-signatures">
            @foreach ([
                'customer' => [
                    'title' => 'Assinatura do cliente',
                    'path' => $record->customer_signature_path,
                ],
                'employee' => [
                    'title' => 'Assinatura do responsável',
                    'path' => $record->employee_signature_path,
                ],
            ] as $role => $signature)
                <div
                    class="cp-signature"
                    x-data="{
                        drawing: false,
                        initialized: false,
                        canvas: null,
                        ctx: null,
                        init() {
                            this.canvas = this.$refs.canvas
                            const ratio = window.devicePixelRatio || 1
                            const rect = this.canvas.getBoundingClientRect()
                            this.canvas.width = rect.width * ratio
                            this.canvas.height = rect.height * ratio
                            this.ctx = this.canvas.getContext('2d')
                            this.ctx.scale(ratio, ratio)
                            this.ctx.lineWidth = 2.4
                            this.ctx.lineCap = 'round'
                            this.ctx.strokeStyle = '#111827'
                        },
                        point(event) {
                            const rect = this.canvas.getBoundingClientRect()
                            const source = event.touches ? event.touches[0] : event
                            return {
                                x: source.clientX - rect.left,
                                y: source.clientY - rect.top,
                            }
                        },
                        start(event) {
                            event.preventDefault()
                            const p = this.point(event)
                            this.drawing = true
                            this.ctx.beginPath()
                            this.ctx.moveTo(p.x, p.y)
                        },
                        move(event) {
                            if (! this.drawing) return
                            event.preventDefault()
                            const p = this.point(event)
                            this.ctx.lineTo(p.x, p.y)
                            this.ctx.stroke()
                        },
                        stop() {
                            this.drawing = false
                        },
                        clear() {
                            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)
                        },
                        save() {
                            $wire.saveSignature(
                                @js($role),
                                this.canvas.toDataURL('image/png')
                            )
                        },
                    }"
                    x-init="init()"
                >
                    <div class="cp-mark-title">
                        <span>{{ $signature['title'] }}</span>
                        @if ($signature['path'])
                            <span style="color:#86efac">Assinada</span>
                        @endif
                    </div>

                    @if ($signature['path'])
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('local')->temporaryUrl($signature['path'], now()->addMinutes(10)) }}"
                            alt="{{ $signature['title'] }}"
                            style="width:100%;height:190px;object-fit:contain;background:#fff;border-radius:12px;margin-top:12px"
                        />
                    @else
                        <canvas
                            x-ref="canvas"
                            class="cp-canvas"
                            style="margin-top:12px"
                            x-on:mousedown="start"
                            x-on:mousemove="move"
                            x-on:mouseup="stop"
                            x-on:mouseleave="stop"
                            x-on:touchstart="start"
                            x-on:touchmove="move"
                            x-on:touchend="stop"
                        ></canvas>

                        <div class="cp-signature-actions">
                            <button
                                type="button"
                                class="cp-btn cp-btn-dark"
                                x-on:click="clear()"
                            >
                                Limpar
                            </button>

                            <button
                                type="button"
                                class="cp-btn cp-btn-primary"
                                x-on:click="save()"
                            >
                                Salvar assinatura
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="cp-muted" style="margin-top:15px">
            O PDF também contém campos em branco para assinatura física quando o documento for impresso.
        </p>
    </section>
</div>
