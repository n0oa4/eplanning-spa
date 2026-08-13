<script setup>
import { router } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ArsipDetailDialog from '@/components/ArsipDetailDialog.vue';

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

/* ====================== MODAL DETAIL ====================== */
const showDetailDialog = ref(false);
const selectedProgram = ref(null);

const highlightActivityId = ref(null);
const highlightSubId = ref(null);

function openDetail(program) {
    selectedProgram.value = program;
    showDetailDialog.value = true;

    if (highlightedProgramId.value === program.id && !highlightActivityId.value && !highlightSubId.value) {
        highlightedProgramId.value = null;
    }
}

// Sinkronisasi otomatis data terbaru ke modal yang sedang terbuka
watch(() => props.programs, (newPrograms) => {
    if (!selectedProgram.value) return;
    const updated = newPrograms.find(p => p.id === selectedProgram.value.id);
    if (updated) {
        selectedProgram.value = updated;
    }
}, { deep: true });

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

/* ====================== HIGHLIGHT DARI SEARCH ====================== */
const highlightedProgramId = ref(null);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    const highlightProgram = params.get('highlight_program');
    const highlightActivity = params.get('highlight_activity');
    const highlightSub = params.get('highlight_sub');

    if (!highlightProgram) return;

    highlightedProgramId.value = Number(highlightProgram);

    if (highlightActivity || highlightSub) {
        // Yang dicari Kegiatan/Sub Kegiatan -> langsung buka modal Detail
        const program = props.programs.find(p => p.id === Number(highlightProgram));
        if (program) {
            highlightActivityId.value = highlightActivity ? Number(highlightActivity) : null;
            highlightSubId.value = highlightSub ? Number(highlightSub) : null;
            openDetail(program);
        }
    } else {
        // Yang dicari Program -> scroll ke baris-nya
        setTimeout(() => {
            const el = document.getElementById(`arsip-row-${highlightProgram}`);
            el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
});
</script>

<template>
    <div class="space-y-6 p-4">

        <h1 class="text-2xl font-semibold tracking-tight">Arsip Program</h1>

        <div class="flex items-center gap-2">
            <a
                :href="`/ranwal/print${tahun ? '?tahun=' + tahun : ''}`"
                target="_blank"
                class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 hover:shadow-md transition"
            >
                Print
            </a>

            <input
                v-model="tahun"
                type="number"
                placeholder="Filter Tahun (contoh: 2026)"
                class="rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground
                       placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring
                       focus:border-ring transition-colors"
            />

            <button
                @click="filterData"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 hover:shadow-md transition"
            >
                Filter
            </button>

            <button
                @click="resetFilter"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 hover:shadow-md transition"
            >
                Reset
            </button>
        </div>

        <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-muted text-foreground">
                        <th class="p-3 text-left font-medium">Nama Program</th>
                        <th
                            class="p-3 text-left font-medium cursor-pointer hover:text-primary transition"
                            @click="sortData('tahun')"
                        >
                            Tahun {{ sortIcon('tahun') }}
                        </th>
                        <th
                            class="p-3 text-left font-medium cursor-pointer hover:text-primary transition"
                            @click="sortData('total_pagu')"
                        >
                            Total Anggaran {{ sortIcon('total_pagu') }}
                        </th>
                        <th class="p-3 text-left font-medium">Tanggal</th>
                        <th class="p-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="programs.length === 0">
                        <td colspan="5" class="p-6 text-center text-muted-foreground">
                            Data arsip tidak ditemukan
                        </td>
                    </tr>
                    <tr
                        v-for="program in programs"
                        :key="program.id"
                        :id="`arsip-row-${program.id}`"
                        :class="[
                            'border-t transition-all duration-300',
                            highlightedProgramId === program.id
                                ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-2 ring-inset ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2]'
                                : 'border-border hover:bg-accent/50'
                        ]"
                    >
                        <td class="p-3 text-foreground">
                            {{ program.nama_program }}
                        </td>
                        <td class="p-3 text-foreground">
                            {{ program.tahun }}
                        </td>
                        <td class="p-3 text-emerald-500 font-medium">
                            {{ formatRupiah(program.total_pagu) }}
                        </td>
                        <td class="p-3 text-muted-foreground">
                            {{ formatTanggal(program.disetujui_pada) }}
                        </td>
                        <td class="p-3">
                            <button
                                @click="openDetail(program)"
                                :class="[
                                    'px-4 py-2 rounded-lg shadow hover:opacity-90 hover:shadow-md transition text-sm',
                                    highlightedProgramId === program.id
                                        ? 'bg-blue-600 text-white ring-2 ring-blue-400 ring-offset-2'
                                        : 'bg-blue-500 text-white'
                                ]"
                            >
                                Detail
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Detail Arsip -->
        <ArsipDetailDialog
            v-model:open="showDetailDialog"
            :program="selectedProgram"
            :highlight-activity-id="highlightActivityId"
            :highlight-sub-id="highlightSubId"
            @clear-highlight="() => { highlightActivityId = null; highlightSubId = null; highlightedProgramId = null }"
        />

    </div>
</template>
