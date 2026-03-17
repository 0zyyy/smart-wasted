<?php

namespace App\Filament\Admin\Widgets;

use App\Models\CollectionSchedule;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingCollectionsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Upcoming Collections';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CollectionSchedule::query()
                    ->with('location')
                    ->where('planned_time', '>=', now())
                    ->orderBy('planned_time')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('location.name')
                    ->label('Location')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('planned_time')
                    ->label('Scheduled')
                    ->dateTime('D, d M H:i')
                    ->sortable(),
                TextColumn::make('collector_name')
                    ->label('Collector')
                    ->default('—'),
            ])
            ->paginated(false);
    }
}
