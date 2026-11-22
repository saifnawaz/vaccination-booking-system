<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HospitalStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hospitalCounts = Hospital::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'pendingHospitals' => (int) $hospitalCounts->get(HospitalStatus::Pending->value, 0),
            'activeHospitals' => (int) $hospitalCounts->get(HospitalStatus::Active->value, 0),
            'inactiveHospitals' => (int) $hospitalCounts->get(HospitalStatus::Inactive->value, 0),
            'parents' => User::where('role', Role::Parent)->count(),
            'awaitingApproval' => Hospital::with('user')
                ->where('status', HospitalStatus::Pending)
                ->oldest()
                ->limit(5)
                ->get(),
        ]);
    }
}
