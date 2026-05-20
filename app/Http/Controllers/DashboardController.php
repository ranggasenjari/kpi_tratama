<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\Submission;
use App\Models\User;
use App\Services\KpiCalculator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, KpiCalculator $calculator): Response
    {
        $user = $request->user();
        $month = now(config('app.timezone'))->format('Y-m');

        $stats = [
            'forms' => FormTemplate::where('is_active', true)->count(),
            'pending_reviews' => Submission::where('status', 'pending')->count(),
            'approved_this_month' => Submission::where('status', 'approved')
                ->whereBetween('submitted_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'employees' => User::where('role', 'karyawan')->count(),
        ];

        $report = $user->role === 'karyawan'
            ? $calculator->reportForUser($user, $month)
            : null;

        $leaderboard = $user->role !== 'karyawan'
            ? User::query()
                ->where('role', 'karyawan')
                ->where('is_active', true)
                ->get()
                ->map(fn (User $employee) => $calculator->reportForUser($employee, $month))
                ->sortByDesc('score')
                ->take(8)
                ->values()
            : [];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'month' => $month,
            'report' => $report,
            'leaderboard' => $leaderboard,
        ]);
    }
}
