<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    notes: { type: Array, default: () => [] },
    programId: { type: Number, required: true },
    notableType: { type: String, required: true }, // 'program' | 'activity' | 'sub_activity'
    notableId: { type: Number, required: true },
    canAddNote: { type: Boolean, default: false },
    canConfirmNote: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const newCatatan = ref('')
const submittingNote = ref(false)
const confirmingId = ref(null)

const statusBadge = (status) => {
    const map = {
        terbuka: { label: 'Menunggu Diperbaiki', class: 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400' },
        dikonfirmasi_operator: { label: 'Menunggu Review Kabid', class: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' },
        selesai: { label: 'Selesai', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' },
    }
    return map[status] || { label: status, class: 'bg-zinc-100 text-zinc-700' }
}

function submitNote() {
    if (!newCatatan.value.trim() || submittingNote.value) return
    submittingNote.value = true

    router.post('/revision-notes', {
        program_id: props.programId,
        notable_type: props.notableType,
        notable_id: props.notableId,
        catatan: newCatatan.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newCatatan.value = ''
        },
        onFinish: () => {
            submittingNote.value = false
        },
    })
}

function confirmNote(noteId) {
    confirmingId.value = noteId
    router.post(`/revision-notes/${noteId}/konfirmasi`, {}, {
        preserveScroll: true,
        onFinish: () => {
            confirmingId.value = null
        },
    })
}
</script>

<template>
    <div
        class="absolute z-[80] -top-3 left-1/2 -translate-x-1/2 -translate-y-full w-72 max-w-[80vw]"
        @click.stop
    >
        <div class="relative rounded-xl border border-red-300 dark:border-red-800 bg-white dark:bg-zinc-900 shadow-lg p-3 space-y-3">

            <!-- Tombol tutup -->
            <button
                @click="emit('close')"
                class="absolute top-2 right-2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs leading-none"
            >
                ✕
            </button>

            <p class="text-xs font-semibold text-foreground pr-4">Catatan Perbaikan</p>

            <!-- Daftar catatan yang sudah ada -->
            <div v-if="notes.length" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                <div
                    v-for="note in notes"
                    :key="note.id"
                    class="border border-border rounded-lg p-2 text-xs space-y-1 bg-muted/40"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span :class="['px-2 py-0.5 rounded-full font-medium', statusBadge(note.status).class]">
                            {{ statusBadge(note.status).label }}
                        </span>
                        <span class="text-zinc-400 shrink-0">{{ note.creator?.name }}</span>
                    </div>
                    <p class="text-foreground">{{ note.catatan }}</p>

                    <button
                        v-if="canConfirmNote && note.status === 'terbuka'"
                        @click="confirmNote(note.id)"
                        :disabled="confirmingId === note.id"
                        class="mt-1 bg-green-600 text-white px-2 py-1 rounded text-xs shadow hover:opacity-90 transition disabled:opacity-50"
                    >
                        {{ confirmingId === note.id ? 'Menyimpan...' : 'Konfirmasi Sudah Diperbaiki' }}
                    </button>
                </div>
            </div>
            <div v-else class="text-xs text-muted-foreground">
                Belum ada catatan pada bagian ini.
            </div>

            <!-- Form tambah catatan (khusus Kabid saat review) -->
            <div v-if="canAddNote" class="space-y-2 pt-2 border-t border-border">
                <textarea
                    v-model="newCatatan"
                    rows="2"
                    placeholder="Tulis catatan perbaikan..."
                    class="w-full rounded-md border border-input bg-background px-2 py-1.5 text-xs text-foreground resize-none"
                ></textarea>
                <button
                    @click="submitNote"
                    :disabled="!newCatatan.trim() || submittingNote"
                    class="bg-red-600 text-white px-3 py-1 rounded text-xs shadow hover:opacity-90 transition disabled:opacity-50"
                >
                    {{ submittingNote ? 'Menyimpan...' : 'Tambah Catatan' }}
                </button>
            </div>

            <!-- Ekor gelembung -->
            <div class="absolute left-1/2 -bottom-1.5 -translate-x-1/2 w-3 h-3 bg-white dark:bg-zinc-900 border-r border-b border-red-300 dark:border-red-800 rotate-45"></div>
        </div>
    </div>
</template>