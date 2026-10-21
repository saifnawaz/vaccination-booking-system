<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineRequest;
use App\Models\Vaccine;
use App\Models\VaccineSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VaccineController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Vaccine::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $vaccines = Vaccine::query()
            ->withCount('scheduleDoses')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.vaccines.index', ['vaccines' => $vaccines, 'filters' => $filters]);
    }

    public function create(): View
    {
        Gate::authorize('create', Vaccine::class);

        return view('admin.vaccines.create', ['vaccine' => new Vaccine(['is_active' => true])]);
    }

    public function store(VaccineRequest $request): RedirectResponse
    {
        Gate::authorize('create', Vaccine::class);

        $vaccine = Vaccine::create($request->validated());

        return redirect()->route('admin.vaccines.index')->with('success', "{$vaccine->name} has been added to the catalog.");
    }

    public function edit(Vaccine $vaccine): View
    {
        Gate::authorize('update', $vaccine);

        $vaccine->load('scheduleDoses');

        return view('admin.vaccines.edit', ['vaccine' => $vaccine]);
    }

    public function update(VaccineRequest $request, Vaccine $vaccine): RedirectResponse
    {
        Gate::authorize('update', $vaccine);

        $vaccine->update($request->validated());

        return redirect()->route('admin.vaccines.index')->with('success', "{$vaccine->name} has been saved.");
    }

    public function destroy(Vaccine $vaccine): RedirectResponse
    {
        Gate::authorize('update', $vaccine);

        if ($vaccine->isScheduled()) {
            return back()->with('error', "{$vaccine->name} is part of the routine schedule, so it can't be deleted. Deactivate it instead.");
        }

        Gate::authorize('delete', $vaccine);

        $vaccine->delete();

        return redirect()->route('admin.vaccines.index')->with('success', "{$vaccine->name} has been deleted.");
    }

    /**
     * The routine schedule, read-only. It is maintained in config/epi.php.
     */
    public function schedule(): View
    {
        Gate::authorize('viewAny', Vaccine::class);

        $doses = VaccineSchedule::with('vaccine')->orderBy('age_days')->orderBy('vaccine_id')->orderBy('dose_number')->get();

        return view('admin.vaccines.schedule', [
            'byAge' => $doses->groupBy(fn (VaccineSchedule $dose) => VaccineSchedule::ageLabel($dose->age_days)),
        ]);
    }
}
