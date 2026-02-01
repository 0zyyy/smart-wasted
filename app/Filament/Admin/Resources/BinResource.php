<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BinResource\Pages;
use App\Models\Bin;
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

class BinResource extends Resource
{
    protected static ?string $model = Bin::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static string|UnitEnum|null $navigationGroup = 'Infrastructure';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bin Details')
                    ->schema([
                        Select::make('location_id')
                            ->relationship('location', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->options([
                                'Organic' => 'Organic',
                                'Anorganic' => 'Anorganic',
                                'B3' => 'B3',
                            ])
                            ->required(),
                        TextInput::make('capacity')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('liters'),
                        DateTimePicker::make('last_emptied'),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bin_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric(2)
                    ->suffix(' L')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_emptied')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensors_count')
                    ->label('Sensors')
                    ->counts('sensors')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListBins::route('/'),
            'create' => Pages\CreateBin::route('/create'),
            'edit' => Pages\EditBin::route('/{record}/edit'),
        ];
    }
}
