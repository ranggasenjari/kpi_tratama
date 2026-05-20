<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, IdCard, Plus, UserRoundCog } from '@lucide/vue';

defineProps({
    outlets: Array,
    jobRoles: Array,
    employees: Array,
});

const outletForm = useForm({
    code: '',
    name: '',
    address: '',
});

const jobRoleForm = useForm({
    name: '',
    code: '',
});

const employeeForm = useForm({
    name: '',
    nik: '',
    email: '',
    password: 'password',
    role: 'karyawan',
    outlet_id: '',
    job_role_id: '',
    phone: '',
    position_title: '',
});

function storeOutlet() {
    outletForm.post('/admin/master-data/outlets', {
        onSuccess: () => outletForm.reset(),
    });
}

function storeJobRole() {
    jobRoleForm.post('/admin/master-data/job-roles', {
        onSuccess: () => jobRoleForm.reset(),
    });
}

function storeEmployee() {
    employeeForm.post('/admin/master-data/employees', {
        onSuccess: () => employeeForm.reset('name', 'nik', 'email', 'phone', 'position_title'),
    });
}

function toggleOutlet(outlet) {
    router.patch(`/admin/master-data/outlets/${outlet.id}/toggle`);
}

function toggleJobRole(jobRole) {
    router.patch(`/admin/master-data/job-roles/${jobRole.id}/toggle`);
}

function toggleEmployee(employee) {
    router.patch(`/admin/master-data/employees/${employee.id}/toggle`);
}
</script>

