<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkTimeSlotRequest;
use App\Http\Requests\TimeSlotRequest;
use App\Models\TimeSlot;
use App\Services\ActivityLogger;
use App\Services\SlotGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

class TimeSlotController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', TimeSlot::class);

        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? today()->toDateString();

        $slots = $request->user()->hospital->timeSlots()
            ->select('time_slots.*')
            ->withBookedChildren()
            ->whereDate('date', '>=', $from)
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('date', '<=', $to))
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(40)
            ->withQueryString();

        return view('hospital.slots.index', [
            'slots' => $slots,
            'byDate' => $slots->getCollection()->groupBy(fn (TimeSlot $slot) => $slot->date->toDateString()),
            'filters' => ['from' => $from, 'to' => $filters['to'] ?? null],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', TimeSlot::class);

        return view('hospital.slots.create');
    }

    public function store(TimeSlotRequest $request): RedirectResponse
    {
        Gate::authorize('create', TimeSlot::class);

        $slot = new TimeSlot($request->validated());
        $slot->hospital_id = $request->user()->hospital->id;
        $slot->save();

        return redirect()->route('hospital.slots.index', ['from' => $slot->date->toDateString()])
            ->with('success', 'Slot added for '.$slot->date->format('D j M').', '.$slot->timeRange().'.');
    }

    public function bulkCreate(): View
    {
        Gate::authorize('create', TimeSlot::class);

        return view('hospital.slots.bulk', [
            'lengths' => config('vaccination.slots.lengths'),
            'maxDays' => config('vaccination.slots.bulk_max_range_days'),
        ]);
    }

    public function bulkStore(BulkTimeSlotRequest $request, SlotGenerator $generator): RedirectResponse
    {
        Gate::authorize('create', TimeSlot::class);

        try {
            $result = $generator->generate(
                $request->user()->hospital,
                CarbonImmutable::parse($request->validated('date_from')),
                CarbonImmutable::parse($request->validated('date_to')),
                array_map('intval', $request->validated('weekdays')),
                $request->validated('start_time'),
                $request->validated('end_time'),
                (int) $request->validated('slot_minutes'),
                (int) $request->validated('capacity'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['date_to' => $e->getMessage()]);
        }

        $message = "Created {$result['created']} ".str('slot')->plural($result['created']).'.';
        if ($result['skipped'] > 0) {
            $message .= " Skipped {$result['skipped']} that overlapped existing slots or had already passed.";
        }

        return redirect()->route('hospital.slots.index', ['from' => $request->validated('date_from')])
            ->with($result['created'] > 0 ? 'success' : 'error', $message);
    }

    public function edit(TimeSlot $slot): View|RedirectResponse
    {
        Gate::authorize('update', $slot);

        if ($slot->date->isBefore(today())) {
            return redirect()->route('hospital.slots.index')->with('error', 'Past slots cannot be changed.');
        }

        return view('hospital.slots.edit', ['slot' => $slot]);
    }

    /**
     * Only the capacity can change; to move a slot, delete it and add a new one.
     */
    public function update(Request $request, TimeSlot $slot): RedirectResponse
    {
        Gate::authorize('update', $slot);

        if ($slot->date->isBefore(today())) {
            return redirect()->route('hospital.slots.index')->with('error', 'Past slots cannot be changed.');
        }

        $booked = $slot->bookedChildren();

        $validated = $request->validate([
            'capacity' => ['required', 'integer', 'min:'.max(1, $booked), 'max:'.config('vaccination.slots.max_capacity')],
        ], [
            'capacity.min' => $booked > 0
                ? "{$booked} ".str('child')->plural($booked).' already booked, so the capacity can\'t be lower than that.'
                : 'The capacity must be at least 1.',
        ]);

        $slot->update($validated);

        return redirect()->route('hospital.slots.index', ['from' => $slot->date->toDateString()])
            ->with('success', 'Capacity updated to '.$slot->capacity.' for '.$slot->date->format('D j M').', '.$slot->timeRange().'.');
    }

    public function destroy(TimeSlot $slot): RedirectResponse
    {
        Gate::authorize('delete', $slot);

        if ($slot->date->isBefore(today())) {
            return back()->with('error', 'Past slots cannot be removed.');
        }

        if ($slot->appointments()->exists()) {
            return back()->with('error', $slot->appointments()->open()->exists()
                ? 'Parents have booked this slot. Decline their appointments first if you need to close it.'
                : 'This slot has appointment history, so it can\'t be removed.');
        }

        $slot->delete();
        app(ActivityLogger::class)->log('slot.removed', 'Removed slot '.$slot->date->format('j M Y').', '.$slot->timeRange()." at {$slot->hospital->name}", $slot);

        return back()->with('success', 'Slot on '.$slot->date->format('D j M').', '.$slot->timeRange().' has been removed.');
    }
}
