<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Services\StockSummary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $hospital = $request->user()->hospital;

        $upcomingSlots = $hospital->timeSlots()->whereDate('date', '>=', today());

        return view('hospital.dashboard', [
            'hospital' => $hospital,
            'stock' => new StockSummary($hospital),
            'upcomingSlotCount' => (clone $upcomingSlots)->count(),
            'nextSlotDate' => (clone $upcomingSlots)->min('date'),
        ]);
    }
}
