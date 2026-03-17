<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MeasurementResource\Pages;
use App\Models\Measurement;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MeasurementResource extends Resource
{
    protected static ?string $model = Measurement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('sensor.bin.location'))
            ->columns([
                Tables\Columns\TextColumn::make('measurement_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.bin.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.sensor_id')
                    ->label('Sensor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->badge(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('sensor')
                    ->relationship('sensor', 'sensor_id'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeasurements::route('/'),
        ];
    }
}
