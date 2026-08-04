<?php

namespace App\Services\Rentals;

use App\Models\RentalDelivery;
use App\Models\RentalReturn;
use Illuminate\Support\Facades\Storage;

final class ChecklistDocumentService
{
    public function deliveryData(RentalDelivery $delivery): array
    {
        $delivery->loadMissing([
            'contract.customer',
            'contract.company',
            'contract.branch',
            'responsibleUser',
            'items.asset.category',
            'items.damageMarks.templateView.template',
            'items.damageMarks.photos',
        ]);

        return $this->buildData('delivery', $delivery, $delivery->items);
    }

    public function returnData(RentalReturn $return): array
    {
        $return->loadMissing([
            'contract.customer',
            'contract.company',
            'contract.branch',
            'delivery',
            'responsibleUser',
            'items.asset.category',
            'items.deliveryItem.damageMarks.templateView.template',
            'items.deliveryItem.damageMarks.photos',
            'items.damageMarks.templateView.template',
            'items.damageMarks.photos',
        ]);

        return $this->buildData('return', $return, $return->items);
    }

    private function buildData(
        string $type,
        RentalDelivery|RentalReturn $record,
        iterable $items,
    ): array {
        $maps = [];

        foreach ($items as $item) {
            $template = app(DamageMapService::class)->templateFor($item);
            $currentMarks = $item->damageMarks->where('status', 'active')->values();
            $previousMarks = $type === 'return'
                ? app(DamageMapService::class)->deliveryMarksForReturn($item)
                : collect();

            $views = [];

            foreach ($template?->views ?? [] as $view) {
                $views[] = [
                    'id' => $view->id,
                    'name' => $view->name,
                    'image' => $this->publicDataUri($view->image_path),
                    'current_marks' => $currentMarks
                        ->where('template_view_id', $view->id)
                        ->values(),
                    'previous_marks' => $previousMarks
                        ->where('template_view_id', $view->id)
                        ->values(),
                ];
            }

            $maps[$item->id] = [
                'template' => $template,
                'views' => $views,
            ];
        }

        $payload = [
            'type' => $type,
            'number' => $record->number,
            'contract' => $record->contract?->number,
            'generated_at' => now()->toIso8601String(),
            'record_updated_at' => $record->updated_at?->toIso8601String(),
            'item_ids' => collect($items)->pluck('id')->values()->all(),
        ];

        return [
            'type' => $type,
            'record' => $record,
            'items' => collect($items),
            'maps' => $maps,
            'logo' => $this->publicDataUri('images/careca-locadora-logo.png'),
            'customer_signature' => $this->privateDataUri(
                $record->customer_signature_path
            ),
            'employee_signature' => $this->privateDataUri(
                $record->employee_signature_path
            ),
            'document_hash' => hash(
                'sha256',
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            ),
            'generated_at' => now(),
        ];
    }

    public function privateDataUri(?string $path): ?string
    {
        if (blank($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($path)
            ?: 'application/octet-stream';

        return sprintf(
            'data:%s;base64,%s',
            $mime,
            base64_encode(Storage::disk('local')->get($path))
        );
    }

    public function publicDataUri(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $absolute = public_path(ltrim($path, '/'));

        if (! is_file($absolute)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            $svg = file_get_contents($absolute);

            $svg = preg_replace(
                '/<filter\b[^>]*>.*?<\/filter>/is',
                '',
                $svg
            );

            $svg = preg_replace(
                '/\sfilter="url\([^"]+\)"/i',
                '',
                $svg
            );

            $svg = str_replace(
                'fill="url(#bg)"',
                'fill="#f8fafc"',
                $svg
            );

            return sprintf(
                'data:image/svg+xml;base64,%s',
                base64_encode($svg)
            );
        }

        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return sprintf(
            'data:%s;base64,%s',
            $mime,
            base64_encode(file_get_contents($absolute))
        );
    }
}
