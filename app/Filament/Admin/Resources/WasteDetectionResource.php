<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WasteDetectionResource\Pages;
use App\Models\WasteDetection;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class WasteDetectionResource extends Resource
{
    protected static ?string $model = WasteDetection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Waste Detections';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('location'))
            ->columns([
                Tables\Columns\TextColumn::make('detection_id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('detected_class')
                    ->label('Type')
                    ->colors([
                        'success' => 'Organic',
                        'info'    => 'Anorganic',
                        'warning' => 'B3',
                    ]),

                Tables\Columns\TextColumn::make('confidence')
                    ->label('Confidence')
                    ->formatStateUsing(fn ($state) => number_format($state * 100, 1) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('latency_ms')
                    ->label('Latency')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} ms" : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('device_id')
                    ->label('Camera')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('detected_class')
                    ->label('Type')
                    ->options([
                        'Organic'   => 'Organic',
                        'Anorganic' => 'Anorganic',
                        'B3'        => 'B3',
                    ]),

                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->label('Location'),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteDetections::route('/'),
        ];
    }
}
