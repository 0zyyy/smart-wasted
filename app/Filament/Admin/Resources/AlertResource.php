<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AlertResource\Pages;
use App\Models\Alert;
use App\Models\Measurement;
use App\Models\User;
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
                        Select::make('severity')
                            ->options([
                                Alert::SEVERITY_INFO => 'Info',
                                Alert::SEVERITY_WARNING => 'Warning',
                                Alert::SEVERITY_CRITICAL => 'Critical',
                            ])
                            ->default(Alert::SEVERITY_WARNING)
                            ->required(),
                        Select::make('status')
                            ->options([
                                Alert::STATUS_OPEN => 'Open',
                                Alert::STATUS_ACKNOWLEDGED => 'Acknowledged',
                                Alert::STATUS_RESOLVED => 'Resolved',
                            ])
                            ->default(Alert::STATUS_OPEN)
                            ->required(),
                        Select::make('assigned_to')
                            ->label('Assigned To')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('resolution_note')
                            ->label('Resolution Note')
                            ->maxLength(1000)
                            ->rows(3)
                            ->visible(fn ($get) => $get('status') === Alert::STATUS_RESOLVED),
                        DateTimePicker::make('timestamp')
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['bin.location', 'assignedTo']))
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
                    ->color(fn (string $state): string => match ($state) {
                        Alert::STATUS_RESOLVED => 'success',
                        Alert::STATUS_ACKNOWLEDGED => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Owner')
                    ->placeholder('Unassigned')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->since()
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Alert::STATUS_OPEN => 'Open',
                        Alert::STATUS_ACKNOWLEDGED => 'Acknowledged',
                        Alert::STATUS_RESOLVED => 'Resolved',
                    ]),
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        Alert::SEVERITY_INFO => 'Info',
                        Alert::SEVERITY_WARNING => 'Warning',
                        Alert::SEVERITY_CRITICAL => 'Critical',
                    ]),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn ($query) => $query->whereNull('assigned_to')),
            ])
            ->recordActions([
                Actions\Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->modalHeading(fn (Alert $record): string => 'Alert #' . $record->alert_id . ' Details')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->registerModalActions([
                        \Filament\Actions\Action::make('quickAcknowledge')
                            ->label('Acknowledge')
                            ->icon('heroicon-o-hand-raised')
                            ->color('warning')
                            ->visible(fn (Alert $record): bool => $record->status === Alert::STATUS_OPEN)
                            ->requiresConfirmation()
                            ->action(function (Alert $record): void {
                                $record->update([
                                    'status' => Alert::STATUS_ACKNOWLEDGED,
                                    'acknowledged_by' => auth()->id(),
                                    'acknowledged_at' => now(),
                                ]);
                                $record->logActivity('acknowledged', 'Alert acknowledged from details panel.', auth()->id());
                            }),
                        \Filament\Actions\Action::make('quickAssignMe')
                            ->label('Assign to Me')
                            ->icon('heroicon-o-user')
                            ->color('info')
                            ->visible(fn (Alert $record): bool => filled(auth()->id()) && $record->assigned_to !== auth()->id())
                            ->action(function (Alert $record): void {
                                $record->update(['assigned_to' => auth()->id()]);
                                $record->logActivity('assigned', 'Assigned to current user from details panel.', auth()->id());
                            }),
                        \Filament\Actions\Action::make('quickResolve')
                            ->label('Resolve')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->visible(fn (Alert $record): bool => $record->status !== Alert::STATUS_RESOLVED)
                            ->requiresConfirmation()
                            ->action(function (Alert $record): void {
                                $record->update([
                                    'status' => Alert::STATUS_RESOLVED,
                                    'resolution_note' => 'Resolved from details panel.',
                                    'resolved_at' => now(),
                                ]);
                                $record->logActivity('resolved', 'Resolved from details panel.', auth()->id());
                            }),
                        \Filament\Actions\Action::make('quickReopen')
                            ->label('Reopen')
                            ->icon('heroicon-o-arrow-path')
                            ->color('danger')
                            ->visible(fn (Alert $record): bool => $record->status === Alert::STATUS_RESOLVED)
                            ->requiresConfirmation()
                            ->action(function (Alert $record): void {
                                $record->update([
                                    'status' => Alert::STATUS_OPEN,
                                    'is_resolved' => false,
                                    'resolved_at' => null,
                                    'resolution_note' => null,
                                ]);
                                $record->logActivity('reopened', 'Reopened from details panel.', auth()->id());
                            }),
                    ])
                    ->modalContent(function (Alert $record, \Filament\Actions\Action $action) {
                        $record->loadMissing(['bin.location', 'activities.actor', 'assignedTo', 'acknowledgedBy']);

                        $recentMeasurements = Measurement::query()
                            ->with('sensor')
                            ->whereHas('sensor', fn ($query) => $query->where('bin_id', $record->bin_id))
                            ->orderByDesc('timestamp')
                            ->limit(10)
                            ->get();

                        return view('filament.admin.alerts.details', [
                            'action'             => $action,
                            'alert'              => $record,
                            'recentMeasurements' => $recentMeasurements,
                            'activities'         => $record->activities->take(15),
                        ]);
                    }),
                Actions\Action::make('acknowledge')
                    ->icon('heroicon-o-hand-raised')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Alert $record): bool => $record->status === Alert::STATUS_OPEN)
                    ->action(function (Alert $record): void {
                        $record->update([
                            'status' => Alert::STATUS_ACKNOWLEDGED,
                            'acknowledged_by' => auth()->id(),
                            'acknowledged_at' => now(),
                        ]);
                        $record->logActivity('acknowledged', 'Alert acknowledged from dashboard.', auth()->id());
                    }),
                Actions\Action::make('assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Select::make('assigned_to')
                            ->label('Assign To')
                            ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                            ->required(),
                        Textarea::make('note')->rows(2)->maxLength(255),
                    ])
                    ->action(function (array $data, Alert $record): void {
                        $record->update([
                            'assigned_to' => (int) $data['assigned_to'],
                        ]);
                        $record->logActivity(
                            'assigned',
                            $data['note'] ?? ('Assigned to user #' . $data['assigned_to']),
                            auth()->id(),
                            ['assigned_to' => (int) $data['assigned_to']]
                        );
                    }),
                Actions\Action::make('resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Textarea::make('resolution_note')
                            ->label('Resolution Note')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->requiresConfirmation()
                    ->visible(fn (Alert $record): bool => $record->status !== Alert::STATUS_RESOLVED)
                    ->action(function (array $data, Alert $record): void {
                        $record->update([
                            'status' => Alert::STATUS_RESOLVED,
                            'resolution_note' => $data['resolution_note'],
                            'resolved_at' => now(),
                        ]);
                        $record->logActivity('resolved', $data['resolution_note'], auth()->id());
                    }),
                Actions\Action::make('reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Alert $record): bool => $record->status === Alert::STATUS_RESOLVED)
                    ->action(function (Alert $record): void {
                        $record->update([
                            'status' => Alert::STATUS_OPEN,
                            'is_resolved' => false,
                            'resolved_at' => null,
                            'resolution_note' => null,
                        ]);
                        $record->logActivity('reopened', 'Alert reopened from dashboard.', auth()->id());
                    }),
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
