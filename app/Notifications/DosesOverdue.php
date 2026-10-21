<?php

namespace App\Notifications;

use App\Models\Child;
use App\Services\ScheduledDose;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * One child's doses that have become overdue. Sent once per dose.
 */
class DosesOverdue extends Notification
{
    /**
     * @param  Collection<int, ScheduledDose>  $doses
     */
    public function __construct(public readonly Child $child, public readonly Collection $doses) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $labels = $this->doses->map->label()->implode(', ');
        $count = $this->doses->count();

        return [
            'kind' => 'danger',
            'event' => 'overdue',
            'title' => "{$this->child->first_name} has ".($count === 1 ? 'an overdue vaccine' : "{$count} overdue vaccines"),
            'body' => "{$labels} ".($count === 1 ? 'is' : 'are').' overdue. They can still be given as catch-up: please book as soon as you can.',
            'url' => route('parent.book.doses', $this->child, absolute: false),
            'child_id' => $this->child->id,
            'child_dose_ids' => $this->doses->map(fn (ScheduledDose $d) => $d->dose->id)->values()->all(),
        ];
    }
}
