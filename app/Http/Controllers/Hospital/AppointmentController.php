<?php

namespace App\Http\Controllers\Hospital;

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ChildDose;
use App\Services\AppointmentService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['open', ...array_column(AppointmentStatus::cases(), 'value')])],
            'date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $status = $filters['status'] ?? 'open';

        $appointments = $request->user()->hospital->appointments()
            ->with('child', 'timeSlot', 'childDose.scheduleDose.vaccine', 'hospitalStock')
            ->join('time_slots', 'time_slots.id', '=', 'appointments.time_slot_id')
            ->select('appointments.*')
            ->when($status === 'open', fn ($q) => $q->open(), fn ($q) => $q->where('appointments.status', $status))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('time_slots.date', $date))
            // Grouped, so the OR can't escape the child relation's join condition.
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('child', fn ($q) => $q->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%"))))
            ->orderBy('time_slots.date', $status === 'open' ? 'asc' : 'desc')
            ->orderBy('time_slots.start_time')
            ->orderBy('appointments.child_id')
            ->paginate(25)
            ->withQueryString();

        return view('hospital.appointments.index', [
            'appointments' => $appointments,
            'filters' => [...$filters, 'status' => $status],
            'pendingCount' => $request->user()->hospital->appointments()->where('status', AppointmentStatus::Pending)->count(),
        ]);
    }

    public function show(Appointment $appointment): View
    {
        Gate::authorize('manage', $appointment);

        $appointment->load('child.parent', 'timeSlot', 'childDose.scheduleDose.vaccine', 'hospitalStock');
        $schedule = $appointment->child->schedule();

        return view('hospital.appointments.show', [
            'appointment' => $appointment,
            'schedule' => $schedule,
            'visit' => Appointment::where('child_id', $appointment->child_id)
                ->where('time_slot_id', $appointment->time_slot_id)
                ->whereKeyNot($appointment->id)
                ->with('childDose.scheduleDose')
                ->get(),
            // Earlier doses the hospital may copy from the child's card.
            'recordable' => $schedule->doses()
                ->reject(fn ($d) => $d->dose->isAdministered())
                ->filter(fn ($d) => $d->schedule()->age_days <= $appointment->child->date_of_birth->diffInDays(today()))
                ->values(),
        ]);
    }

    public function approve(Appointment $appointment): RedirectResponse
    {
        return $this->act($appointment, fn () => $this->appointments->approve($appointment), 'confirmed');
    }

    public function reject(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('manage', $appointment);

        $reason = $request->validateWithBag('reject'.$appointment->id, [
            'reason' => ['required', 'string', 'max:500'],
        ], ['reason.required' => 'Tell the parent why, so they can book again.'])['reason'];

        return $this->act($appointment, fn () => $this->appointments->reject($appointment, $reason), 'declined');
    }

    public function complete(Appointment $appointment): RedirectResponse
    {
        return $this->act($appointment, fn () => $this->appointments->complete($appointment), 'recorded as given');
    }

    public function noShow(Appointment $appointment): RedirectResponse
    {
        return $this->act($appointment, fn () => $this->appointments->markNoShow($appointment), 'marked as missed');
    }

    /**
     * Copy a dose from the child's previous records (e.g. their vaccination card).
     */
    public function recordHistory(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('manage', $appointment);

        $validated = $request->validateWithBag('history', [
            'child_dose_id' => ['required', 'integer'],
            'given_on' => ['required', 'date', 'before_or_equal:today'],
            'batch_number' => ['nullable', 'string', 'max:50'],
        ]);

        $dose = ChildDose::where('child_id', $appointment->child_id)->findOrFail($validated['child_dose_id']);

        try {
            $this->appointments->recordHistoricalDose(
                $dose,
                $request->user()->hospital,
                CarbonImmutable::parse($validated['given_on']),
                $validated['batch_number'] ?? null,
            );
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$dose->scheduleDose->label} has been recorded from previous records.");
    }

    private function act(Appointment $appointment, callable $action, string $done): RedirectResponse
    {
        Gate::authorize('manage', $appointment);

        try {
            $action();
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        $appointment->load('child', 'childDose.scheduleDose');

        return back()->with('success', "{$appointment->doseLabel()} for {$appointment->child->fullName()} has been {$done}.");
    }
}
