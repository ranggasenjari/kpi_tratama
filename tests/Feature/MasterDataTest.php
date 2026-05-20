<?php

namespace Tests\Feature;

use App\Models\JobRole;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_master_data_records(): void
    {
        $this->seed();

        $admin = User::where('nik', 'ADM001')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/master-data/outlets', [
                'code' => 'tc04',
                'name' => 'Tratama Center 04',
                'address' => 'Jalan Uji Master Data',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $outlet = Outlet::where('code', 'TC04')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/master-data/job-roles', [
                'name' => 'Kasir',
                'code' => 'kasir',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $jobRole = JobRole::where('code', 'kasir')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/master-data/employees', [
                'name' => 'Karyawan Baru',
                'nik' => 'emp999',
                'email' => 'emp999@tratama.local',
                'password' => 'secret123',
                'role' => 'karyawan',
                'outlet_id' => $outlet->id,
                'job_role_id' => $jobRole->id,
                'phone' => '08123456789',
                'position_title' => 'Kasir Outlet',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $employee = User::where('nik', 'EMP999')->firstOrFail();

        $this->assertSame($outlet->id, $employee->outlet_id);
        $this->assertSame($jobRole->id, $employee->job_role_id);
        $this->assertTrue(Hash::check('secret123', $employee->password));
    }

    public function test_admin_can_toggle_master_data_statuses(): void
    {
        $this->seed();

        $admin = User::where('nik', 'ADM001')->firstOrFail();
        $outlet = Outlet::firstOrFail();
        $jobRole = JobRole::firstOrFail();
        $employee = User::where('role', 'karyawan')->firstOrFail();

        $this->actingAs($admin)->patch("/admin/master-data/outlets/{$outlet->id}/toggle")->assertRedirect();
        $this->actingAs($admin)->patch("/admin/master-data/job-roles/{$jobRole->id}/toggle")->assertRedirect();
        $this->actingAs($admin)->patch("/admin/master-data/employees/{$employee->id}/toggle")->assertRedirect();

        $this->assertFalse($outlet->fresh()->is_active);
        $this->assertFalse($jobRole->fresh()->is_active);
        $this->assertFalse($employee->fresh()->is_active);
    }
}
