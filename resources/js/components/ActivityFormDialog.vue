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
    programId: [String, Number],
    kodeProgram: String,
    jumlahKegiatan: { type: Number, default: 0 },
})

const emit = defineEmits(['update:open'])

const form = useForm({
    kode_kegiatan: '',
    nama_kegiatan: '',
})

function generateKodeKegiatan() {
    if (!props.kodeProgram) return ''
    const nomorUrut = String(props.jumlahKegiatan + 1).padStart(2, '0')
    return `${props.kodeProgram}.2.${nomorUrut}`
}

/* ====================== AUTO-FILL DARI DATA MASTER (REFERENSI EXCEL) ====================== */
const masterActivities = ref([])
const isMatchedFromMaster = ref(false)

async function fetchMasterKode() {
    try {
        const res = await fetch('/master-kode')
        const data = await res.json()
        masterActivities.value = data.activities || []
    } catch (e) {
        // Diamkan — auto-fill cuma bantuan, tidak wajib berhasil
    }
}

function tryAutoFillActivity() {
    const match = masterActivities.value.find(a => a.kode_kegiatan === form.kode_kegiatan)
    if (match) {
        form.nama_kegiatan = match.nama_kegiatan
        isMatchedFromMaster.value = true
    } else {
        isMatchedFromMaster.value = false
    }
}

// Cek ulang tiap kali kode berubah (generate ATAU diketik manual)
watch(() => form.kode_kegiatan, tryAutoFillActivity)
// Cek ulang setelah data master selesai dimuat
watch(masterActivities, tryAutoFillActivity)

// Isi otomatis kode kegiatan setiap kali dialog dibuka
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        form.kode_kegiatan = generateKodeKegiatan()
        fetchMasterKode()
    }
})

const submit = () => {
    form.post(`/program/${props.programId}/activity`, {
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
        <DialogContent class="sm:max-w-md z-[60]" overlay-class="z-[60]">
            <DialogHeader>
                <DialogTitle>Tambah Kegiatan</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Kode Kegiatan
                    </label>
                    <input
                        v-model="form.kode_kegiatan"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p class="text-xs text-muted-foreground">
                        Dibuat otomatis dari kode program — boleh diedit jika perlu
                    </p>
                    <p v-if="form.errors.kode_kegiatan" class="text-xs text-destructive">
                        {{ form.errors.kode_kegiatan }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Nama Kegiatan
                    </label>
                    <input
                        v-model="form.nama_kegiatan"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p v-if="isMatchedFromMaster" class="text-xs text-emerald-600 dark:text-emerald-400">
                        Terisi otomatis dari data referensi Renja
                    </p>
                    <p v-if="form.errors.nama_kegiatan" class="text-xs text-destructive">
                        {{ form.errors.nama_kegiatan }}
                    </p>
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