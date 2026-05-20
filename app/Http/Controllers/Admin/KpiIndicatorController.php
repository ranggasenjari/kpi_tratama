<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\JobRole;
use App\Models\KpiIndicator;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KpiIndicatorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Kpi', [
            'indicators' => KpiIndicator::with(['template', 'assignments'])->orderBy('name')->get(),
            'templates' => FormTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name', 'category']),
            'outlets' => Outlet::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'jobRoles' => JobRole::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'types' => [
                ['value' => 'frequency', 'label' => 'Frekuensi submit'],
                ['value' => 'binary_compliance', 'label' => 'Kepatuhan ya/tidak'],
                ['value' => 'quantity_reward', 'label' => 'Unit + reward'],
                ['value' => 'fixed_threshold_reward', 'label' => 'Target + reward tetap'],
                ['value' => 'manual_score', 'label' => 'Skor manual'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'form_template_id' => ['nullable', 'exists:form_templates,id'],
            'type' => ['required', 'in:frequency,binary_compliance,quantity_reward,fixed_threshold_reward,manual_score'],
            'period' => ['required', 'in:daily,weekly,monthly'],
            'target' => ['required', 'numeric', 'min:0.01'],
            'weight' => ['required', 'numeric', 'min:0.01'],
            'reward_amount' => ['nullable', 'numeric', 'min:0'],
            'reward_unit' => ['nullable', 'string', 'max:50'],
            'allow_overachievement' => ['boolean'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'job_role_id' => ['nullable', 'exists:job_roles,id'],
        ]);

        $indicator = KpiIndicator::create([
            ...collect($data)->except(['outlet_id', 'job_role_id'])->all(),
            'code' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'reward_amount' => $data['reward_amount'] ?? 0,
            'allow_overachievement' => $data['allow_overachievement'] ?? false,
            'is_active' => true,
        ]);

        $indicator->assignments()->create([
            'outlet_id' => $data['outlet_id'] ?? null,
            'job_role_id' => $data['job_role_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Indikator KPI berhasil dibuat.');
    }

    public function toggle(KpiIndicator $indicator): RedirectResponse
    {
        $indicator->update(['is_active' => ! $indicator->is_active]);

        return back()->with('success', 'Status indikator diperbarui.');
    }
}
