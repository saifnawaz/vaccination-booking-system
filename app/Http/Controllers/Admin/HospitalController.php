<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HospitalStatus;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HospitalController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Hospital::class);

        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(HospitalStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $hospitals = Hospital::query()
            ->with('user')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                });
            })
            // Pending first: those are the ones waiting on an admin.
            ->orderByRaw("status = 'pending' desc")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.hospitals.index', [
            'hospitals' => $hospitals,
            'filters' => $filters,
            'statuses' => HospitalStatus::cases(),
        ]);
    }

    public function show(Hospital $hospital): View
    {
        Gate::authorize('view', $hospital);

        $hospital->load('user', 'approver');

        return view('admin.hospitals.show', [
            'hospital' => $hospital,
            'openAppointments' => $hospital->appointments()->open()->count(),
        ]);
    }

    /**
     * Soft delete. Refused while parents have open appointments there.
     */
    public function destroy(Hospital $hospital): RedirectResponse
    {
        Gate::authorize('delete', $hospital);

        $open = $hospital->appointments()->open()->count();

        if ($open > 0) {
            return back()->with('error', "{$hospital->name} has {$open} open ".str('appointment')->plural($open)
                .'. Those must be completed, declined or cancelled before the hospital can be deleted.');
        }

        DB::transaction(function () use ($hospital) {
            $hospital->delete();
            $hospital->user->delete();
        });

        return redirect()->route('admin.hospitals.index')->with('success', "{$hospital->name} has been deleted.");
    }

    public function updateStatus(Request $request, Hospital $hospital): RedirectResponse
    {
        Gate::authorize('changeStatus', $hospital);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(HospitalStatus::class)],
        ]);

        $next = HospitalStatus::from($validated['status']);
        $previous = $hospital->status;

        if (! $previous->canTransitionTo($next)) {
            return back()->withErrors([
                'status' => "{$hospital->name} is {$previous->label()} and cannot be changed to {$next->label()}.",
            ]);
        }

        $hospital->transitionTo($next, $request->user());

        return back()->with('success', "{$hospital->name} has been {$previous->actionDoneLabel($next)}.");
    }
}
