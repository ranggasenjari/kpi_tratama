<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps({
    month: String,
    reports: Array,
    summary: Object,
});

const selectedMonth = ref(props.month);

function applyMonth() {
    router.get('/reports', { month: selectedMonth.value }, { preserveState: true });
}

function money(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
}
</script>

<template>
    <AppLayout>
        <Head title="Laporan KPI" />

        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <div class="kicker">Monthly report</div>
                <h1 class="page-title mt-1">Laporan KPI</h1>
                <p class="page-subtitle mt-1">Rekap skor dan reward berdasarkan submission approved.</p>
            </div>
            <div class="flex gap-2">
                <input v-model="selectedMonth" type="month" class="field-control h-10 w-auto" @change="applyMonth" />
                <a :href="`/reports/export?month=${selectedMonth}`" class="btn-secondary">
                    <Download class="h-4 w-4" />
                    CSV
                </a>
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="metric-card">
                <div class="text-3xl font-black text-slate-950">{{ summary.employees }}</div>
                <div class="text-sm font-medium text-slate-500">Karyawan</div>
            </div>
            <div class="metric-card">
                <div class="text-3xl font-black text-blue-700">{{ summary.average_score }}</div>
                <div class="text-sm font-medium text-slate-500">Rata-rata skor</div>
            </div>
            <div class="metric-card">
                <div class="text-3xl font-black text-emerald-700">{{ money(summary.reward_total) }}</div>
                <div class="text-sm font-medium text-slate-500">Estimasi reward</div>
            </div>
        </section>

        <section class="table-shell mt-6">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Karyawan</th>
                            <th class="px-4 py-3">Outlet</th>
                            <th class="px-4 py-3">Jabatan</th>
                            <th class="px-4 py-3">Skor</th>
                            <th class="px-4 py-3">Reward</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="report in reports" :key="report.user.id">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ report.user.name }}</div>
                                <div class="text-xs font-medium text-slate-500">{{ report.user.nik }}</div>
                            </td>
                            <td class="px-4 py-3">{{ report.outlet || '-' }}</td>
                            <td class="px-4 py-3">{{ report.job_role || '-' }}</td>
                            <td class="px-4 py-3 font-black text-blue-700">{{ report.score }}</td>
                            <td class="px-4 py-3 font-semibold text-emerald-700">{{ money(report.reward_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
