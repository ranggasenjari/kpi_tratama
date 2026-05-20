<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, KeyRound, ShieldCheck, Sparkles } from '@lucide/vue';

const form = useForm({
    nik: '',
    password: '',
    remember: true,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <Head title="Login" />

    <main class="relative min-h-screen overflow-hidden bg-[#eaf4ff] px-4 py-8">
        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(37,99,235,0.18),transparent_36%),linear-gradient(225deg,rgba(245,158,11,0.18),transparent_34%)]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.55)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.55)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <section class="relative mx-auto grid min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center gap-6 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="hidden lg:block">
                <div class="kicker">KPI Tratama</div>
                <h1 class="mt-4 max-w-2xl text-5xl font-black leading-tight text-slate-950">
                    Operasional rapi, KPI terbaca, keputusan lebih cepat.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-600">
                    Satu ruang kerja untuk form aktivitas, review bukti, skor KPI, dan reward karyawan.
                </p>

                <div class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                    <div class="material-card p-4">
                        <BadgeCheck class="mb-4 h-5 w-5 text-emerald-600" />
                        <div class="text-2xl font-black text-slate-950">11</div>
                        <div class="text-sm font-medium text-slate-500">Template awal</div>
                    </div>
                    <div class="material-card p-4">
                        <ShieldCheck class="mb-4 h-5 w-5 text-blue-600" />
                        <div class="text-2xl font-black text-slate-950">3</div>
                        <div class="text-sm font-medium text-slate-500">Role akses</div>
                    </div>
                    <div class="material-card p-4">
                        <Sparkles class="mb-4 h-5 w-5 text-amber-600" />
                        <div class="text-2xl font-black text-slate-950">7</div>
                        <div class="text-sm font-medium text-slate-500">Indikator KPI</div>
                    </div>
                </div>
            </div>

            <section class="material-card-strong mx-auto w-full max-w-md min-w-0 overflow-hidden p-6 sm:p-7">
                <div class="mb-7 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-500/25">
                        <KeyRound class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xl font-black text-slate-950">Masuk KPI</div>
                        <p class="text-sm font-medium text-slate-500">Gunakan NIK karyawan</p>
                    </div>
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <label class="block">
                        <span class="field-label">NIK</span>
                        <input
                            v-model="form.nik"
                            class="field-control mt-1.5"
                            autocomplete="username"
                            autofocus
                            placeholder="Contoh: EMP001"
                        />
                        <span v-if="form.errors.nik" class="mt-1.5 block text-xs font-semibold text-red-600">{{ form.errors.nik }}</span>
                    </label>

                    <label class="block">
                        <span class="field-label">Password</span>
                        <input
                            v-model="form.password"
                            type="password"
                            class="field-control mt-1.5"
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                        <span v-if="form.errors.password" class="mt-1.5 block text-xs font-semibold text-red-600">{{ form.errors.password }}</span>
                    </label>

                    <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                        <input v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" />
                        Ingat sesi login
                    </label>

                    <button type="submit" class="btn-primary w-full" :disabled="form.processing">
                        Masuk
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </form>

                <div class="mt-6 break-words rounded-lg border border-slate-200 bg-slate-50/80 px-4 py-3 text-xs font-semibold leading-5 text-slate-500">
                    Demo: OWNER001 / ADM001 / EMP001 &middot; password: password
                </div>
            </section>
        </section>
    </main>
</template>
