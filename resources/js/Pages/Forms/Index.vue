<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, FileText } from '@lucide/vue';

defineProps({
    forms: Array,
});
</script>

<template>
    <AppLayout>
        <Head title="Isi Form" />

        <div class="mb-6">
            <div class="kicker">Employee portal</div>
            <h1 class="page-title mt-1">Isi Form</h1>
            <p class="page-subtitle mt-1">Pilih form aktivitas, KPI, atau administrasi yang perlu dikirim.</p>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="form in forms"
                :key="form.id"
                :href="`/forms/${form.id}`"
                class="material-card group block p-4 hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-xl"
            >
                <div class="mb-4 flex items-center justify-between gap-3">
                    <span class="chip uppercase">{{ form.category }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-50 to-cyan-50 text-blue-700">
                        <BadgeCheck v-if="form.is_kpi" class="h-5 w-5" />
                        <FileText v-else class="h-5 w-5" />
                    </span>
                </div>
                <h2 class="text-base font-black leading-snug text-slate-950">{{ form.name }}</h2>
                <p class="mt-2 line-clamp-3 min-h-12 text-sm font-medium leading-6 text-slate-500">{{ form.description || 'Tidak ada deskripsi.' }}</p>
                <div class="mt-5 flex items-center justify-between text-sm">
                    <span class="font-semibold text-slate-500">{{ form.fields_count }} field</span>
                    <span class="inline-flex items-center gap-1 font-black text-blue-700">
                        Buka
                        <ArrowRight class="h-4 w-4 transition group-hover:translate-x-0.5" />
                    </span>
                </div>
            </Link>
        </section>
    </AppLayout>
</template>
