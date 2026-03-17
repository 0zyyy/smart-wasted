<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertOverflowNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Alert $alert)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id'    => $this->alert->alert_id,
            'bin_id'      => $this->alert->bin_id,
            'type'        => $this->alert->type,
            'severity'    => $this->alert->severity,
            'description' => $this->alert->description,
            'status'      => $this->alert->status,
        ];
    }
}
