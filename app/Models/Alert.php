<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_RESOLVED = 'resolved';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    protected $primaryKey = 'alert_id';

    protected $fillable = [
        'bin_id',
        'timestamp',
        'type',
        'description',
        'status',
        'severity',
        'assigned_to',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_at',
        'resolution_note',
        'last_seen_at',
        'is_resolved',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $alert): void {
            if ($alert->status === self::STATUS_RESOLVED) {
                $alert->is_resolved = true;
                $alert->resolved_at ??= now();
            } elseif ($alert->status) {
                $alert->is_resolved     = false;
                $alert->resolved_at     = null;
                $alert->resolution_note = null;
                if ($alert->status === self::STATUS_OPEN) {
                    $alert->acknowledged_at = null;
                    $alert->acknowledged_by = null;
                }
            } elseif ($alert->is_resolved) {
                $alert->status      = self::STATUS_RESOLVED;
                $alert->resolved_at ??= now();
            } else {
                $alert->status = self::STATUS_OPEN;
            }

            $alert->severity    ??= self::SEVERITY_WARNING;
            $alert->last_seen_at ??= $alert->timestamp ?? now();
        });
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'bin_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AlertActivity::class, 'alert_id')->latest();
    }

    public function logActivity(string $action, ?string $note = null, ?int $actorId = null, array $meta = []): void
    {
        $this->activities()->create([
            'action' => $action,
            'note' => $note,
            'actor_id' => $actorId,
            'meta' => $meta ?: null,
        ]);
    }
}
