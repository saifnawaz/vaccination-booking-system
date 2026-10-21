<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockBatchRequest;
use App\Models\HospitalStock;
use App\Models\Vaccine;
use App\Services\StockSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', HospitalStock::class);

        $hospital = $request->user()->hospital;

        $filters = $request->validate([
            'vaccine' => ['nullable', 'integer'],
            'condition' => ['nullable', 'in:expired,expiring,usable'],
            'search' => ['nullable', 'string', 'max:50'],
        ]);

        $soon = today()->addDays(config('vaccination.stock.expiring_soon_days'));

        $batches = $hospital->stock()
            ->with('vaccine')
            ->when($filters['vaccine'] ?? null, fn ($query, $id) => $query->where('vaccine_id', $id))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('batch_number', 'like', "%{$search}%"))
            ->when($filters['condition'] ?? null, fn ($query, $condition) => match ($condition) {
                'expired' => $query->whereDate('expiry_date', '<', today()),
                'expiring' => $query->whereDate('expiry_date', '>=', today())->whereDate('expiry_date', '<=', $soon),
                'usable' => $query->whereDate('expiry_date', '>=', today())->whereColumn('quantity', '>', 'reserved'),
            })
            ->orderBy('expiry_date')
            ->paginate(20)
            ->withQueryString();

        return view('hospital.stock.index', [
            'summary' => new StockSummary($hospital),
            'batches' => $batches,
            'filters' => $filters,
            'vaccines' => Vaccine::whereIn('id', $hospital->stock()->select('vaccine_id'))->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', HospitalStock::class);

        return view('hospital.stock.create', [
            'vaccines' => Vaccine::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StockBatchRequest $request): RedirectResponse
    {
        Gate::authorize('create', HospitalStock::class);

        $batch = new HospitalStock($request->validated());
        $batch->hospital_id = $request->user()->hospital->id;
        $batch->save();

        return redirect()->route('hospital.stock.index')
            ->with('success', "Batch {$batch->batch_number} ({$batch->quantity} doses of {$batch->vaccine->name}) has been added.");
    }

    public function edit(HospitalStock $batch): View
    {
        Gate::authorize('update', $batch);

        return view('hospital.stock.edit', ['batch' => $batch->load('vaccine')]);
    }

    /**
     * Correct the number of doses on hand (a recount, wastage, breakage).
     * It can't go below the doses already reserved for bookings.
     */
    public function update(Request $request, HospitalStock $batch): RedirectResponse
    {
        Gate::authorize('update', $batch);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', "min:{$batch->reserved}", 'max:100000'],
        ], [
            'quantity.min' => "{$batch->reserved} doses of this batch are reserved for bookings, so the quantity can't be lower than that.",
        ]);

        $batch->quantity = $validated['quantity'];
        $batch->save();

        return redirect()->route('hospital.stock.index')
            ->with('success', "Batch {$batch->batch_number} now has {$batch->quantity} doses.");
    }

    public function destroy(HospitalStock $batch): RedirectResponse
    {
        Gate::authorize('delete', $batch);

        if (! $batch->isRemovable()) {
            return back()->with('error', "Batch {$batch->batch_number} has been used or reserved, so it can't be removed. Set its quantity to 0 instead.");
        }

        $batch->delete();

        return redirect()->route('hospital.stock.index')->with('success', "Batch {$batch->batch_number} has been removed.");
    }
}
