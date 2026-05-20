<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobRole;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/MasterData', [
            'outlets' => Outlet::query()
                ->withCount('users')
                ->orderBy('code')
                ->get(),
            'jobRoles' => JobRole::query()
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'employees' => User::query()
                ->with(['outlet', 'jobRole'])
                ->orderBy('role')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nik' => $user->nik,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'position_title' => $user->position_title,
                    'is_active' => $user->is_active,
                    'outlet' => $user->outlet?->only(['id', 'code', 'name']),
                    'job_role' => $user->jobRole?->only(['id', 'code', 'name']),
                ]),
        ]);
    }

    public function storeOutlet(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $code = Str::upper($data['code']);

        if (Outlet::where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Kode outlet sudah digunakan.',
            ]);
        }

        Outlet::create([
            ...$data,
            'code' => $code,
            'is_active' => true,
        ]);

        return back()->with('success', 'Outlet baru berhasil ditambahkan.');
    }

    public function toggleOutlet(Outlet $outlet): RedirectResponse
    {
        $outlet->update(['is_active' => ! $outlet->is_active]);

        return back()->with('success', 'Status outlet diperbarui.');
    }

    public function storeJobRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $code = Str::slug($data['code'] ?: $data['name']);

        if (JobRole::where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Kode jabatan sudah digunakan.',
            ]);
        }

        JobRole::create([
            'name' => $data['name'],
            'code' => $code,
            'is_active' => true,
        ]);

        return back()->with('success', 'Jabatan baru berhasil ditambahkan.');
    }

    public function toggleJobRole(JobRole $jobRole): RedirectResponse
    {
        $jobRole->update(['is_active' => ! $jobRole->is_active]);

        return back()->with('success', 'Status jabatan diperbarui.');
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:karyawan,admin,owner'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'job_role_id' => ['nullable', 'exists:job_roles,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position_title' => ['nullable', 'string', 'max:255'],
        ]);

        $nik = Str::upper($data['nik']);

        if (User::where('nik', $nik)->exists()) {
            throw ValidationException::withMessages([
                'nik' => 'NIK sudah digunakan.',
            ]);
        }

        User::create([
            ...$data,
            'nik' => $nik,
            'password' => Hash::make($data['password'] ?: 'password'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Employee baru berhasil ditambahkan.');
    }

    public function toggleEmployee(User $employee): RedirectResponse
    {
        $employee->update(['is_active' => ! $employee->is_active]);

        return back()->with('success', 'Status employee diperbarui.');
    }
}
