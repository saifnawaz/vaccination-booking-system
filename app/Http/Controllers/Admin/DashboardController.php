<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HospitalStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Hospital;
use App\Services\AdminStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = new AdminStats;

        return view('admin.dashboard', [
            'childrenRegistered' => $stats->childrenRegistered(),
            'dosesThisMonth' => $stats->dosesGivenThisMonth(),
            'overdueDoses' => $stats->overdueDoses(),
            'pendingHospitals' => $stats->pendingApprovals(),
            'stockWarnings' => $stats->stockWarnings(),
            'dosesByMonth' => $stats->dosesGivenByMonth(),
            'overdueByVaccine' => $stats->overdueByVaccine(),
            'appointmentsByStatus' => $stats->appointmentsThisMonthByStatus(),
            'awaitingApproval' => Hospital::with('user')
                ->where('status', HospitalStatus::Pending)
                ->oldest()
                ->limit(5)
                ->get(),
            'recentActivity' => ActivityLog::with('causer')->latest('id')->limit(6)->get(),
        ]);
    }
}
