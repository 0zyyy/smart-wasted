<?php

namespace App\Filament\Admin\Resources\AlertResource\Pages;

use App\Filament\Admin\Resources\AlertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlert extends CreateRecord
{
    protected static string $resource = AlertResource::class;

    protected function afterCreate(): void
    {
        $this->record->logActivity(
            'opened',
            'Alert created from admin form.',
            auth()->id()
        );
    }
}
