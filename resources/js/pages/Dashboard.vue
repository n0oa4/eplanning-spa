<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const props = defineProps<{
    stats: {
        total_program: number
        menunggu_persetujuan: number
        total_pagu: number
    },
    program_terbaru: Array<{
        id: number
        kode_program: string
        nama_program: string
        tahun: number
        status: string
        total_pagu: number
    }>
}>()

const formatRupiah = (value: number) => {
    return value?.toLocaleString('id-ID') || '0'
}

const statusLabel = (status: string) => {
    const map: Record<string, string> = {
        draft: 'Draft',
        verifikasi: 'Menunggu Konfirmasi SIPD',
        disetujui: 'Disetujui',
    }
    return map[status] || status
}

const statusClass = (status: string) => {
    const map: Record<string, string> = {
        draft: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
        verifikasi: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
        disetujui: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
    }
    return map[status] || ''
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <!-- KARTU STATISTIK -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <!-- Total Program -->
                <div class="rounded-xl border border-border bg-card dark:bg-zinc-900 p-5 flex flex-col gap-2 shadow-sm">
                    <span class="text-sm text-zinc-500">Total Program</span>
                    <span class="text-3xl font-bold">{{ stats.total_program }}</span>
                    <span class="text-xs text-zinc-400">Program aktif (draft & verifikasi)</span>
                </div>

                <!-- Menunggu Konfirmasi SIPD -->
                <div class="rounded-xl border border-border bg-card dark:bg-zinc-900 p-5 flex flex-col gap-2 shadow-sm">
                    <span class="text-sm text-zinc-500">Menunggu Konfirmasi SIPD</span>
                    <span class="text-3xl font-bold text-yellow-500">{{ stats.menunggu_persetujuan }}</span>
                    <span class="text-xs text-zinc-400">Program siap dikonfirmasi ke SIPD</span>
                </div>

                <!-- Total Pagu -->
                <div class="rounded-xl border border-border bg-card dark:bg-zinc-900 p-5 flex flex-col gap-2 shadow-sm">
                    <span class="text-sm text-zinc-500">Total Pagu Anggaran</span>
                    <span class="text-2xl font-bold text-emerald-500">Rp {{ formatRupiah(stats.total_pagu) }}</span>
                    <span class="text-xs text-zinc-400">Akumulasi seluruh program aktif</span>
                </div>

            </div>

            <!-- TABEL PROGRAM TERBARU -->
            <div class="rounded-xl border border-border bg-card dark:bg-zinc-900 p-5 shadow-sm">
                <h2 class="text-base font-semibold mb-4">Program Terbaru</h2>

                <div v-if="program_terbaru.length" class="flex flex-col gap-3">
                    <div
                        v-for="program in program_terbaru"
                        :key="program.id"
                        class="flex items-center justify-between border border-border rounded-lg px-4 py-3"
                    >
                        <div class="flex flex-col gap-1">
                            <span class="font-medium text-sm">{{ program.nama_program }}</span>
                            <span class="text-xs text-zinc-400">Tahun {{ program.tahun }} · Rp {{ formatRupiah(program.total_pagu) }}</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusClass(program.status)]">
                                {{ statusLabel(program.status) }}
                            </span>
                            <Link :href="`/program?highlight=${program.id}`" class="text-xs text-primary hover:underline">
                                Lihat →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-sm text-zinc-400 text-center py-8">
                    Belum ada program yang dibuat.
                </div>
            </div>

        </div>
    </AppLayout>
</template>
