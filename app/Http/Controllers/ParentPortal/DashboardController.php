<?php

namespace App\Http\Controllers\ParentPortal;

use App\Enums\DoseStatus;
use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $children = $request->user()->children()
            ->with('doses.scheduleDose.vaccine', 'doses.hospitalStock.hospital')
            ->orderBy('date_of_birth', 'desc')
            ->get();

        $schedules = $children->mapWithKeys(fn (Child $child) => [$child->id => $child->schedule()]);

        return view('parent.dashboard', [
            'user' => $request->user(),
            'children' => $children,
            'schedules' => $schedules,
            'overdue' => $schedules->sum(fn ($s) => $s->counts()[DoseStatus::Overdue->value]),
            'due' => $schedules->sum(fn ($s) => $s->counts()[DoseStatus::Due->value]),
        ]);
    }
}
