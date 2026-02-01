<?php

namespace App\Filament\Admin\Resources\SensorResource\Pages;

use App\Filament\Admin\Resources\SensorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSensor extends EditRecord
{
    protected static string $resource = SensorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
