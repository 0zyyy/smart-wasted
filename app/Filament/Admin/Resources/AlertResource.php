<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertResource\Pages;
use App\Models\Alert;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alert Details')
                    ->schema([
                        Select::make('bin_id')
                            ->relationship('bin', 'bin_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Bin #{$record->bin_id} - {$record->location?->name}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('type')
                            ->required()
                            ->maxLength(64)
                            ->placeholder('e.g., Overflow, Sensor Failure, Low Battery'),
                        Textarea::make('description')
                            ->required()
                            ->maxLength(255)
                            ->rows(3),
                        DateTimePicker::make('timestamp')
                            ->default(now()),
                        Toggle::make('is_resolved')
                            ->label('Resolved')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alert_id')
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
                    ->color('danger')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('Resolved')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_resolved')
                    ->label('Status')
                    ->placeholder('All Alerts')
                    ->trueLabel('Resolved')
                    ->falseLabel('Open'),
            ])
            ->recordActions([
                Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Alert $record) => $record->update(['is_resolved' => true]))
                    ->requiresConfirmation()
                    ->visible(fn (Alert $record) => !$record->is_resolved),
                Actions\EditAction::make(),
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
            'index' => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit' => Pages\EditAlert::route('/{record}/edit'),
        ];
    }
}
