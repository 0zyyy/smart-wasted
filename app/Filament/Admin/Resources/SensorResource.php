<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SensorResource\Pages;
use App\Models\Sensor;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SensorResource extends Resource
{
    protected static ?string $model = Sensor::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|UnitEnum|null $navigationGroup = 'Infrastructure';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sensor Details')
                    ->schema([
                        Select::make('bin_id')
                            ->relationship('bin', 'bin_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Bin #{$record->bin_id} - {$record->location?->name}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->options([
                                'Loadcell' => 'Loadcell',
                                'Ultrasonic' => 'Ultrasonic',
                            ])
                            ->required(),
                        TextInput::make('model')
                            ->maxLength(64)
                            ->placeholder('e.g., HC-SR04'),
                        DateTimePicker::make('last_maintenance'),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sensor_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin.bin_id')
                    ->label('Bin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_maintenance')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('measurements_count')
                    ->label('Readings')
                    ->counts('measurements')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(fn () => Sensor::query()->distinct()->pluck('type', 'type')->toArray()),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            'index' => Pages\ListSensors::route('/'),
            'create' => Pages\CreateSensor::route('/create'),
            'edit' => Pages\EditSensor::route('/{record}/edit'),
        ];
    }
}
