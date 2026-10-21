<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public const AREAS = [
        'hospital' => 'Hospitals',
        'appointment' => 'Appointments',
        'vaccine' => 'Vaccines',
        'stock' => 'Stock',
        'slot' => 'Time slots',
        'dose' => 'Dose records',
        'child' => 'Children',
        'admin' => 'Administrators',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('viewActivity', ActivityLog::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'in:'.implode(',', array_keys(self::AREAS))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $entries = ActivityLog::query()
            ->with('causer')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('description', 'like', "%{$search}%")
                ->orWhereHas('causer', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->when($filters['area'] ?? null, fn ($q, $area) => $q->where('action', 'like', "{$area}.%"))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity.index', ['entries' => $entries, 'filters' => $filters, 'areas' => self::AREAS]);
    }
}
