<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Measurement;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LiveTelemetryWidget extends BaseWidget
{
    protected static ?string $heading = 'Live Telemetry';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '3s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Measurement::query()
                    ->with('sensor.bin.location')
                    ->orderByDesc('timestamp')
                    ->take(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('sensor.bin.location.name')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sensor.sensor_id')
                    ->label('Sensor')
                    ->prefix('#'),
                Tables\Columns\TextColumn::make('sensor.type')
                    ->label('Type')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('value')
                    ->numeric(2)
                    ->suffix(fn($record) => ' ' . $record->unit),
                Tables\Columns\TextColumn::make('timestamp')
                    ->since()
                    ->label('When'),
            ])
            ->searchable(false)
            ->paginated(false);
    }
}
