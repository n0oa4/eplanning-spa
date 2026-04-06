<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    programs: Array,
    filters: Object,
});

const tahun = ref(props.filters?.tahun || '');
const sort = ref(props.filters?.sort || 'disetujui_pada');
const direction = ref(props.filters?.direction || 'desc');

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(angka || 0);
}

function formatTanggal(tanggal) {
    if (!tanggal) return '-';

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'long',
        timeStyle: 'short'
    }).format(new Date(tanggal));
}

function filterData() {
    router.get('/arsip', {
        tahun: tahun.value
    }, {
        preserveState: true,
        replace: true
    });
}

function resetFilter() {
    tahun.value = '';
    sort.value = 'disetujui_pada';
    direction.value = 'desc';

    router.get('/arsip', {
    }, {
        preserveState: false,
        replace: true
    });
}

function sortIcon(field) {
    if (sort.value !== field) return '';

    return direction.value === 'asc' ? '↑' : '↓';
}

function sortData(field) {
    if (sort.value === field) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = field;
        direction.value = 'asc';
    }

    router.get('/arsip', {
        tahun: tahun.value,
        sort: sort.value,
        direction: direction.value
    }, {
        preserveState: true,
        replace: true
    });
}
</script>

<template>
    <div>
        <h1 class="text-xl font-bold mb-4">Arsip Program</h1>

        <a
            href="/ranwal/export"
            class="bg-green-600 text-white px-4 py-2"
        >
            Export Excel
        </a>

        <div class="mb-4 flex gap-2">
            <input
                v-model="tahun"
                type="number"
                placeholder="Filter Tahun (contoh: 2026)"
                class="border p-2 rounded"
            />

            <button
                @click="filterData"
                class="bg-blue-500 text-white px-4 py-2 rounded"
            >
                Filter
            </button>

            <button
                @click="resetFilter"
                class="bg-gray-500 text-white px-4 py-2 rounded"
            >
                Reset
            </button>
        </div>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-200 text-black">
                    <th class="p-2 text-left">Nama Program</th>
                    <th
                        class="p-2 text-left cursor-pointer"
                        @click="sortData('tahun')"
                    >
                        Tahun {{ sortIcon('tahun') }}
                    </th>
                    <th
                        class="p-2 text-left cursor-pointer"
                        @click="sortData('total_pagu')"
                    >
                        Total Anggaran {{ sortIcon('total_pagu') }}
                    </th>
                    <th class="p-2 text-left">Disetujui Oleh</th>
                    <th class="p-2 text-left">Tanggal</th>
                    <th class="p-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="programs.length === 0">
                    <td colspan="6" class="p-4 text-center text-gray-400">
                        Data arsip tidak ditemukan
                    </td>
                </tr>
                <tr
                    v-for="program in programs"
                    :key="program.id"
                    class="border-t hover:bg-gray-800"
                >
                    <td class="p-2">
                        {{ program.nama_program }}
                    </td>
                    <td class="p-2">
                        {{ program.tahun }}
                    </td>
                    <td class="p-2">
                        {{ formatRupiah(program.total_pagu) }}
                    </td>
                    <td class="p-2">
                         {{ program.approver?.name || '-' }}
                    </td>
                    <td class="p-2">
                        {{formatTanggal(program.disetujui_pada) }}
                    </td>
                    <td class="p-2">
                        <Link
                            :href="`/program/${program.id}`"
                            class="text-blue-400 hover:underline"
                        >
                            Detail
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

