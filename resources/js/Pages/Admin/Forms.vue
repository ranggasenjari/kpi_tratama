<script setup>
import AppLayout from '../../Components/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CopyPlus, Plus, Save, Trash2 } from '@lucide/vue';

defineProps({
    templates: Array,
    fieldTypes: Array,
});

const form = useForm({
    name: '',
    description: '',
    category: 'kpi',
    is_active: true,
    fields: [
        { label: '', type: 'text', is_required: true, optionsText: '', options: [], config: { max_files: 1, max_size_mb: 10, accepted_types: ['image'] } },
    ],
});

function addField() {
    form.fields.push({ label: '', type: 'text', is_required: false, optionsText: '', options: [], config: { max_files: 1, max_size_mb: 10, accepted_types: ['image'] } });
}

function removeField(index) {
    if (form.fields.length > 1) form.fields.splice(index, 1);
}

function payload() {
    return {
        ...form.data(),
        fields: form.fields.map((field) => ({
            label: field.label,
            type: field.type,
            is_required: field.is_required,
            options: (field.optionsText || '').split('\n').map((item) => item.trim()).filter(Boolean),
            config: field.type === 'file' ? field.config : {},
        })),
    };
}

function submit() {
    form.transform(payload).post('/admin/forms', {
        onSuccess: () => form.reset(),
    });
}

function duplicate(template) {
    form.name = `${template.name} Copy`;
    form.description = template.description || '';
    form.category = template.category;
    form.is_active = template.is_active;
    form.fields = template.fields.map((field) => ({
        label: field.label,
        type: field.type,
        is_required: field.is_required,
        optionsText: (field.options || []).join('\n'),
        options: field.options || [],
        config: field.config || { max_files: 1, max_size_mb: 10, accepted_types: ['image'] },
    }));
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toggle(template) {
    router.patch(`/admin/forms/${template.id}/toggle`);
}
</script>

<template>
    <AppLayout>
        <Head title="Form Builder" />

        <div class="mb-6">
            <div class="kicker">Admin studio</div>
            <h1 class="page-title mt-1">Form Builder</h1>
            <p class="page-subtitle mt-1">Buat template form dinamis. Perubahan besar diterbitkan sebagai versi baru.</p>
        </div>

        <section class="material-card p-5">
            <form class="space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="block md:col-span-2">
                        <span class="field-label">Nama form</span>
                        <input v-model="form.name" class="field-control mt-1.5" />
                        <span v-if="form.errors.name" class="text-xs font-semibold text-red-600">{{ form.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="field-label">Kategori</span>
                        <select v-model="form.category" class="field-control mt-1.5">
                            <option value="kpi">KPI</option>
                            <option value="hr">HR</option>
                            <option value="operational">Operasional</option>
                        </select>
                    </label>
                    <label class="block md:col-span-3">
                        <span class="field-label">Deskripsi</span>
                        <textarea v-model="form.description" rows="2" class="field-control mt-1.5" />
                    </label>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="kicker">Field</h2>
                        <button type="button" class="btn-secondary" @click="addField">
                            <Plus class="h-4 w-4" />
                            Tambah field
                        </button>
                    </div>

                    <div v-for="(field, index) in form.fields" :key="index" class="rounded-lg border border-slate-200 bg-white/78 p-4 shadow-sm">
                        <div class="grid gap-3 md:grid-cols-12">
                            <label class="block md:col-span-5">
                                <span class="field-label">Label</span>
                                <input v-model="field.label" class="field-control mt-1.5" />
                            </label>
                            <label class="block md:col-span-3">
                                <span class="field-label">Tipe</span>
                                <select v-model="field.type" class="field-control mt-1.5">
                                    <option v-for="type in fieldTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                                </select>
                            </label>
                            <label class="mt-7 flex items-center gap-2 text-sm font-semibold text-slate-700 md:col-span-2">
                                <input v-model="field.is_required" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" />
                                Wajib
                            </label>
                            <button type="button" class="btn-secondary mt-6 md:col-span-2" @click="removeField(index)">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <label v-if="['select', 'radio', 'radio_other', 'checkbox'].includes(field.type)" class="mt-3 block">
                            <span class="field-label">Opsi, satu baris per pilihan</span>
                            <textarea v-model="field.optionsText" rows="3" class="field-control mt-1.5" />
                        </label>

                        <div v-if="field.type === 'file'" class="mt-3 grid gap-3 md:grid-cols-3">
                            <label>
                                <span class="field-label">Maks file</span>
                                <input v-model.number="field.config.max_files" type="number" min="1" class="field-control mt-1.5" />
                            </label>
                            <label>
                                <span class="field-label">Maks MB/file</span>
                                <input v-model.number="field.config.max_size_mb" type="number" min="1" class="field-control mt-1.5" />
                            </label>
                            <label>
                                <span class="field-label">Tipe file</span>
                                <select v-model="field.config.accepted_types" multiple class="field-control mt-1.5">
                                    <option value="image">Gambar</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        <Save class="h-4 w-4" />
                        Simpan Template
                    </button>
                </div>
            </form>
        </section>

        <section class="table-shell mt-6">
            <div class="border-b border-slate-200 bg-white/80 px-5 py-4">
                <h2 class="font-black text-slate-950">Template Aktif</h2>
            </div>
            <div class="divide-y divide-slate-100">
                <div v-for="template in templates" :key="template.id" class="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="font-semibold text-slate-900">{{ template.name }}</div>
                        <div class="text-sm font-medium text-slate-500">{{ template.category }} &middot; v{{ template.version }} &middot; {{ template.fields.length }} field &middot; {{ template.submissions_count }} submission</div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary" @click="duplicate(template)">
                            <CopyPlus class="h-4 w-4" />
                            Duplikat
                        </button>
                        <button type="button" class="btn-secondary" @click="toggle(template)">
                            {{ template.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
