<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MaintenanceLogResource\Pages;
use App\Models\MaintenanceLog;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maintenance Details')
                    ->schema([
                        Select::make('sensor_id')
                            ->relationship('sensor', 'sensor_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Sensor #{$record->sensor_id} ({$record->type})")
                            ->required()
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('maintenance_date')
                            ->required()
                            ->default(now()),
                        TextInput::make('technician_name')
                            ->required()
                            ->maxLength(128),
                        Textarea::make('action_taken')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.bin.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor.sensor_id')
                    ->label('Sensor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('maintenance_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('technician_name')
                    ->label('Technician')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action_taken')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->defaultSort('maintenance_date', 'desc')
            ->filters([
                //
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
            'index' => Pages\ListMaintenanceLogs::route('/'),
            'create' => Pages\CreateMaintenanceLog::route('/create'),
            'edit' => Pages\EditMaintenanceLog::route('/{record}/edit'),
        ];
    }
}
