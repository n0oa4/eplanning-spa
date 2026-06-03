<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'


defineOptions({
    layout: AppLayout
})

defineProps({
    programs: Array
});
</script>

<template>
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                Daftar Program
            </h1>

            <Link
                href="/program/create"
                class="bg-primary text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition"
            >
                + Tambah Program
            </Link>
        </div>

        <!-- List Program -->
        <div v-if="programs.length" class="grid gap-4">
            <div
                v-for="program in programs"
                :key="program.id"
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-sm hover:shadow-md transition"
            >
                <div class="flex justify-between items-start">

                    <!-- Informasi Program -->
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold">
                            {{ program.nama_program }}
                        </h2>

                        <p class="text-sm text-zinc-500">
                            Tahun: {{ program.tahun }}
                        </p>

                        <p class="text-sm text-zinc-500">
                            Status: {{ program.status }}
                        </p>

                        <div class="text-emerald-500 font-medium pt-2">
                            Total Pagu:
                            Rp {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
                        </div>

                        <button
                            v-if="program.status === 'draft'"
                            @click="$inertia.post(route('program.verifikasi', program.id))"
                            class="bg-yellow-500 text-white px-3 py-1 rounded ml-2"
                        >
                            Verifikasi
                        </button>

                        <button
                            v-if="program.status === 'verifikasi'"
                            @click="$inertia.post(route('program.kembalikan', program.id))"
                            class="px-3 py-1 bg-yellow-500 text-white rounded"
                        >
                            Kembalikan ke Draft
                        </button>

                        <button
                            v-if="program.status === 'verifikasi'"
                            @click="$inertia.post(route('program.setujui', program.id))"
                            class="bg-green-600 text-white px-3 py-1 rounded ml-2"
                        >
                            Setujui
                        </button>
                    </div>

                    <!-- Tombol Detail -->
                    <Link
                        :href="`/program/${program.id}`"
                        class="text-sm text-primary hover:underline"
                    >
                        Lihat Detail →
                    </Link>

                </div>
            </div>
        </div>

        <!-- Jika Kosong -->
        <div
            v-else
            class="text-center py-12 border border-dashed rounded-xl text-zinc-500"
        >
            Belum ada program yang dibuat.
        </div>

    </div>
</template>
