<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AnalysisResultResource\Pages;
use App\Models\AnalysisResult;
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

class AnalysisResultResource extends Resource
{
    protected static ?string $model = AnalysisResult::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Telemetry';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Analysis Details')
                    ->schema([
                        Select::make('bin_id')
                            ->relationship('bin', 'bin_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Bin #{$record->bin_id} - {$record->location?->name}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('analysis_type')
                            ->required()
                            ->maxLength(64)
                            ->placeholder('e.g., Fill Prediction, Anomaly Detection'),
                        DateTimePicker::make('timestamp')
                            ->default(now()),
                        Textarea::make('result_data')
                            ->label('Result Data (JSON)')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('result_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin.bin_id')
                    ->label('Bin')
                    ->sortable(),
                Tables\Columns\TextColumn::make('analysis_type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('analysis_type')
                    ->options(fn () => AnalysisResult::query()->distinct()->pluck('analysis_type', 'analysis_type')->toArray()),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListAnalysisResults::route('/'),
            'create' => Pages\CreateAnalysisResult::route('/create'),
        ];
    }
}
