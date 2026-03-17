<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertActivityResource\Pages;
use App\Models\AlertActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AlertActivityResource extends Resource
{
    protected static ?string $model = AlertActivity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['alert.bin.location', 'actor']))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alert.alert_id')
                    ->label('Alert')
                    ->prefix('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('alert.bin.location.name')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable(),
                Tables\Columns\TextColumn::make('note')
                    ->limit(60)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'opened' => 'Opened',
                        'acknowledged' => 'Acknowledged',
                        'assigned' => 'Assigned',
                        'resolved' => 'Resolved',
                        'reopened' => 'Reopened',
                        'updated' => 'Updated',
                    ]),
            ])
            ->recordActions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlertActivities::route('/'),
        ];
    }
}

