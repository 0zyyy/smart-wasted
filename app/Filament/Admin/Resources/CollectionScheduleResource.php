<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CollectionScheduleResource\Pages;
use App\Models\CollectionSchedule;
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

class CollectionScheduleResource extends Resource
{
    protected static ?string $model = CollectionSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Details')
                    ->schema([
                        Select::make('location_id')
                            ->relationship('location', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('planned_time')
                            ->required(),
                        TextInput::make('collector_name')
                            ->required()
                            ->maxLength(128)
                            ->placeholder('Crew member name'),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schedule_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('planned_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collector_name')
                    ->label('Collector')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('planned_time', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name'),
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
            'index' => Pages\ListCollectionSchedules::route('/'),
            'create' => Pages\CreateCollectionSchedule::route('/create'),
            'edit' => Pages\EditCollectionSchedule::route('/{record}/edit'),
        ];
    }
}
