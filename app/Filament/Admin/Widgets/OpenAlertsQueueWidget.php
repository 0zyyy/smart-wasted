<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Alert;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OpenAlertsQueueWidget extends BaseWidget
{
    protected static ?string $heading = 'Open Alerts Queue';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '10s';

    #[\Livewire\Attributes\On('alert-created')]
    public function refresh(array $alertData = []): void
    {
        Notification::make()
            ->title('Bin Overflow Alert')
            ->body($alertData['description'] ?? 'A bin has reached overflow level.')
            ->danger()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Alert::query()
                    ->with(['bin.location', 'assignedTo'])
                    ->whereIn('status', [Alert::STATUS_OPEN, Alert::STATUS_ACKNOWLEDGED])
                    ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
                    ->orderBy('timestamp')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('alert_id')
                    ->label('Alert')
                    ->prefix('#'),
                Tables\Columns\TextColumn::make('bin.location.name')
                    ->label('Location'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Alert::SEVERITY_CRITICAL => 'danger',
                        Alert::SEVERITY_WARNING => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === Alert::STATUS_ACKNOWLEDGED ? 'warning' : 'danger'),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Owner')
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Triggered')
                    ->since(),
            ])
            ->actions([
                Action::make('acknowledge')
                    ->label('Acknowledge')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn (Alert $record): bool => $record->status === Alert::STATUS_OPEN)
                    ->action(function (Alert $record): void {
                        $record->status = Alert::STATUS_ACKNOWLEDGED;
                        $record->save();
                        $record->logActivity(
                            action: 'acknowledged',
                            note: 'Acknowledged from dashboard.',
                            actorId: auth()->id(),
                        );
                        Notification::make()
                            ->title('Alert acknowledged')
                            ->success()
                            ->send();
                    }),
            ])
            ->searchable(false)
            ->paginated(false);
    }
}

