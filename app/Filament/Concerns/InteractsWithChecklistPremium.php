<?php

namespace App\Filament\Concerns;

use App\Models\RentalDamageMark;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;

trait InteractsWithChecklistPremium
{
    use WithFileUploads;

    public array $damagePhotoUploads = [];

    public function saveSignature(
        string $role,
        string $dataUrl,
    ): void {
        if (! in_array($role, ['customer', 'employee'], true)) {
            throw ValidationException::withMessages([
                'signature' => 'Tipo de assinatura inválido.',
            ]);
        }

        if (! preg_match(
            '/^data:image\/png;base64,(.+)$/',
            $dataUrl,
            $matches
        )) {
            throw ValidationException::withMessages([
                'signature' => 'Assinatura inválida.',
            ]);
        }

        $binary = base64_decode($matches[1], true);

        if ($binary === false || strlen($binary) < 100) {
            throw ValidationException::withMessages([
                'signature' => 'A assinatura está vazia.',
            ]);
        }

        $directory = $this->signatureDirectory();
        $path = sprintf(
            '%s/%s-%s-%s.png',
            $directory,
            $this->record->id,
            $role,
            now()->format('YmdHis')
        );

        Storage::disk('local')->put($path, $binary);

        $field = $role === 'customer'
            ? 'customer_signature_path'
            : 'employee_signature_path';

        $metadata = (array) ($this->record->metadata ?? []);
        $metadata['signatures'][$role] = [
            'signed_at' => now()->toIso8601String(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $this->record->update([
            $field => $path,
            'metadata' => $metadata,
        ]);

        $this->reloadPremiumChecklist();

        $this->dispatch(
            'checklist-signature-saved',
            role: $role,
        );
    }

    public function saveDamagePhotos(
        string $markId,
    ): void {
        $mark = $this->findAuthorizedDamageMark($markId);

        $uploads = $this->damagePhotoUploads[$markId] ?? [];

        if (! is_array($uploads) || $uploads === []) {
            throw ValidationException::withMessages([
                'photos' => 'Selecione pelo menos uma foto.',
            ]);
        }

        $currentOrder = (int) $mark->photos()->max('display_order');

        foreach ($uploads as $index => $upload) {
            if (! $upload instanceof UploadedFile) {
                continue;
            }

            if (! str_starts_with((string) $upload->getMimeType(), 'image/')) {
                throw ValidationException::withMessages([
                    'photos' => 'Somente imagens são permitidas.',
                ]);
            }

            if ($upload->getSize() > 12 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'photos' => 'Cada imagem deve possuir no máximo 12 MB.',
                ]);
            }

            $path = $upload->store(
                'rental-damage-photos',
                'local'
            );

            $mark->photos()->create([
                'file_path' => $path,
                'caption' => null,
                'display_order' => $currentOrder + $index + 1,
            ]);
        }

        unset($this->damagePhotoUploads[$markId]);

        $this->reloadPremiumChecklist();
    }

    public function deleteDamagePhoto(
        string $photoId,
    ): void {
        $photo = $this->authorizedDamagePhoto($photoId);

        if (Storage::disk('local')->exists($photo->file_path)) {
            Storage::disk('local')->delete($photo->file_path);
        }

        $photo->delete();

        $this->reloadPremiumChecklist();
    }

    abstract protected function signatureDirectory(): string;

    abstract protected function findAuthorizedDamageMark(
        string $markId,
    ): RentalDamageMark;

    abstract protected function authorizedDamagePhoto(
        string $photoId,
    );

    abstract protected function reloadPremiumChecklist(): void;
}
