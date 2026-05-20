<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardCheck,
    FileClock,
    FilePenLine,
    Gauge,
    LayoutDashboard,
    LogOut,
    Network,
    Settings2,
} from '@lucide/vue';
import BrandIdentity from './BrandIdentity.vue';

const page = usePage();
const user = page.props.auth.user;

const nav = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard, roles: ['karyawan', 'admin', 'owner'] },
    { label: 'Isi Form', href: '/forms', icon: FilePenLine, roles: ['karyawan', 'admin', 'owner'] },
    { label: 'Riwayat', href: '/submissions', icon: FileClock, roles: ['karyawan', 'admin', 'owner'] },
    { label: 'Master Data', href: '/admin/master-data', icon: Network, roles: ['admin', 'owner'] },
    { label: 'Form Builder', href: '/admin/forms', icon: Settings2, roles: ['admin', 'owner'] },
    { label: 'KPI Setup', href: '/admin/kpi', icon: Gauge, roles: ['admin', 'owner'] },
    { label: 'Review', href: '/admin/reviews', icon: ClipboardCheck, roles: ['admin', 'owner'] },
    { label: 'Laporan', href: '/reports', icon: BarChart3, roles: ['admin', 'owner'] },
].filter((item) => item.roles.includes(user?.role));

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="app-frame">
        <aside class="fixed inset-y-0 left-0 z-20 hidden w-72 overflow-hidden bg-[#122033] px-4 py-5 text-white shadow-2xl lg:block">
            <div class="absolute inset-x-0 top-0 h-36 bg-gradient-to-br from-blue-500/35 via-cyan-400/20 to-amber-300/25"></div>

            <div class="relative px-2">
                <BrandIdentity theme="dark" size="sm" />
            </div>

            <nav class="relative mt-9 space-y-1.5">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/10 hover:text-white"
                    :class="{ 'bg-white text-slate-950 shadow-xl shadow-blue-950/25': page.url.startsWith(item.href) }"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-md bg-white/8 text-cyan-100 group-hover:bg-cyan-300/20"
                        :class="{ 'bg-gradient-to-br from-blue-600 to-cyan-500 text-white': page.url.startsWith(item.href) }"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                    </span>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="absolute inset-x-4 bottom-5 rounded-lg border border-white/10 bg-white/8 p-4 text-sm text-slate-200 shadow-xl">
                <div class="font-semibold text-white">{{ user?.role?.toUpperCase() }}</div>
                <div class="mt-1 text-cyan-100/80">{{ user?.nik }}</div>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-10 border-b border-white/60 bg-white/78 px-4 py-3 shadow-sm backdrop-blur-xl lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <BrandIdentity class="lg:hidden" compact size="sm" />
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-slate-950">{{ user?.name }}</div>
                            <div class="truncate text-xs font-semibold uppercase text-slate-500">{{ user?.role }} &middot; {{ user?.nik }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary" @click="logout">
                        <LogOut class="h-4 w-4" />
                        Keluar
                    </button>
                </div>

                <nav class="mt-3 flex gap-2 overflow-x-auto lg:hidden">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-slate-200 bg-white/85 px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm"
                        :class="{ 'border-cyan-200 bg-cyan-50 text-cyan-800': page.url.startsWith(item.href) }"
                    >
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.label }}
                    </Link>
                </nav>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 lg:px-8">
                <div v-if="page.props.flash.success" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50/95 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm">
                    {{ page.props.flash.success }}
                </div>
                <div v-if="page.props.flash.error" class="mb-4 rounded-lg border border-red-200 bg-red-50/95 px-4 py-3 text-sm font-medium text-red-800 shadow-sm">
                    {{ page.props.flash.error }}
                </div>
                <div class="fade-rise">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
