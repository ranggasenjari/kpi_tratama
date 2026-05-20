<script setup>
import logoUrl from '../../img/logo_tratama.jpeg';

defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md',
    },
    stacked: {
        type: Boolean,
        default: false,
    },
    theme: {
        type: String,
        default: 'light',
    },
});

const companyName = 'PT. TRATAMA KREATIF INDONESIA';
</script>

<template>
    <div
        class="min-w-0"
        :class="[
            stacked ? 'flex flex-col items-start gap-4' : 'flex items-center gap-3',
            theme === 'dark' ? 'text-white' : 'text-slate-950',
        ]"
    >
        <div
            class="flex shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white shadow-lg ring-1"
            :class="[
                size === 'lg' ? 'h-24 w-24 p-1.5' : size === 'sm' ? 'h-12 w-12 p-1' : 'h-14 w-14 p-1',
                theme === 'dark' ? 'shadow-blue-950/25 ring-white/18' : 'shadow-slate-900/10 ring-slate-200',
            ]"
        >
            <img :src="logoUrl" :alt="companyName" class="h-full w-full object-contain" />
        </div>

        <div v-if="!compact" class="min-w-0">
            <div
                class="font-black uppercase leading-tight tracking-normal"
                :class="[
                    size === 'lg' ? 'text-2xl sm:text-3xl' : size === 'sm' ? 'text-sm' : 'text-base',
                    theme === 'dark' ? 'text-white' : 'text-slate-950',
                ]"
            >
                {{ companyName }}
            </div>
            <div
                class="mt-1 font-semibold"
                :class="[
                    size === 'lg' ? 'text-sm' : 'text-xs',
                    theme === 'dark' ? 'text-cyan-100/80' : 'text-slate-500',
                ]"
            >
                KPI & performance workspace
            </div>
        </div>
    </div>
</template>
