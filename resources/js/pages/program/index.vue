<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import ProgramFormDialog from '@/components/ProgramFormDialog.vue'
import ProgramDetailDialog from '@/components/ProgramDetailDialog.vue'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    programs: Array,
    auth: Object
})

const isKabid = computed(() =>
    props.auth?.user?.roles?.some(r => r.name === 'kabid')
)

const isOperator = computed(() =>
    props.auth?.user?.roles?.some(r => r.name === 'operator')
)

const statusLabel = (status) => {
    const map = {
        draft: 'Draft',
        verifikasi: 'Menunggu Konfirmasi SIPD',
        disetujui: 'Disetujui',
        ditolak: 'Ditolak',
        diajukan_ulang: 'Menunggu Review Ulang Kabid',
    }
    return map[status] || status
}

const statusBadgeClass = (status) => {
    const map = {
        draft: 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
        verifikasi: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400',
        disetujui: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400',
        ditolak: 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400',
        diajukan_ulang: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400',
    }
    return map[status] || 'bg-zinc-200 text-zinc-700'
}

const showCreateDialog = ref(false)

/* ====================== MODAL DETAIL ====================== */
const showDetailDialog = ref(false)
const selectedProgram = ref(null)

const highlightActivityId = ref(null)
const highlightSubId = ref(null)

const openDetail = (program) => {
    selectedProgram.value = program
    showDetailDialog.value = true

    if (highlightedProgramId.value === program.id && !highlightActivityId.value && !highlightSubId.value) {
        highlightedProgramId.value = null
    }
}

watch(() => props.programs, (newPrograms) => {
    if (!selectedProgram.value) return

    const updated = newPrograms.find(p => p.id === selectedProgram.value.id)
    if (updated) {
        selectedProgram.value = updated
    }
}, { deep: true })

/* ====================== HIGHLIGHT DARI DASHBOARD / SEARCH ====================== */
const highlightedProgramId = ref(null)

/* ====================== AUTO POLLING ====================== */
let pollingTimer = null
let isRefreshing = false
let isNavigating = false
let removeStartListener = null
let removeFinishListener = null

function startPolling() {
    // Track semua request Inertia yang sedang aktif (form submit, delete, approval, dll)
    // Polling akan skip otomatis kalau ada request lain yang sedang berjalan
    removeStartListener = router.on('start', () => { isNavigating = true })
    removeFinishListener = router.on('finish', () => { isNavigating = false })

    pollingTimer = setInterval(() => {
        // Skip jika polling sebelumnya belum selesai, atau ada request Inertia lain aktif
        if (isRefreshing || isNavigating) return

        isRefreshing = true
        router.reload({
            only: ['programs'],
            onFinish: () => { isRefreshing = false },
        })
    }, 1000)
}

function stopPolling() {
    if (pollingTimer) {
        clearInterval(pollingTimer)
        pollingTimer = null
    }
    if (removeStartListener) removeStartListener()
    if (removeFinishListener) removeFinishListener()
}

onMounted(() => {
    const params = new URLSearchParams(window.location.search)

    const legacyHighlight = params.get('highlight')
    const highlightProgram = params.get('highlight_program') || legacyHighlight
    const highlightActivity = params.get('highlight_activity')
    const highlightSub = params.get('highlight_sub')

    if (highlightProgram) {
        highlightedProgramId.value = Number(highlightProgram)

        if (highlightActivity || highlightSub) {
            const program = props.programs.find(p => p.id === Number(highlightProgram))
            if (program) {
                highlightActivityId.value = highlightActivity ? Number(highlightActivity) : null
                highlightSubId.value = highlightSub ? Number(highlightSub) : null
                openDetail(program)
            }
        } else {
            setTimeout(() => {
                const el = document.getElementById(`program-card-${highlightProgram}`)
                el?.scrollIntoView({ behavior: 'smooth', block: 'center' })
            }, 100)
        }
    }

    startPolling()
})

onUnmounted(() => {
    stopPolling()
})
</script>

<template>
    <div class="space-y-6 p-4">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                Daftar Program
            </h1>

            <button
                v-if="isOperator"
                @click="showCreateDialog = true"
                class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition"
            >
                + Tambah Program
            </button>
        </div>

        <!-- List Program -->
        <div v-if="programs.length" class="grid gap-4">
            <div
                v-for="program in programs"
                :key="program.id"
                :id="`program-card-${program.id}`"
                :class="[
                    'rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-300',
                    highlightedProgramId === program.id
                        ? 'border-2 border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-4 ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2]'
                        : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-500'
                ]"
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

                        <p class="text-sm text-zinc-500 flex items-center gap-2">
                            Status:
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusBadgeClass(program.status)]">
                                {{ statusLabel(program.status) }}
                            </span>
                        </p>

                        <div class="text-emerald-500 font-medium pt-2">
                            Total Pagu:
                            Rp {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
                        </div>

                        <!-- Kabid: Setujui Rencana (draft/diajukan_ulang → verifikasi) -->
                        <button
                            v-if="(program.status === 'draft' || program.status === 'diajukan_ulang') && isKabid"
                            @click="router.post(`/program/${program.id}/verifikasi`, {}, { preserveScroll: true })"
                            class="bg-yellow-500 text-white px-3 py-1 rounded shadow hover:opacity-90 hover:shadow-md transition"
                        >
                            Setujui Rencana
                        </button>

                        <!-- Operator: Konfirmasi Input SIPD (verifikasi → disetujui → arsip) -->
                        <button
                            v-if="program.status === 'verifikasi' && isOperator"
                            @click="router.post(`/program/${program.id}/konfirmasi`, {}, { preserveScroll: true })"
                            class="bg-green-600 text-white px-3 py-1 rounded shadow hover:opacity-90 hover:shadow-md transition"
                        >
                            Konfirmasi
                        </button>

                        <!-- Operator: Ajukan Ulang (ditolak → diajukan_ulang) -->
                        <button
                            v-if="program.status === 'ditolak' && isOperator"
                            @click="router.post(`/program/${program.id}/ajukan`, {}, { preserveScroll: true })"
                            class="bg-orange-600 text-white px-3 py-1 rounded shadow hover:opacity-90 hover:shadow-md transition"
                        >
                            Ajukan
                        </button>
                    </div>

                    <!-- Tombol Detail -->
                    <button
                        @click="openDetail(program)"
                        :class="[
                            'px-4 py-2 rounded-lg shadow hover:opacity-90 transition text-sm shrink-0',
                            program.status === 'ditolak' && isOperator
                                ? 'bg-red-600 text-white ring-2 ring-red-400 ring-offset-2 animate-pulse'
                                : highlightedProgramId === program.id
                                    ? 'bg-blue-600 text-white ring-2 ring-blue-400 ring-offset-2'
                                    : 'bg-blue-500 text-white'
                        ]"
                    >
                        Detail
                    </button>

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

        <!-- Dialog Tambah Program -->
        <ProgramFormDialog v-model:open="showCreateDialog" />

        <!-- Dialog Detail Program -->
        <ProgramDetailDialog
            v-model:open="showDetailDialog"
            :program="selectedProgram"
            :highlight-activity-id="highlightActivityId"
            :highlight-sub-id="highlightSubId"
            @clear-highlight="() => { highlightActivityId = null; highlightSubId = null; highlightedProgramId = null }"
        />

    </div>
</template>