<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    submissions: Object,
});

const statusClass = {
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    rejected: 'bg-red-50 text-red-700 border-red-200',
    draft: 'bg-slate-50 text-slate-700 border-slate-200',
};
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Submission" />

        <div class="mb-6">
            <div class="kicker">Submission log</div>
            <h1 class="page-title mt-1">Riwayat Submission</h1>
            <p class="page-subtitle mt-1">Pantau status form yang sudah dikirim.</p>
        </div>

        <section class="table-shell">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Form</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dikirim</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="submission in submissions.data" :key="submission.id">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ submission.form_name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold" :class="statusClass[submission.status]">
                                    {{ submission.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ submission.submitted_at }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ submission.approved_units }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ submission.review_note || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap gap-2 border-t border-slate-200 bg-white/80 px-4 py-3">
                <Link
                    v-for="link in submissions.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="btn-secondary min-h-0 px-3 py-1 text-sm"
                    :class="{ 'border-cyan-200 bg-cyan-50 text-cyan-800': link.active }"
                    v-html="link.label"
                />
            </div>
        </section>
    </AppLayout>
</template>
