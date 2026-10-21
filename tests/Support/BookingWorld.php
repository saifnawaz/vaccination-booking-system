<?php

namespace Tests\Support;

use App\Models\Appointment;
use App\Models\Child;
use App\Models\ChildDose;
use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\Vaccine;
use App\Services\AppointmentService;
use App\Services\ScheduleGenerator;
use Database\Seeders\EpiScheduleSeeder;
use Illuminate\Support\Collection;

/**
 * A small booking world for tests: the EPI schedule, a parent with a child of a
 * chosen age, and an active hospital with 20 doses of every vaccine (one batch
 * each, expiring in 180 days). Call travelTo() first if dates matter.
 */
final class BookingWorld
{
    public User $parent;

    public Child $child;

    public Hospital $hospital;

    public AppointmentService $service;

    public static function create(int $childAgeDays = 49): self
    {
        $world = new self;
        (new EpiScheduleSeeder)->run();

        $world->service = app(AppointmentService::class);
        $world->parent = User::factory()->parent()->create();
        $world->child = $world->childAged($childAgeDays);
        $world->hospital = Hospital::factory()->active()->create();
        $world->stockEverything($world->hospital);

        return $world;
    }

    public function childAged(int $days, ?User $parent = null): Child
    {
        $child = Child::factory()->for($parent ?? $this->parent, 'parent')->bornDaysAgo($days)->create();
        (new ScheduleGenerator)->generate($child);

        return $child->fresh();
    }

    public function stockEverything(Hospital $hospital, int $quantity = 20, int $expiresInDays = 180): void
    {
        Vaccine::all()->each(fn (Vaccine $vaccine) => HospitalStock::factory()->create([
            'hospital_id' => $hospital->id,
            'vaccine_id' => $vaccine->id,
            'batch_number' => $vaccine->code.'-A',
            'quantity' => $quantity,
            'expiry_date' => now()->addDays($expiresInDays)->toDateString(),
        ]));
    }

    public function slot(int $daysAhead = 3, string $start = '09:00', int $capacity = 4, ?Hospital $hospital = null): TimeSlot
    {
        return TimeSlot::factory()->create([
            'hospital_id' => ($hospital ?? $this->hospital)->id,
            'date' => now()->addDays($daysAhead)->toDateString(),
            'start_time' => $start.':00',
            'end_time' => date('H:i:s', strtotime($start) + 1800),
            'capacity' => $capacity,
        ]);
    }

    public function dose(string $label, ?Child $child = null): ChildDose
    {
        return ($child ?? $this->child)->doses()
            ->whereHas('scheduleDose', fn ($q) => $q->where('label', $label))
            ->sole();
    }

    /**
     * @param  list<string>  $labels
     * @return Collection<int, Appointment>
     */
    public function book(array $labels, TimeSlot $slot, ?Child $child = null): Collection
    {
        $child ??= $this->child;

        return $this->service->book($child, array_map(fn ($l) => $this->dose($l, $child)->id, $labels), $slot);
    }

    public function batch(string $vaccineCode, ?Hospital $hospital = null): HospitalStock
    {
        return HospitalStock::where('hospital_id', ($hospital ?? $this->hospital)->id)
            ->whereHas('vaccine', fn ($q) => $q->where('code', $vaccineCode))
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Invariant: every batch's reserved count equals its open appointments.
     */
    public static function reservationsAreConsistent(): bool
    {
        return HospitalStock::all()->every(fn (HospitalStock $batch) => $batch->reserved === $batch->appointments()->open()->count());
    }
}
