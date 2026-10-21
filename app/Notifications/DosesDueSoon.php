<?php

namespace App\Notifications;

use App\Models\Child;
use App\Services\ScheduledDose;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * One child's doses that are due now or become due within 7 days. Sent once per dose.
 */
class DosesDueSoon extends Notification
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
        $by = $this->doses->map(fn (ScheduledDose $d) => $d->effectiveDueDate)->max()->format('j M');
        $count = $this->doses->count();

        return [
            'kind' => 'warning',
            'event' => 'due_soon',
            'title' => "{$this->child->first_name} has ".($count === 1 ? 'a vaccine' : "{$count} vaccines").' due',
            'body' => "{$labels} ".($count === 1 ? 'is' : 'are')." due by {$by}. Book a visit so they're given on time.",
            'url' => route('parent.book.doses', $this->child, absolute: false),
            'child_id' => $this->child->id,
            'child_dose_ids' => $this->doses->map(fn (ScheduledDose $d) => $d->dose->id)->values()->all(),
        ];
    }
}
