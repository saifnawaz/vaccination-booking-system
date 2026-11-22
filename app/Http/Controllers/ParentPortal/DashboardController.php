<?php

namespace App\Http\Controllers\ParentPortal;

use App\Enums\DoseStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
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
            'upcoming' => Appointment::open()
                ->whereIn('child_id', $children->modelKeys())
                ->with('child', 'hospital', 'timeSlot', 'childDose.scheduleDose')
                ->get()
                ->sortBy(fn (Appointment $a) => $a->startsAt()->timestamp)
                ->groupBy(fn (Appointment $a) => $a->child_id.'-'.$a->time_slot_id)
                ->take(3),
        ]);
    }
}
