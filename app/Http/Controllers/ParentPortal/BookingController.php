<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Hospital;
use App\Models\TimeSlot;
use App\Services\BookingOptions;
use App\Services\ScheduledDose;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The booking steps: child → doses → hospital → slot → confirm.
 * Choices travel in the query string; every step re-checks them, and the final
 * POST (AppointmentController@store) re-checks everything under locks.
 */
class BookingController extends Controller
{
    /** Step 1: choose a child. Skipped when the parent has only one. */
    public function children(Request $request): View|RedirectResponse
    {
        $children = $request->user()->children()->orderBy('date_of_birth', 'desc')->get();

        if ($children->count() === 1) {
            return redirect()->route('parent.book.doses', $children->first());
        }

        return view('parent.book.children', [
            'children' => $children->map(fn (Child $child) => [
                'child' => $child,
                'bookable' => (new BookingOptions($child))->bookableDoses()->whereNull('appointment')->count(),
            ]),
        ]);
    }

    /** Step 2: choose which due doses to book together. */
    public function doses(Child $child): View
    {
        Gate::authorize('book', $child);

        $rows = (new BookingOptions($child))->bookableDoses();

        return view('parent.book.doses', [
            'child' => $child,
            'rows' => $rows,
            'preselected' => $rows->whereNull('appointment')
                ->filter(fn (array $row) => $row['dose']->status->needsAction())
                ->map(fn (array $row) => $row['dose']->dose->id)
                ->all(),
        ]);
    }

    /** Step 3: choose a hospital that has every chosen vaccine and a free slot. */
    public function hospitals(Request $request, Child $child): View|RedirectResponse
    {
        Gate::authorize('book', $child);

        $options = new BookingOptions($child);
        $doses = $this->selectedOrFail($request, $options);
        if ($doses instanceof RedirectResponse) {
            return $doses;
        }

        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        return view('parent.book.hospitals', [
            'child' => $child,
            'doses' => $doses,
            'hospitals' => $options->hospitals($doses, $search),
            'search' => $search,
            'window' => $options->window($doses),
        ]);
    }

    /** Step 4: choose a time. */
    public function slots(Request $request, Child $child, Hospital $hospital): View|RedirectResponse
    {
        Gate::authorize('book', $child);
        abort_unless($hospital->isActive(), 404);

        $options = new BookingOptions($child);
        $doses = $this->selectedOrFail($request, $options);
        if ($doses instanceof RedirectResponse) {
            return $doses;
        }

        $slots = $options->slots($hospital, $doses);

        return view('parent.book.slots', [
            'child' => $child,
            'hospital' => $hospital,
            'doses' => $doses,
            'byDate' => $slots->groupBy(fn (TimeSlot $slot) => $slot->date->toDateString()),
        ]);
    }

    /** Step 5: check and confirm. */
    public function confirm(Request $request, Child $child): View|RedirectResponse
    {
        Gate::authorize('book', $child);

        $options = new BookingOptions($child);
        $doses = $this->selectedOrFail($request, $options);
        if ($doses instanceof RedirectResponse) {
            return $doses;
        }

        $slot = TimeSlot::with('hospital')->find($request->integer('slot'));

        if (! $slot || ! $slot->hospital?->isActive() || $slot->hasStarted()) {
            return redirect()->route('parent.book.doses', $child)->with('error', 'That time is no longer available. Please choose again.');
        }

        return view('parent.book.confirm', ['child' => $child, 'doses' => $doses, 'slot' => $slot]);
    }

    /**
     * @return Collection<int, ScheduledDose>|RedirectResponse
     */
    private function selectedOrFail(Request $request, BookingOptions $options): Collection|RedirectResponse
    {
        $ids = (array) $request->query('doses', []);
        $doses = $options->selected($ids);

        if ($doses->isEmpty() || $doses->count() !== count(array_unique($ids))) {
            return redirect()->route('parent.book.doses', $request->route('child'))
                ->with('error', 'Choose one or more vaccines that can be booked.');
        }

        return $doses;
    }
}
