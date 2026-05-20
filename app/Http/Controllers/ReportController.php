<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\KpiCalculator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, KpiCalculator $calculator): Response
    {
        $month = $request->input('month', now(config('app.timezone'))->format('Y-m'));
        $reports = $this->reports($calculator, $month);

        return Inertia::render('Reports/Index', [
            'month' => $month,
            'reports' => $reports,
            'summary' => [
                'employees' => $reports->count(),
                'average_score' => round($reports->avg('score') ?? 0, 2),
                'reward_total' => round($reports->sum('reward_total'), 2),
            ],
        ]);
    }

    public function export(Request $request, KpiCalculator $calculator): StreamedResponse
    {
        $month = $request->input('month', now(config('app.timezone'))->format('Y-m'));
        $reports = $this->reports($calculator, $month);

        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIK', 'Nama', 'Skor KPI', 'Reward']);

            foreach ($reports as $report) {
                fputcsv($handle, [
                    $report['user']['nik'],
                    $report['user']['name'],
                    $report['score'],
                    $report['reward_total'],
                ]);
            }

            fclose($handle);
        }, "laporan-kpi-{$month}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function reports(KpiCalculator $calculator, string $month)
    {
        return User::query()
            ->with(['outlet', 'jobRole'])
            ->where('role', 'karyawan')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $employee) => [
                ...$calculator->reportForUser($employee, $month),
                'outlet' => $employee->outlet?->code,
                'job_role' => $employee->jobRole?->name,
            ]);
    }
}
