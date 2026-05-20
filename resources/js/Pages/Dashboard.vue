<script setup>
import AppLayout from '../Components/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Award, ClipboardList, FileCheck2, Users } from '@lucide/vue';

defineProps({
    stats: Object,
    month: String,
    report: Object,
    leaderboard: Array,
});

const page = usePage();

function money(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);
}
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="kicker">Periode {{ month }}</div>
                <h1 class="page-title mt-1">Dashboard</h1>
                <p class="page-subtitle mt-1">Ringkasan aktivitas, approval, dan capaian KPI berjalan.</p>
            </div>
            <Link href="/forms" class="btn-primary">
                Isi Form
                <ArrowRight class="h-4 w-4" />
            </Link>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="metric-card">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <ClipboardList class="h-5 w-5" />
                </div>
                <div class="text-3xl font-black text-slate-950">{{ stats.forms }}</div>
                <div class="text-sm font-medium text-slate-500">Form aktif</div>
            </div>
            <div class="metric-card">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <FileCheck2 class="h-5 w-5" />
                </div>
                <div class="text-3xl font-black text-slate-950">{{ stats.approved_this_month }}</div>
                <div class="text-sm font-medium text-slate-500">Approved bulan ini</div>
            </div>
            <div class="metric-card">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <Award class="h-5 w-5" />
                </div>
                <div class="text-3xl font-black text-slate-950">{{ stats.pending_reviews }}</div>
                <div class="text-sm font-medium text-slate-500">Menunggu review</div>
            </div>
            <div class="metric-card">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700">
                    <Users class="h-5 w-5" />
                </div>
                <div class="text-3xl font-black text-slate-950">{{ stats.employees }}</div>
                <div class="text-sm font-medium text-slate-500">Karyawan aktif</div>
            </div>
        </section>

        <section v-if="report" class="material-card mt-6 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Skor KPI Saya</h2>
                    <p class="text-sm font-medium text-slate-500">Hanya submission approved yang dihitung.</p>
                </div>
                <div class="rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 px-5 py-4 text-right text-white shadow-lg shadow-blue-500/20">
                    <div class="text-3xl font-black">{{ report.score }}</div>
                    <div class="text-sm font-semibold text-cyan-50">{{ money(report.reward_total) }}</div>
                </div>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Indikator</th>
                            <th class="px-4 py-3">Target</th>
                            <th class="px-4 py-3">Tercapai</th>
                            <th class="px-4 py-3">Skor</th>
                            <th class="px-4 py-3">Reward</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in report.breakdown" :key="item.indicator_id">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ item.name }}</td>
                            <td class="px-4 py-3">{{ item.target }}</td>
                            <td class="px-4 py-3">{{ item.achieved }}</td>
                            <td class="px-4 py-3 font-semibold text-blue-700">{{ item.achievement_percent }}%</td>
                            <td class="px-4 py-3">{{ money(item.reward) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section v-if="page.props.auth.user.role !== 'karyawan'" class="material-card mt-6 p-5">
            <h2 class="text-lg font-black text-slate-950">Leaderboard KPI</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <div v-for="item in leaderboard" :key="item.user.id" class="flex items-center justify-between rounded-lg border border-slate-200 bg-white/80 px-4 py-3 shadow-sm">
                    <div>
                        <div class="font-semibold text-slate-900">{{ item.user.name }}</div>
                        <div class="text-xs font-medium text-slate-500">{{ item.user.nik }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-black text-blue-700">{{ item.score }}</div>
                        <div class="text-xs font-medium text-slate-500">{{ money(item.reward_total) }}</div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
