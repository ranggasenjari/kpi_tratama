<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Send, UploadCloud } from '@lucide/vue';

const props = defineProps({
    template: Object,
});

const initialAnswers = Object.fromEntries(
    props.template.fields.map((field) => [field.id, field.type === 'checkbox' ? [] : '']),
);

const form = useForm({
    answers: initialAnswers,
    files: {},
});

function setFiles(field, event) {
    form.files[field.id] = Array.from(event.target.files || []);
}

function submit() {
    form.post(`/forms/${props.template.id}/submissions`, {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout>
        <Head :title="template.name" />

        <div class="mb-6">
            <div class="kicker">{{ template.category }} &middot; v{{ template.version }}</div>
            <h1 class="page-title mt-1">{{ template.name }}</h1>
            <p class="page-subtitle mt-2 max-w-3xl">{{ template.description }}</p>
        </div>

        <form class="material-card space-y-4 p-4 sm:p-6" @submit.prevent="submit">
            <div v-for="field in template.fields" :key="field.id" class="rounded-lg border border-slate-200/70 bg-white/68 p-4 shadow-sm">
                <label class="mb-2 block text-sm font-black text-slate-800">
                    {{ field.label }}
                    <span v-if="field.is_required" class="text-red-600">*</span>
                </label>

                <input v-if="field.type === 'text'" v-model="form.answers[field.id]" class="field-control" />

                <textarea v-else-if="field.type === 'textarea'" v-model="form.answers[field.id]" rows="4" class="field-control" />

                <input v-else-if="field.type === 'date'" v-model="form.answers[field.id]" type="date" class="field-control" />

                <select v-else-if="field.type === 'select'" v-model="form.answers[field.id]" class="field-control">
                    <option value="">Pilih</option>
                    <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                </select>

                <div v-else-if="field.type === 'radio' || field.type === 'radio_other'" class="grid gap-2 sm:grid-cols-2">
                    <label v-for="option in field.options" :key="option" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white/85 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                        <input v-model="form.answers[field.id]" type="radio" :value="option" class="h-4 w-4 text-blue-600" />
                        {{ option }}
                    </label>
                    <input v-if="field.type === 'radio_other'" v-model="form.answers[field.id]" placeholder="Yang lain" class="field-control sm:col-span-2" />
                </div>

                <div v-else-if="field.type === 'checkbox'" class="grid gap-2 sm:grid-cols-2">
                    <label v-for="option in field.options" :key="option" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white/85 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm">
                        <input v-model="form.answers[field.id]" type="checkbox" :value="option" class="h-4 w-4 rounded border-slate-300 text-blue-600" />
                        {{ option }}
                    </label>
                </div>

                <label v-else class="flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-cyan-300 bg-cyan-50/70 px-4 py-6 text-center hover:bg-cyan-50">
                    <UploadCloud class="mb-2 h-7 w-7 text-cyan-700" />
                    <span class="text-sm font-black text-slate-800">Pilih file bukti</span>
                    <span class="mt-1 text-xs font-semibold text-slate-500">
                        Maks {{ field.config?.max_files || 1 }} file, {{ field.config?.max_size_mb || 10 }} MB per file
                    </span>
                    <input class="hidden" type="file" :multiple="(field.config?.max_files || 1) > 1" @change="setFiles(field, $event)" />
                </label>

                <div v-if="form.errors[`answers.${field.id}`]" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors[`answers.${field.id}`] }}</div>
                <div v-if="form.errors[`files.${field.id}`]" class="mt-2 text-xs font-semibold text-red-600">{{ form.errors[`files.${field.id}`] }}</div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    Kirim Review
                    <Send class="h-4 w-4" />
                </button>
            </div>
        </form>
    </AppLayout>
</template>
