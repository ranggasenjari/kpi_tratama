<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Gauge } from '@lucide/vue';

defineProps({
    indicators: Array,
    templates: Array,
    outlets: Array,
    jobRoles: Array,
    types: Array,
});

const form = useForm({
    name: '',
    form_template_id: '',
    type: 'frequency',
    period: 'monthly',
    target: 1,
    weight: 10,
    reward_amount: 0,
    reward_unit: '',
    allow_overachievement: false,
    outlet_id: '',
    job_role_id: '',
});

function submit() {
    form.post('/admin/kpi', {
        onSuccess: () => form.reset(),
    });
}

function toggle(indicator) {
    router.patch(`/admin/kpi/${indicator.id}/toggle`);
}

function money(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
}
</script>

<template>
    <AppLayout>
        <Head title="KPI Setup" />

        <div class="mb-6">
            <div class="kicker">Performance engine</div>
            <h1 class="page-title mt-1">KPI Setup</h1>
            <p class="page-subtitle mt-1">Hubungkan form dinamis ke target, bobot, assignment, dan reward.</p>
        </div>

        <section class="material-card p-5">
            <form class="grid gap-4 md:grid-cols-4" @submit.prevent="submit">
                <label class="block md:col-span-2">
                    <span class="field-label">Nama indikator</span>
                    <input v-model="form.name" class="field-control mt-1.5" />
                </label>
                <label class="block md:col-span-2">
                    <span class="field-label">Form sumber</span>
                    <select v-model="form.form_template_id" class="field-control mt-1.5">
                        <option value="">Tanpa form</option>
                        <option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Tipe</span>
                    <select v-model="form.type" class="field-control mt-1.5">
                        <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Target</span>
                    <input v-model.number="form.target" type="number" min="0.01" step="0.01" class="field-control mt-1.5" />
                </label>
                <label class="block">
                    <span class="field-label">Bobot</span>
                    <input v-model.number="form.weight" type="number" min="0.01" step="0.01" class="field-control mt-1.5" />
                </label>
                <label class="block">
                    <span class="field-label">Reward</span>
                    <input v-model.number="form.reward_amount" type="number" min="0" step="100" class="field-control mt-1.5" />
                </label>
                <label class="block">
                    <span class="field-label">Outlet</span>
                    <select v-model="form.outlet_id" class="field-control mt-1.5">
                        <option value="">Semua outlet</option>
                        <option v-for="outlet in outlets" :key="outlet.id" :value="outlet.id">{{ outlet.code }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Jabatan</span>
                    <select v-model="form.job_role_id" class="field-control mt-1.5">
                        <option value="">Semua jabatan</option>
                        <option v-for="role in jobRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
                    </select>
                </label>
                <label class="mt-7 flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input v-model="form.allow_overachievement" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" />
                    Boleh over target
                </label>
                <div class="flex items-end justify-end md:col-span-1">
                    <button type="submit" class="btn-primary">
                        <Gauge class="h-4 w-4" />
                        Simpan KPI
                    </button>
                </div>
            </form>
        </section>

        <section class="table-shell mt-6">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Indikator</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Target</th>
                            <th class="px-4 py-3">Bobot</th>
                            <th class="px-4 py-3">Reward</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="indicator in indicators" :key="indicator.id">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ indicator.name }}</div>
                                <div class="text-xs font-medium text-slate-500">{{ indicator.template?.name || 'Tanpa form' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ indicator.type }}</td>
                            <td class="px-4 py-3">{{ indicator.target }}</td>
                            <td class="px-4 py-3">{{ indicator.weight }}</td>
                            <td class="px-4 py-3 font-semibold text-emerald-700">{{ money(indicator.reward_amount) }}</td>
                            <td class="px-4 py-3">
                                <button class="btn-secondary min-h-0 px-3 py-1 text-xs" @click="toggle(indicator)">
                                    {{ indicator.is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
