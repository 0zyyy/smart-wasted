<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DataTransmissionResource\Pages;
use App\Models\DataTransmission;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class DataTransmissionResource extends Resource
{
    protected static ?string $model = DataTransmission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-signal';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transmission Details')
                    ->schema([
                        Select::make('sensor_id')
                            ->relationship('sensor', 'sensor_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Sensor #{$record->sensor_id} ({$record->type})")
                            ->required()
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('timestamp')
                            ->default(now()),
                        Toggle::make('successful')
                            ->default(true),
                        Textarea::make('error_message')
                            ->maxLength(255)
                            ->rows(2)
                            ->visible(fn ($get) => !$get('successful')),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transmission_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.bin.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.sensor_id')
                    ->label('Sensor')
                    ->sortable(),
                Tables\Columns\IconColumn::make('successful')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('error_message')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('successful')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Successful')
                    ->falseLabel('Failed'),
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
            'index' => Pages\ListDataTransmissions::route('/'),
        ];
    }
}
