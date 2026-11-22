<?php

namespace App\Http\Controllers\ParentPortal;

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Child;
use App\Models\TimeSlot;
use App\Services\AppointmentService;
use App\Services\BookingOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $base = Appointment::query()
            ->whereIn('child_id', $request->user()->children()->withTrashed()->select('id'))
            ->with('child', 'hospital', 'timeSlot', 'childDose.scheduleDose.vaccine')
            ->join('time_slots', 'time_slots.id', '=', 'appointments.time_slot_id')
            ->select('appointments.*');

        $upcoming = (clone $base)->open()
            ->orderBy('time_slots.date')->orderBy('time_slots.start_time')
            ->get();

        $filters = $request->validate([
            'child' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:completed,cancelled,rejected,no_show'],
        ]);

        $past = (clone $base)->whereNotIn('appointments.status', AppointmentStatus::OPEN)
            ->when($filters['child'] ?? null, fn ($q, $id) => $q->where('appointments.child_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('appointments.status', $status))
            ->orderByDesc('time_slots.date')->orderByDesc('time_slots.start_time')
            ->paginate(15)
            ->withQueryString();

        return view('parent.appointments.index', [
            // A visit = one child in one slot; it may cover several vaccines.
            'visits' => $upcoming->groupBy(fn (Appointment $a) => $a->child_id.'-'.$a->time_slot_id),
            'past' => $past,
            'filters' => $filters,
            'children' => $request->user()->children()->orderBy('first_name')->get(['id', 'first_name', 'last_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'child' => ['required', 'integer'],
            'slot' => ['required', 'integer'],
            'doses' => ['required', 'array', 'min:1'],
            'doses.*' => ['integer'],
        ]);

        $child = Child::findOrFail($validated['child']);
        Gate::authorize('book', $child);
        $slot = TimeSlot::findOrFail($validated['slot']);

        try {
            $booked = $this->appointments->book($child, $validated['doses'], $slot);
        } catch (BookingException $e) {
            return redirect()->route('parent.book.doses', $child)->with('error', $e->getMessage());
        }

        return redirect()->route('parent.appointments.index')->with('success',
            "Booked {$booked->count()} ".str('vaccine')->plural($booked->count())." for {$child->first_name} on "
            .$slot->date->format('l j F').' at '.TimeSlot::formatTime($slot->start_time)
            .'. The hospital will confirm it.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('change', $appointment);

        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:500']])['reason'] ?? null;

        try {
            $this->appointments->cancel($appointment, $reason);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('parent.appointments.index')
            ->with('success', "{$appointment->doseLabel()} for {$appointment->child->first_name} has been cancelled.");
    }

    public function editSchedule(Appointment $appointment): View|RedirectResponse
    {
        Gate::authorize('change', $appointment);

        if (! $appointment->canBeChangedByParent()) {
            return redirect()->route('parent.appointments.index')
                ->with('error', 'This appointment can no longer be changed online. Please contact the hospital.');
        }

        $appointment->load('child', 'hospital', 'timeSlot', 'childDose.scheduleDose');
        $options = new BookingOptions($appointment->child);
        $dose = $options->schedule()->doses()->first(fn ($d) => $d->dose->id === $appointment->child_dose_id);

        $slots = $dose
            ? $options->slots($appointment->hospital, collect([$dose]))->reject(fn (TimeSlot $s) => $s->id === $appointment->time_slot_id)
            : collect();

        return view('parent.appointments.reschedule', [
            'appointment' => $appointment,
            'byDate' => $slots->groupBy(fn (TimeSlot $slot) => $slot->date->toDateString()),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('change', $appointment);

        $slot = TimeSlot::findOrFail($request->validate(['slot' => ['required', 'integer']])['slot']);

        try {
            $this->appointments->reschedule($appointment, $slot);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('parent.appointments.index')->with('success',
            "{$appointment->doseLabel()} has been moved to ".$slot->date->format('l j F').' at '
            .TimeSlot::formatTime($slot->start_time).'. The hospital will confirm the new time.');
    }
}
