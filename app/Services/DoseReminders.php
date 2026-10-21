<?php

namespace App\Services;

use App\Enums\DoseStatus;
use App\Models\Appointment;
use App\Models\Child;
use App\Notifications\DosesDueSoon;
use App\Notifications\DosesOverdue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Reminds parents about doses that are due within 7 days or overdue.
 *
 * Each dose is reminded at most once per kind (due / overdue), and never while it
 * has an open appointment. A child's doses are grouped into one notification.
 */
final class DoseReminders
{
    public const DUE_WITHIN_DAYS = 7;

    /**
     * @return array{due: int, overdue: int} notifications sent
     */
    public function send(?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $sent = ['due' => 0, 'overdue' => 0];

        $booked = Appointment::open()->pluck('child_dose_id')->flip();

        Child::query()
            ->whereHas('parent')
            ->with('parent', 'doses.scheduleDose.vaccine', 'doses.administeredBy')
            ->chunkById(100, function (Collection $children) use ($today, $booked, &$sent) {
                foreach ($children as $child) {
                    $doses = $child->schedule($today)->doses()->reject(fn (ScheduledDose $d) => $booked->has($d->dose->id));
                    $already = $this->alreadyReminded($child);

                    $due = $doses->filter(fn (ScheduledDose $d) => $d->effectiveDueDate !== null
                        && in_array($d->status, [DoseStatus::Due, DoseStatus::Upcoming], true)
                        && $d->effectiveDueDate->lte($today->addDays(self::DUE_WITHIN_DAYS))
                        && ! $already['due']->contains($d->dose->id));

                    $overdue = $doses->filter(fn (ScheduledDose $d) => $d->status === DoseStatus::Overdue
                        && ! $already['overdue']->contains($d->dose->id));

                    if ($due->isNotEmpty()) {
                        $child->parent->notify(new DosesDueSoon($child, $due->values()));
                        $sent['due']++;
                    }

                    if ($overdue->isNotEmpty()) {
                        $child->parent->notify(new DosesOverdue($child, $overdue->values()));
                        $sent['overdue']++;
                    }
                }
            });

        return $sent;
    }

    /**
     * Dose ids this child's parent has already been reminded about, per kind.
     *
     * @return array{due: Collection<int, int>, overdue: Collection<int, int>}
     */
    private function alreadyReminded(Child $child): array
    {
        $previous = $child->parent->notifications()
            ->whereIn('type', [DosesDueSoon::class, DosesOverdue::class])
            ->get(['type', 'data'])
            ->filter(fn ($n) => ($n->data['child_id'] ?? null) === $child->id);

        $ids = fn (string $type) => $previous->where('type', $type)->flatMap(fn ($n) => $n->data['child_dose_ids'] ?? [])->values();

        return ['due' => $ids(DosesDueSoon::class), 'overdue' => $ids(DosesOverdue::class)];
    }
}