<template>
    <AppLayout>
        <Head title="Master Data" />

        <div class="mb-6">
            <div class="kicker">Admin registry</div>
            <h1 class="page-title mt-1">Master Data</h1>
            <p class="page-subtitle mt-1">Kelola outlet, jabatan, dan employee yang dipakai untuk assignment KPI.</p>
        </div>

        <section class="grid gap-4 xl:grid-cols-3">
            <form class="material-card p-5" @submit.prevent="storeOutlet">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                        <Building2 class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-black text-slate-950">Outlet</h2>
                        <p class="text-sm font-medium text-slate-500">Tambah cabang atau lokasi kerja.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block">
                        <span class="field-label">Kode outlet</span>
                        <input v-model="outletForm.code" class="field-control mt-1.5" placeholder="TC04" />
                        <span v-if="outletForm.errors.code" class="text-xs font-semibold text-red-600">{{ outletForm.errors.code }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Nama outlet</span>
                        <input v-model="outletForm.name" class="field-control mt-1.5" placeholder="Tratama Center 04" />
                        <span v-if="outletForm.errors.name" class="text-xs font-semibold text-red-600">{{ outletForm.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Alamat</span>
                        <textarea v-model="outletForm.address" rows="3" class="field-control mt-1.5" />
                    </label>
                </div>

                <button type="submit" class="btn-primary mt-4 w-full" :disabled="outletForm.processing">
                    <Plus class="h-4 w-4" />
                    Tambah Outlet
                </button>
            </form>

            <form class="material-card p-5" @submit.prevent="storeJobRole">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                        <IdCard class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-black text-slate-950">Jabatan</h2>
                        <p class="text-sm font-medium text-slate-500">Tambah role pekerjaan untuk assignment.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block">
                        <span class="field-label">Nama jabatan</span>
                        <input v-model="jobRoleForm.name" class="field-control mt-1.5" placeholder="Kasir" />
                        <span v-if="jobRoleForm.errors.name" class="text-xs font-semibold text-red-600">{{ jobRoleForm.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Kode</span>
                        <input v-model="jobRoleForm.code" class="field-control mt-1.5" placeholder="kasir" />
                        <span v-if="jobRoleForm.errors.code" class="text-xs font-semibold text-red-600">{{ jobRoleForm.errors.code }}</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary mt-4 w-full" :disabled="jobRoleForm.processing">
                    <Plus class="h-4 w-4" />
                    Tambah Jabatan
                </button>
            </form>

            <form class="material-card p-5" @submit.prevent="storeEmployee">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <UserRoundCog class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="font-black text-slate-950">Employee</h2>
                        <p class="text-sm font-medium text-slate-500">Tambah akun untuk login dan penilaian.</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="field-label">Nama</span>
                        <input v-model="employeeForm.name" class="field-control mt-1.5" />
                        <span v-if="employeeForm.errors.name" class="text-xs font-semibold text-red-600">{{ employeeForm.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">NIK</span>
                        <input v-model="employeeForm.nik" class="field-control mt-1.5" />
                        <span v-if="employeeForm.errors.nik" class="text-xs font-semibold text-red-600">{{ employeeForm.errors.nik }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Role akses</span>
                        <select v-model="employeeForm.role" class="field-control mt-1.5">
                            <option value="karyawan">Karyawan</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="field-label">Email</span>
                        <input v-model="employeeForm.email" type="email" class="field-control mt-1.5" />
                        <span v-if="employeeForm.errors.email" class="text-xs font-semibold text-red-600">{{ employeeForm.errors.email }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Password awal</span>
                        <input v-model="employeeForm.password" type="password" class="field-control mt-1.5" />
                    </label>
                    <label class="block">
                        <span class="field-label">No. HP</span>
                        <input v-model="employeeForm.phone" class="field-control mt-1.5" />
                    </label>
                    <label class="block">
                        <span class="field-label">Outlet</span>
                        <select v-model="employeeForm.outlet_id" class="field-control mt-1.5">
                            <option value="">Tanpa outlet</option>
                            <option v-for="outlet in outlets.filter((item) => item.is_active)" :key="outlet.id" :value="outlet.id">
                                {{ outlet.code }} - {{ outlet.name }}
                            </option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="field-label">Jabatan</span>
                        <select v-model="employeeForm.job_role_id" class="field-control mt-1.5">
                            <option value="">Tanpa jabatan</option>
                            <option v-for="jobRole in jobRoles.filter((item) => item.is_active)" :key="jobRole.id" :value="jobRole.id">
                                {{ jobRole.name }}
                            </option>
                        </select>
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="field-label">Title posisi</span>
                        <input v-model="employeeForm.position_title" class="field-control mt-1.5" />
                    </label>
                </div>

                <button type="submit" class="btn-primary mt-4 w-full" :disabled="employeeForm.processing">
                    <Plus class="h-4 w-4" />
                    Tambah Employee
                </button>
            </form>
        </section>

        <section class="mt-6 grid gap-4 xl:grid-cols-2">
            <div class="table-shell">
                <div class="border-b border-slate-200 bg-white/80 px-5 py-4">
                    <h2 class="font-black text-slate-950">Daftar Outlet</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="outlet in outlets" :key="outlet.id">
                                <td class="px-4 py-3 font-black text-blue-700">{{ outlet.code }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ outlet.name }}</div>
                                    <div class="text-xs font-medium text-slate-500">{{ outlet.address || '-' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ outlet.users_count }}</td>
                                <td class="px-4 py-3">
                                    <button class="btn-secondary min-h-0 px-3 py-1 text-xs" @click="toggleOutlet(outlet)">
                                        {{ outlet.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-shell">
                <div class="border-b border-slate-200 bg-white/80 px-5 py-4">
                    <h2 class="font-black text-slate-950">Daftar Jabatan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Kode</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="jobRole in jobRoles" :key="jobRole.id">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ jobRole.name }}</td>
                                <td class="px-4 py-3">{{ jobRole.code }}</td>
                                <td class="px-4 py-3">{{ jobRole.users_count }}</td>
                                <td class="px-4 py-3">
                                    <button class="btn-secondary min-h-0 px-3 py-1 text-xs" @click="toggleJobRole(jobRole)">
                                        {{ jobRole.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="table-shell mt-6">
            <div class="border-b border-slate-200 bg-white/80 px-5 py-4">
                <h2 class="font-black text-slate-950">Daftar Employee</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Outlet</th>
                            <th class="px-4 py-3">Jabatan</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees" :key="employee.id">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ employee.name }}</div>
                                <div class="text-xs font-medium text-slate-500">{{ employee.nik }} &middot; {{ employee.email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="chip uppercase">{{ employee.role }}</span>
                            </td>
                            <td class="px-4 py-3">{{ employee.outlet?.code || '-' }}</td>
                            <td class="px-4 py-3">{{ employee.job_role?.name || employee.position_title || '-' }}</td>
                            <td class="px-4 py-3">{{ employee.phone || '-' }}</td>
                            <td class="px-4 py-3">
                                <button class="btn-secondary min-h-0 px-3 py-1 text-xs" @click="toggleEmployee(employee)">
                                    {{ employee.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
