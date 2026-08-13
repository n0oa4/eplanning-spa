<script setup>
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    open: Boolean,
    activityId: [String, Number],
    kodeKegiatan: String,
    jumlahSub: { type: Number, default: 0 },
})

const emit = defineEmits(['update:open'])

const form = useForm({
    kode_sub_kegiatan: '',
    nama_sub_kegiatan: '',
    pagu_anggaran: '',
    indikator: '',
    target: '',
    prioritas_provinsi: '',
    prioritas_kabupaten: '',
    bidang_urusan: '',
    n1: '',
    n2: '',
})

function generateKodeSub() {
    if (!props.kodeKegiatan) return ''
    const nomorUrut = String(props.jumlahSub + 1).padStart(4, '0')
    return `${props.kodeKegiatan}.${nomorUrut}`
}

/* ====================== AUTO-FILL DARI DATA MASTER (REFERENSI EXCEL) ====================== */
const masterSubActivities = ref([])
const isMatchedFromMaster = ref(false)

async function fetchMasterKode() {
    try {
        const res = await fetch('/master-kode')
        const data = await res.json()
        masterSubActivities.value = data.sub_activities || []
    } catch (e) {
        // Diamkan — auto-fill cuma bantuan, tidak wajib berhasil
    }
}

function tryAutoFillSub() {
    const match = masterSubActivities.value.find(s => s.kode_sub_kegiatan === form.kode_sub_kegiatan)
    if (!match) {
        isMatchedFromMaster.value = false
        return
    }

    isMatchedFromMaster.value = true

    form.nama_sub_kegiatan = match.nama_sub_kegiatan
    if (match.indikator) form.indikator = match.indikator
    if (match.target) form.target = match.target
    if (match.prioritas_provinsi) form.prioritas_provinsi = match.prioritas_provinsi
    if (match.prioritas_kabupaten) form.prioritas_kabupaten = match.prioritas_kabupaten
    if (match.bidang_urusan) form.bidang_urusan = match.bidang_urusan
    if (match.pagu_anggaran) form.pagu_anggaran = match.pagu_anggaran
    // n1 dan n2 tidak perlu diisi manual di sini — watcher pagu_anggaran
    // di bawah otomatis menghitung ulang begitu pagu_anggaran berubah
}

// Cek ulang tiap kali kode berubah (generate ATAU diketik manual)
watch(() => form.kode_sub_kegiatan, tryAutoFillSub)
// Cek ulang setelah data master selesai dimuat
watch(masterSubActivities, tryAutoFillSub)

// Isi otomatis kode sub kegiatan setiap kali dialog dibuka
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        form.kode_sub_kegiatan = generateKodeSub()
        fetchMasterKode()
    }
})

// Auto hitung N+1 dan N+2
watch(() => form.pagu_anggaran, (value) => {
    const pagu = parseFloat(value) || 0
    form.n1 = Math.round(pagu * 1.1)
    form.n2 = Math.round(pagu * 1.21)
})

const submit = () => {
    form.post(`/activity/${props.activityId}/sub`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            emit('update:open', false)
            router.reload({ preserveScroll: true })
        },
    })
}

const close = () => {
    form.reset()
    form.clearErrors()
    isMatchedFromMaster.value = false
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-lg max-h-[85vh] overflow-y-auto z-[60]" overlay-class="z-[60]">
            <DialogHeader>
                <DialogTitle>Tambah Sub Kegiatan</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Kode Sub Kegiatan</label>
                    <input
                        v-model="form.kode_sub_kegiatan"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                    <p class="text-xs text-muted-foreground">
                        Dibuat otomatis dari kode kegiatan — boleh diedit jika perlu
                    </p>
                    <p v-if="form.errors.kode_sub_kegiatan" class="text-xs text-destructive">{{ form.errors.kode_sub_kegiatan }}</p>
                </div>

                <p v-if="isMatchedFromMaster" class="text-xs text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 rounded-md px-3 py-2">
                    Kode ini cocok dengan data referensi Renja — field di bawah terisi otomatis, boleh diedit jika perlu.
                </p>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Nama Sub Kegiatan</label>
                    <input
                        v-model="form.nama_sub_kegiatan"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                    <p v-if="form.errors.nama_sub_kegiatan" class="text-xs text-destructive">{{ form.errors.nama_sub_kegiatan }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Pagu Anggaran</label>
                    <input
                        v-model="form.pagu_anggaran"
                        type="number"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                    <p v-if="form.errors.pagu_anggaran" class="text-xs text-destructive">{{ form.errors.pagu_anggaran }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Indikator</label>
                    <input
                        v-model="form.indikator"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                    <p v-if="form.errors.indikator" class="text-xs text-destructive">{{ form.errors.indikator }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Target</label>
                    <input
                        v-model="form.target"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                    <p v-if="form.errors.target" class="text-xs text-destructive">{{ form.errors.target }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Prioritas Provinsi</label>
                    <input
                        v-model="form.prioritas_provinsi"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Prioritas Kabupaten</label>
                    <input
                        v-model="form.prioritas_kabupaten"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Bidang Urusan</label>
                    <input
                        v-model="form.bidang_urusan"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-muted-foreground">N+1 (otomatis)</label>
                        <input
                            v-model="form.n1"
                            type="number"
                            readonly
                            class="w-full rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-muted-foreground">N+2 (otomatis)</label>
                        <input
                            v-model="form.n2"
                            type="number"
                            readonly
                            class="w-full rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 rounded-lg text-sm text-muted-foreground hover:bg-accent transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm shadow
                               hover:opacity-90 hover:shadow-md transition disabled:opacity-50"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>