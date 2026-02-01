<?php

namespace App\Filament\Admin\Resources\CollectionScheduleResource\Pages;

use App\Filament\Admin\Resources\CollectionScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollectionSchedule extends EditRecord
{
    protected static string $resource = CollectionScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
