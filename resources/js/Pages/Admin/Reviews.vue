<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from '@lucide/vue';
import { reactive } from 'vue';

const props = defineProps({
    submissions: Object,
});

const forms = reactive({});

props.submissions.data.forEach((submission) => {
    forms[submission.id] = useForm({
        status: submission.status === 'approved' ? 'approved' : 'approved',
        review_note: submission.review_note || '',
        manual_score: submission.manual_score || '',
        approved_units: submission.approved_units || 1,
    });
});

function reviewForm(submission) {
    return forms[submission.id];
}

function save(submission, status) {
    const form = reviewForm(submission);
    form.status = status;
    form.patch(`/admin/reviews/${submission.id}`);
}

function answerValue(value) {
    if (Array.isArray(value)) return value.join(', ');
    return value || '-';
}
</script>

<template>
    <AppLayout>
        <Head title="Review Submission" />

        <div class="mb-6">
            <div class="kicker">Approval queue</div>
            <h1 class="page-title mt-1">Review Submission</h1>
            <p class="page-subtitle mt-1">Approve atau reject bukti aktivitas sebelum masuk perhitungan KPI.</p>
        </div>

        <section class="space-y-4">
            <article v-for="submission in submissions.data" :key="submission.id" class="material-card p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="chip uppercase">{{ submission.status }}</div>
                        <h2 class="mt-2 text-lg font-black text-slate-950">{{ submission.form_name }}</h2>
                        <p class="text-sm font-medium text-slate-500">{{ submission.user.name }} &middot; {{ submission.user.nik }} &middot; {{ submission.submitted_at }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn-success" @click="save(submission, 'approved')">
                            <CheckCircle2 class="h-4 w-4" />
                            Approve
                        </button>
                        <button class="btn-danger" @click="save(submission, 'rejected')">
                            <XCircle class="h-4 w-4" />
                            Reject
                        </button>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Jawaban</h3>
                        <dl class="mt-2 space-y-2 text-sm">
                            <div v-for="answer in submission.answers" :key="answer.label" class="rounded-lg border border-slate-200 bg-white/80 px-3 py-2 shadow-sm">
                                <dt class="font-semibold text-slate-700">{{ answer.label }}</dt>
                                <dd class="mt-1 text-slate-600">{{ answerValue(answer.value) }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Bukti Upload</h3>
                        <div class="mt-2 space-y-2 text-sm">
                            <div v-for="attachment in submission.attachments" :key="attachment.name" class="rounded-lg border border-cyan-100 bg-cyan-50/70 px-3 py-2 shadow-sm">
                                <div class="font-semibold text-slate-700">{{ attachment.label }}</div>
                                <div class="text-slate-600">{{ attachment.name }}</div>
                            </div>
                            <div v-if="submission.attachments.length === 0" class="text-sm font-medium text-slate-500">Tidak ada lampiran.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <label>
                        <span class="field-label">Unit approved</span>
                        <input v-model.number="reviewForm(submission).approved_units" type="number" min="0" step="0.01" class="field-control mt-1.5" />
                    </label>
                    <label>
                        <span class="field-label">Skor manual</span>
                        <input v-model="reviewForm(submission).manual_score" type="number" min="0" max="100" step="0.01" class="field-control mt-1.5" />
                    </label>
                    <label class="md:col-span-2">
                        <span class="field-label">Catatan review</span>
                        <input v-model="reviewForm(submission).review_note" class="field-control mt-1.5" />
                    </label>
                </div>
            </article>
        </section>

        <div class="mt-4 flex flex-wrap gap-2">
            <Link
                v-for="link in submissions.links"
                :key="link.label"
                :href="link.url || '#'"
                class="btn-secondary min-h-0 px-3 py-1 text-sm"
                :class="{ 'border-cyan-200 bg-cyan-50 text-cyan-800': link.active }"
                v-html="link.label"
            />
        </div>
    </AppLayout>
</template>
