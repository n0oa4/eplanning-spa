<script setup>
import { watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    open: Boolean,
    program: Object,
    highlightActivityId: { type: [Number, null], default: null },
    highlightSubId: { type: [Number, null], default: null },
})

const emit = defineEmits(['update:open', 'clear-highlight'])

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(angka || 0);
}

function clearActivityHighlight(activityId) {
    if (props.highlightActivityId === activityId && !props.highlightSubId) {
        emit('clear-highlight')
    }
}

function clearSubHighlight(subId) {
    if (props.highlightSubId === subId) {
        emit('clear-highlight')
    }
}

// Saat modal dibuka dengan target highlight Kegiatan/Sub Kegiatan, scroll ke elemen itu
watch(() => props.open, (isOpen) => {
    if (isOpen && (props.highlightActivityId || props.highlightSubId)) {
        setTimeout(() => {
            const targetId = props.highlightSubId
                ? `arsip-sub-card-${props.highlightSubId}`
                : `arsip-activity-card-${props.highlightActivityId}`
            document.getElementById(targetId)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }, 150)
    }
})
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent
            v-if="program"
            class="sm:max-w-3xl max-h-[85vh] flex flex-col p-0 gap-0"
        >
            <DialogHeader class="px-6 pt-6 pb-2 shrink-0">
                <DialogTitle>{{ program.nama_program }}</DialogTitle>
            </DialogHeader>

            <div class="space-y-6 overflow-y-auto px-6 pb-6 flex-1">

                <p class="text-sm text-muted-foreground">Tahun: {{ program.tahun }}</p>

                <div class="bg-green-700 p-4 text-white rounded">
                    <strong>Total Pagu Program:</strong>
                    {{ formatRupiah(program.total_pagu) }}
                </div>

                <!-- DAFTAR KEGIATAN -->
                <h2 class="text-lg font-semibold text-foreground">Daftar Kegiatan</h2>

                <div v-if="!program.activities || program.activities.length === 0" class="text-muted-foreground text-sm">
                    Belum ada kegiatan
                </div>

                <div
                    v-for="activity in program.activities"
                    :key="activity.id"
                    :id="`arsip-activity-card-${activity.id}`"
                    @click="clearActivityHighlight(activity.id)"
                    :class="[
                        'border rounded-lg p-4 space-y-3 transition-all duration-300',
                        highlightActivityId === activity.id && !highlightSubId
                            ? 'border-2 border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-4 ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2] cursor-pointer'
                            : 'border-border bg-card'
                    ]"
                >
                    <h3 class="font-semibold text-foreground">{{ activity.nama_kegiatan }}</h3>
                    <div class="text-sm text-emerald-500">
                        Total Pagu: {{ formatRupiah(activity.total_pagu) }}
                    </div>

                    <!-- DAFTAR SUB KEGIATAN -->
                    <div v-if="activity.sub_activities && activity.sub_activities.length" class="space-y-2">
                        <div
                            v-for="sub in activity.sub_activities"
                            :key="sub.id"
                            :id="`arsip-sub-card-${sub.id}`"
                            @click.stop="clearSubHighlight(sub.id)"
                            :class="[
                                'border rounded-lg p-3 text-sm text-foreground space-y-1 transition-all duration-300',
                                highlightSubId === sub.id
                                    ? 'border-2 border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-4 ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2] cursor-pointer'
                                    : 'border-border bg-muted/50'
                            ]"
                        >
                            <div><strong>Kode:</strong> {{ sub.kode_sub_kegiatan }}</div>
                            <div><strong>Sub:</strong> {{ sub.nama_sub_kegiatan }}</div>
                            <div><strong>Pagu:</strong> {{ formatRupiah(sub.pagu_anggaran) }}</div>
                            <div><strong>Indikator:</strong> {{ sub.indikator }}</div>
                            <div><strong>Target:</strong> {{ sub.target }}</div>
                            <div><strong>Prioritas Provinsi:</strong> {{ sub.prioritas_provinsi || '-' }}</div>
                            <div><strong>Prioritas Kabupaten:</strong> {{ sub.prioritas_kabupaten || '-' }}</div>
                            <div><strong>Bidang Urusan:</strong> {{ sub.bidang_urusan || '-' }}</div>
                            <div><strong>N+1:</strong> {{ formatRupiah(sub.n1) }}</div>
                            <div><strong>N+2:</strong> {{ formatRupiah(sub.n2) }}</div>
                        </div>
                    </div>
                    <div v-else class="text-muted-foreground text-sm">
                        Belum ada sub kegiatan
                    </div>
                </div>

            </div>
        </DialogContent>
    </Dialog>
</template>
