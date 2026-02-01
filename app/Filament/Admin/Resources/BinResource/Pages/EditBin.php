<?php

namespace App\Filament\Admin\Resources\BinResource\Pages;

use App\Filament\Admin\Resources\BinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBin extends EditRecord
{
    protected static string $resource = BinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
