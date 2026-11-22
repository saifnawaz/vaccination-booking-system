<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DoseSource;
use App\Enums\DoseStatus;
use App\Enums\HospitalStatus;
use App\Models\Appointment;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Hospital;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * System-wide numbers for the admin dashboard. Everything is counted from the
 * database (or worked out from it, for schedule statuses); nothing is estimated.
 */
final class AdminStats
{
    private CarbonImmutable $today;

    /** @var Collection<int, ScheduledDose>|null */
    private ?Collection $overdue = null;

    public function __construct(?CarbonImmutable $today = null)
    {
        $this->today = $today ?? CarbonImmutable::today();
    }

    public function childrenRegistered(): int
    {
        return Child::count();
    }

    /**
     * Doses given through the app this calendar month (historical records excluded:
     * they were given earlier, elsewhere).
     */
    public function dosesGivenThisMonth(): int
    {
        return ChildDose::where('source', DoseSource::GivenHere)
            ->whereBetween('administered_at', [$this->today->startOfMonth()->toDateString(), $this->today->endOfMonth()->toDateString()])
            ->count();
    }

    public function overdueDoses(): int
    {
        return $this->overdue()->count();
    }

    public function pendingApprovals(): int
    {
        return Hospital::where('status', HospitalStatus::Pending)->count();
    }

    /**
     * Stock warnings across active hospitals.
     *
     * @return array{low: int, expiring: int, expired: int, total: int}
     */
    public function stockWarnings(): array
    {
        $summaries = Hospital::active()->get()->map(fn (Hospital $h) => new StockSummary($h, $this->today));

        $low = $summaries->sum(fn (StockSummary $s) => $s->lowStockCount());
        $expiring = $summaries->sum(fn (StockSummary $s) => $s->expiringBatchCount());
        $expired = $summaries->sum(fn (StockSummary $s) => $s->expiredBatchCount());

        return ['low' => $low, 'expiring' => $expiring, 'expired' => $expired, 'total' => $low + $expiring + $expired];
    }

    /**
     * Doses given through the app in each of the last $months months, oldest first.
     *
     * @return list<array{label: string, value: int}>
     */
    public function dosesGivenByMonth(int $months = 6): array
    {
        $start = $this->today->startOfMonth()->subMonths($months - 1);

        $counts = ChildDose::where('source', DoseSource::GivenHere)
            ->whereDate('administered_at', '>=', $start->toDateString())
            ->whereDate('administered_at', '<=', $this->today->toDateString())
            ->selectRaw("DATE_FORMAT(administered_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(0, $months - 1))
            ->map(fn (int $i) => $start->addMonths($i))
            ->map(fn (CarbonImmutable $m) => ['label' => $m->format('M Y'), 'value' => (int) ($counts[$m->format('Y-m')] ?? 0)])
            ->all();
    }

    /**
     * Overdue doses per vaccine, most first.
     *
     * @return list<array{label: string, value: int}>
     */
    public function overdueByVaccine(): array
    {
        return $this->overdue()
            ->countBy(fn (ScheduledDose $d) => $d->schedule()->vaccine->name)
            ->sortDesc()
            ->map(fn (int $count, string $name) => ['label' => $name, 'value' => $count])
            ->values()
            ->all();
    }

    /**
     * Appointments with a slot this month, per status.
     *
     * @return list<array{status: AppointmentStatus, label: string, value: int}>
     */
    public function appointmentsThisMonthByStatus(): array
    {
        $counts = Appointment::query()
            ->join('time_slots', 'time_slots.id', '=', 'appointments.time_slot_id')
            ->whereBetween('time_slots.date', [$this->today->startOfMonth()->toDateString(), $this->today->endOfMonth()->toDateString()])
            ->selectRaw('appointments.status, count(*) as total')
            ->groupBy('appointments.status')
            ->pluck('total', 'status');

        return collect(AppointmentStatus::cases())
            ->map(fn (AppointmentStatus $s) => ['status' => $s, 'label' => $s->label(), 'value' => (int) ($counts[$s->value] ?? 0)])
            ->all();
    }

    /**
     * Every overdue dose in the system. Statuses are worked out from dates, so this
     * walks each child's schedule (fine at this scale; see DECISIONS.md).
     *
     * @return Collection<int, ScheduledDose>
     */
    private function overdue(): Collection
    {
        return $this->overdue ??= Child::with('doses.scheduleDose.vaccine', 'doses.administeredBy')->get()
            ->flatMap(fn (Child $child) => $child->schedule($this->today)->doses())
            ->filter(fn (ScheduledDose $d) => $d->status === DoseStatus::Overdue)
            ->values();
    }
}
