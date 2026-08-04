<?php
namespace App\Filament\Resources\DigitalCertificates\Pages;
use App\Filament\Resources\DigitalCertificates\DigitalCertificateResource;use Filament\Resources\Pages\ListRecords;
class ListDigitalCertificates extends ListRecords{protected static string $resource=DigitalCertificateResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label('Novo certificado digital')]; }
}
