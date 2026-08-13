<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    open: Boolean,
})

const emit = defineEmits(['update:open'])

const form = useForm({
    tahun: new Date().getFullYear(),
    kode_program: '',
    nama_program: '',
})

async function fetchNextCode(tahun) {
    if (!tahun) return
    try {
        const res = await fetch(`/program/next-code?tahun=${tahun}`)
        const data = await res.json()
        if (data.kode) {
            form.kode_program = data.kode
        }
    } catch (e) {
        // Diamkan saja — operator tetap bisa isi kode manual jika fetch gagal
    }
}

/* ====================== AUTO-FILL DARI DATA MASTER (REFERENSI EXCEL) ====================== */
const masterPrograms = ref([])
const isMatchedFromMaster = ref(false)

async function fetchMasterKode() {
    try {
        const res = await fetch('/master-kode')
        const data = await res.json()
        masterPrograms.value = data.programs || []
    } catch (e) {
        // Diamkan — auto-fill cuma bantuan, tidak wajib berhasil
    }
}

function tryAutoFillProgram() {
    const match = masterPrograms.value.find(p => p.kode_program === form.kode_program)
    if (match) {
        form.nama_program = match.nama_program
        isMatchedFromMaster.value = true
    } else {
        isMatchedFromMaster.value = false
    }
}

// Cek ulang tiap kali kode berubah (generate ATAU diketik manual)
watch(() => form.kode_program, tryAutoFillProgram)
// Cek ulang setelah data master selesai dimuat (jaga-jaga kalau kode sudah
// terisi lebih dulu sebelum fetch /master-kode selesai)
watch(masterPrograms, tryAutoFillProgram)

// Isi otomatis kode program setiap kali tahun berubah
watch(() => form.tahun, (tahun) => {
    fetchNextCode(tahun)
})

// Generate kode pertama kali saat dialog dibuka (tahun sudah terisi default)
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        fetchNextCode(form.tahun)
        fetchMasterKode()
    }
})

const submit = () => {
    form.post('/program', {
        onSuccess: () => {
            form.reset()
            emit('update:open', false)
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
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Tambah Program</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Tahun
                    </label>
                    <input
                        v-model="form.tahun"
                        type="number"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p v-if="form.errors.tahun" class="text-xs text-destructive">
                        {{ form.errors.tahun }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Kode Program
                    </label>
                    <input
                        v-model="form.kode_program"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p class="text-xs text-muted-foreground">
                        Dibuat otomatis berdasarkan tahun — boleh diedit jika perlu
                    </p>
                    <p v-if="form.errors.kode_program" class="text-xs text-destructive">
                        {{ form.errors.kode_program }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Nama Program
                    </label>
                    <input
                        v-model="form.nama_program"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p v-if="isMatchedFromMaster" class="text-xs text-emerald-600 dark:text-emerald-400">
                        Terisi otomatis dari data referensi Renja
                    </p>
                    <p v-if="form.errors.nama_program" class="text-xs text-destructive">
                        {{ form.errors.nama_program }}
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