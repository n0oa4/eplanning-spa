<script setup>
import { useForm, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import ActivityFormDialog from '@/components/ActivityFormDialog.vue'
import SubActivityFormDialog from '@/components/SubActivityFormDialog.vue'
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue'
import RevisionNoteBubble from '@/components/RevisionNoteBubble.vue'

const props = defineProps({
    open: Boolean,
    program: Object,
    highlightActivityId: { type: [Number, null], default: null },
    highlightSubId: { type: [Number, null], default: null },
})

const emit = defineEmits(['update:open', 'clear-highlight'])

const close = () => emit('update:open', false)

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

/* ====================== EDIT PROGRAM ====================== */
const editingProgram = ref(false)
const programForm = useForm({
    kode_program: '',
    nama_program: '',
    tahun: '',
})

watch(() => props.program, (program) => {
    if (program) {
        programForm.kode_program = program.kode_program
        programForm.nama_program = program.nama_program
        programForm.tahun = program.tahun
    }
}, { immediate: true })

// Saat modal dibuka dengan target highlight Kegiatan/Sub Kegiatan, scroll ke elemen itu
watch(() => props.open, (isOpen) => {
    if (isOpen && (props.highlightActivityId || props.highlightSubId)) {
        setTimeout(() => {
            const targetId = props.highlightSubId
                ? `sub-card-${props.highlightSubId}`
                : `activity-card-${props.highlightActivityId}`
            document.getElementById(targetId)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }, 150)
    }
})

const updateProgram = () => {
    programForm.put(`/program/${props.program.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingProgram.value = false
            router.reload({ preserveScroll: true })
        }
    })
}

/* ====================== KONFIRMASI HAPUS (REUSABLE) ====================== */
const showConfirmDialog = ref(false)
const confirmMessage = ref('')
const confirmAction = ref(null)

function askConfirm(message, action) {
    confirmMessage.value = message
    confirmAction.value = action
    showConfirmDialog.value = true
}

function runConfirmedAction() {
    if (confirmAction.value) {
        confirmAction.value()
    }
}

const deleteProgram = () => {
    askConfirm('Apakah Anda yakin ingin menghapus program ini? Tindakan ini tidak dapat dibatalkan.', () => {
        useForm().delete(`/program/${props.program.id}`, {
            onSuccess: () => close()
        })
    })
}

/* ====================== ROLE ====================== */
const page = usePage()

const isKabid = computed(() =>
    page.props.auth?.user?.roles?.some(r => r.name === 'kabid')
)

const isOperator = computed(() =>
    page.props.auth?.user?.roles?.some(r => r.name === 'operator')
)

/* ====================== CATATAN PERBAIKAN ====================== */
// Program bisa diedit Operator saat draft (belum pernah direview) ATAU ditolak (sedang diperbaiki)
const isEditable = computed(() =>
    props.program && ['draft', 'ditolak'].includes(props.program.status)
)

// Kabid bisa menambah catatan saat program sedang direview: draft (baru) atau diajukan_ulang (resubmit)
const canReview = computed(() =>
    props.program && ['draft', 'diajukan_ulang'].includes(props.program.status)
)

const allNotes = computed(() => {
    if (!props.program) return []
    const notes = [...(props.program.notes || [])]
    for (const activity of props.program.activities || []) {
        notes.push(...(activity.notes || []))
        for (const sub of activity.sub_activities || []) {
            notes.push(...(sub.notes || []))
        }
    }
    return notes
})

// Dipakai untuk aktif/nonaktifkan tombol "Tolak Program"
const hasOpenNotes = computed(() =>
    allNotes.value.some(n => n.status === 'terbuka')
)

// Item masih perlu perhatian selama belum ditutup ('selesai') oleh Kabid
function hasUnresolvedNotes(notes) {
    return (notes || []).some(n => n.status !== 'selesai')
}

// Bubble bisa dibuka untuk: Kabid saat review (nambah catatan baru),
// atau siapa saja kalau item itu sudah punya catatan (lihat riwayat)
function canOpenNotePopover(notes) {
    if (isKabid.value && canReview.value) return true
    return (notes || []).length > 0
}

const activeNoteTarget = ref(null) // { type: 'program' | 'activity' | 'sub_activity', id: number }

function toggleNoteTarget(type, id) {
    if (activeNoteTarget.value?.type === type && activeNoteTarget.value?.id === id) {
        activeNoteTarget.value = null
    } else {
        activeNoteTarget.value = { type, id }
    }
}

function isNoteTargetActive(type, id) {
    return activeNoteTarget.value?.type === type && activeNoteTarget.value?.id === id
}

const tolakProgram = () => {
    askConfirm('Apakah Anda yakin ingin menolak program ini? Operator akan diminta memperbaiki sesuai catatan yang diberikan.', () => {
        router.post(`/program/${props.program.id}/tolak`, {}, {
            preserveScroll: true,
            onSuccess: () => close()
        })
    })
}

/* ====================== KEGIATAN ====================== */
const showActivityDialog = ref(false)

const editingActivityId = ref(null)
const activityEditForm = useForm({ kode_kegiatan: '', nama_kegiatan: '' })

const startEditActivity = (activity) => {
    editingActivityId.value = activity.id
    activityEditForm.kode_kegiatan = activity.kode_kegiatan
    activityEditForm.nama_kegiatan = activity.nama_kegiatan
}

const updateActivity = () => {
    activityEditForm.put(`/activity/${editingActivityId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingActivityId.value = null
            activityEditForm.reset()
            router.reload({ preserveScroll: true })
        }
    })
}

const deleteActivity = (id) => {
    askConfirm('Apakah Anda yakin ingin menghapus kegiatan ini? Sub kegiatan di dalamnya akan ikut terhapus.', () => {
        useForm().delete(`/activity/${id}`, {
            preserveScroll: true,
            onSuccess: () => router.reload({ preserveScroll: true })
        })
    })
}

function onActivityCardClick(activity) {
    clearActivityHighlight(activity.id)
    if (canOpenNotePopover(activity.notes)) {
        toggleNoteTarget('activity', activity.id)
    }
}

/* ====================== SUB KEGIATAN ====================== */
const showSubDialog = ref(false)
const activeActivityIdForSub = ref(null)
const activeKodeKegiatanForSub = ref('')
const activeJumlahSubForSub = ref(0)

const openSubDialog = (activity) => {
    activeActivityIdForSub.value = activity.id
    activeKodeKegiatanForSub.value = activity.kode_kegiatan
    activeJumlahSubForSub.value = activity.sub_activities?.length || 0
    showSubDialog.value = true
}

const editingId = ref(null)
const editForm = useForm({
    kode_sub_kegiatan: '', nama_sub_kegiatan: '', pagu_anggaran: '',
    indikator: '', target: '', prioritas_provinsi: '', prioritas_kabupaten: '',
    bidang_urusan: '', n1: '', n2: ''
})

const startEdit = (sub) => {
    editingId.value = sub.id
    editForm.kode_sub_kegiatan = sub.kode_sub_kegiatan
    editForm.nama_sub_kegiatan = sub.nama_sub_kegiatan
    editForm.pagu_anggaran = sub.pagu_anggaran
    editForm.indikator = sub.indikator
    editForm.target = sub.target
    editForm.prioritas_provinsi = sub.prioritas_provinsi || ''
    editForm.prioritas_kabupaten = sub.prioritas_kabupaten || ''
    editForm.bidang_urusan = sub.bidang_urusan || ''
}

const updateSub = () => {
    editForm.put(`/sub/${editingId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null
            editForm.reset()
            router.reload({ preserveScroll: true })
        }
    })
}

const deleteSub = (id) => {
    askConfirm('Apakah Anda yakin ingin menghapus sub kegiatan ini? Tindakan ini tidak dapat dibatalkan.', () => {
        useForm().delete(`/sub/${id}`, {
            preserveScroll: true,
            onSuccess: () => router.reload({ preserveScroll: true })
        })
    })
}

function onSubCardClick(sub) {
    clearSubHighlight(sub.id)
    if (canOpenNotePopover(sub.notes)) {
        toggleNoteTarget('sub_activity', sub.id)
    }
}

watch(() => editForm.pagu_anggaran, (value) => {
    let pagu = parseFloat(value) || 0
    editForm.n1 = Math.round(pagu * 1.1)
    editForm.n2 = Math.round(pagu * 1.21)
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

                <!-- MODE VIEW PROGRAM -->
                <div v-if="!editingProgram">
                    <div class="flex items-center flex-wrap gap-2 mb-2">
                        <button
                            v-if="isEditable"
                            @click="editingProgram = true"
                            class="bg-yellow-500 px-3 py-1 rounded text-black text-sm shadow hover:opacity-90 hover:shadow-md transition"
                        >
                            Edit Program
                        </button>
                        <button
                            v-if="isEditable && program.activities.length === 0"
                            @click="deleteProgram"
                            class="bg-red-600 px-3 py-1 rounded text-white text-sm shadow hover:opacity-90 hover:shadow-md transition"
                        >
                            Hapus Program
                        </button>

                        <!-- Kabid: Tolak Program (aktif hanya jika ada catatan terbuka) -->
                        <button
                            v-if="isKabid && canReview"
                            @click="tolakProgram"
                            :disabled="!hasOpenNotes"
                            :class="[
                                'px-3 py-1 rounded text-white text-sm shadow transition',
                                hasOpenNotes
                                    ? 'bg-red-600 hover:opacity-90 hover:shadow-md'
                                    : 'bg-red-600/40 cursor-not-allowed'
                            ]"
                        >
                            Tolak Program
                        </button>

                        <!-- Catatan yang menempel langsung pada Program -->
                        <button
                            v-if="canOpenNotePopover(program.notes)"
                            @click.stop="toggleNoteTarget('program', program.id)"
                            :class="[
                                'relative px-3 py-1 rounded text-sm shadow transition',
                                hasUnresolvedNotes(program.notes)
                                    ? 'bg-red-600 text-white animate-pulse hover:opacity-90'
                                    : 'bg-zinc-500 text-white hover:opacity-90'
                            ]"
                        >
                            Catatan Program
                            <RevisionNoteBubble
                                v-if="isNoteTargetActive('program', program.id)"
                                :notes="program.notes || []"
                                :program-id="program.id"
                                notable-type="program"
                                :notable-id="program.id"
                                :can-add-note="isKabid && canReview"
                                :can-confirm-note="isOperator && program.status === 'ditolak'"
                                @close="activeNoteTarget = null"
                            />
                        </button>
                    </div>
                </div>

                <!-- MODE EDIT PROGRAM -->
                <div v-else class="space-y-2">
                    <input v-model="programForm.kode_program" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Kode Program" />
                    <input v-model="programForm.nama_program" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Nama Program" />
                    <input v-model="programForm.tahun" type="number" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Tahun" />
                    <div class="space-x-2">
                        <button @click="updateProgram" class="bg-green-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Simpan</button>
                        <button @click="editingProgram = false" class="bg-gray-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Batal</button>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground">Tahun: {{ program.tahun }}</p>

                <div class="bg-green-700 p-4 text-white rounded">
                    <strong>Total Pagu Program:</strong>
                    Rp. {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
                </div>

                <!-- DAFTAR KEGIATAN -->
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-foreground">Daftar Kegiatan</h2>
                    <button
                        v-if="isEditable"
                        @click="showActivityDialog = true"
                        class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition text-sm"
                    >
                        + Tambah Kegiatan
                    </button>
                </div>

                <div v-if="program.activities.length === 0" class="text-muted-foreground text-sm">
                    Belum ada kegiatan
                </div>

                <div
                    v-for="activity in program.activities"
                    :key="activity.id"
                    :id="`activity-card-${activity.id}`"
                    @click="onActivityCardClick(activity)"
                    :class="[
                        'border rounded-lg p-4 space-y-3 transition-all duration-300 relative',
                        hasUnresolvedNotes(activity.notes)
                            ? 'border-2 border-red-500 bg-red-50/60 dark:bg-red-950/30 ring-4 ring-red-500/20 animate-pulse'
                            : highlightActivityId === activity.id && !highlightSubId
                                ? 'border-2 border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-4 ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2]'
                                : 'border-border bg-card',
                        canOpenNotePopover(activity.notes) ? 'cursor-pointer' : ''
                    ]"
                >
                    <RevisionNoteBubble
                        v-if="isNoteTargetActive('activity', activity.id)"
                        :notes="activity.notes || []"
                        :program-id="program.id"
                        notable-type="activity"
                        :notable-id="activity.id"
                        :can-add-note="isKabid && canReview"
                        :can-confirm-note="isOperator && program.status === 'ditolak'"
                        @close="activeNoteTarget = null"
                    />

                    <!-- View Mode Kegiatan -->
                    <div v-if="editingActivityId !== activity.id">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-foreground">{{ activity.nama_kegiatan }}</h3>
                            <button
                                v-if="isEditable"
                                @click.stop="openSubDialog(activity)"
                                class="bg-green-500 text-white px-3 py-1 rounded-lg shadow hover:opacity-90 transition text-xs"
                            >
                                + Tambah Sub
                            </button>
                        </div>
                        <div class="text-sm text-emerald-500 mb-2">
                            Total Pagu: Rp {{ activity.total_pagu?.toLocaleString('id-ID') || 0 }}
                        </div>
                        <div class="space-x-2 mb-3">
                            <button v-if="isEditable" @click.stop="startEditActivity(activity)" class="bg-yellow-500 px-3 py-1 rounded text-black text-xs shadow hover:opacity-90 hover:shadow-md transition">Edit</button>
                            <button v-if="isEditable" @click.stop="deleteActivity(activity.id)" class="bg-red-600 px-3 py-1 rounded text-white text-xs shadow hover:opacity-90 hover:shadow-md transition">Hapus</button>
                        </div>
                    </div>

                    <!-- Edit Mode Kegiatan -->
                    <div v-else class="space-y-2" @click.stop>
                        <input v-model="activityEditForm.kode_kegiatan" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" />
                        <input v-model="activityEditForm.nama_kegiatan" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" />
                        <div class="space-x-2">
                            <button @click="updateActivity" class="bg-green-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Simpan</button>
                            <button @click="editingActivityId = null" class="bg-gray-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Batal</button>
                        </div>
                    </div>

                    <!-- DAFTAR SUB KEGIATAN -->
                    <div v-if="activity.sub_activities && activity.sub_activities.length" class="space-y-2">
                        <div
                            v-for="sub in activity.sub_activities"
                            :key="sub.id"
                            :id="`sub-card-${sub.id}`"
                            @click.stop="onSubCardClick(sub)"
                            :class="[
                                'border rounded-lg p-3 transition-all duration-300 relative',
                                hasUnresolvedNotes(sub.notes)
                                    ? 'border-2 border-red-500 bg-red-50/60 dark:bg-red-950/30 ring-4 ring-red-500/20 animate-pulse'
                                    : highlightSubId === sub.id
                                        ? 'border-2 border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 ring-4 ring-blue-500/20 animate-[pulse_1.2s_ease-in-out_2]'
                                        : 'border-border bg-muted/50',
                                canOpenNotePopover(sub.notes) ? 'cursor-pointer' : ''
                            ]"
                        >
                            <RevisionNoteBubble
                                v-if="isNoteTargetActive('sub_activity', sub.id)"
                                :notes="sub.notes || []"
                                :program-id="program.id"
                                notable-type="sub_activity"
                                :notable-id="sub.id"
                                :can-add-note="isKabid && canReview"
                                :can-confirm-note="isOperator && program.status === 'ditolak'"
                                @close="activeNoteTarget = null"
                            />

                            <!-- View Mode Sub -->
                            <div v-if="editingId !== sub.id" class="text-sm text-foreground space-y-1">
                                <div><strong>Kode:</strong> {{ sub.kode_sub_kegiatan }}</div>
                                <div><strong>Sub:</strong> {{ sub.nama_sub_kegiatan }}</div>
                                <div><strong>Pagu:</strong> Rp {{ Number(sub.pagu_anggaran).toLocaleString('id-ID') }}</div>
                                <div><strong>Indikator:</strong> {{ sub.indikator }}</div>
                                <div><strong>Target:</strong> {{ sub.target }}</div>
                                <div><strong>Prioritas Provinsi:</strong> {{ sub.prioritas_provinsi || '-' }}</div>
                                <div><strong>Prioritas Kabupaten:</strong> {{ sub.prioritas_kabupaten || '-' }}</div>
                                <div><strong>Bidang Urusan:</strong> {{ sub.bidang_urusan || '-' }}</div>
                                <div><strong>N+1:</strong> Rp {{ Number(sub.n1 || 0).toLocaleString('id-ID') }}</div>
                                <div><strong>N+2:</strong> Rp {{ Number(sub.n2 || 0).toLocaleString('id-ID') }}</div>

                                <div class="pt-2 space-x-2">
                                    <button v-if="isEditable" @click.stop="startEdit(sub)" class="bg-yellow-500 px-3 py-1 rounded text-black text-xs shadow hover:opacity-90 hover:shadow-md transition">Edit</button>
                                    <button v-if="isEditable" @click.stop="deleteSub(sub.id)" class="bg-red-600 px-3 py-1 rounded text-white text-xs shadow hover:opacity-90 hover:shadow-md transition">Hapus</button>
                                </div>
                            </div>

                            <!-- Edit Mode Sub -->
                            <div v-else class="space-y-2" @click.stop>
                                <input v-model="editForm.kode_sub_kegiatan" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Kode Sub Kegiatan" />
                                <input v-model="editForm.nama_sub_kegiatan" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Nama Sub Kegiatan" />
                                <input v-model="editForm.pagu_anggaran" type="number" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Pagu Anggaran" />
                                <input v-model="editForm.indikator" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Indikator" />
                                <input v-model="editForm.target" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Target" />
                                <input v-model="editForm.prioritas_provinsi" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Prioritas Provinsi" />
                                <input v-model="editForm.prioritas_kabupaten" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Prioritas Kabupaten" />
                                <input v-model="editForm.bidang_urusan" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Bidang Urusan" />
                                <div class="grid grid-cols-2 gap-2">
                                    <input v-model="editForm.n1" type="number" class="w-full rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground" placeholder="N+1" readonly />
                                    <input v-model="editForm.n2" type="number" class="w-full rounded-md border border-input bg-muted px-3 py-2 text-sm text-muted-foreground" placeholder="N+2" readonly />
                                </div>
                                <div class="space-x-2">
                                    <button @click="updateSub" class="bg-green-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Simpan</button>
                                    <button @click="editingId = null" class="bg-gray-500 text-white px-3 py-1 rounded text-sm shadow hover:opacity-90 hover:shadow-md transition">Batal</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-muted-foreground text-sm">
                        Belum ada sub kegiatan
                    </div>
                </div>

            </div>
        </DialogContent>
    </Dialog>

    <!-- MODAL: Tambah Kegiatan -->
    <ActivityFormDialog
        v-model:open="showActivityDialog"
        :program-id="program?.id"
        :kode-program="program?.kode_program"
        :jumlah-kegiatan="program?.activities?.length || 0"
    />

    <!-- MODAL: Tambah Sub Kegiatan -->
    <SubActivityFormDialog
        v-model:open="showSubDialog"
        :activity-id="activeActivityIdForSub"
        :kode-kegiatan="activeKodeKegiatanForSub"
        :jumlah-sub="activeJumlahSubForSub"
    />

    <!-- MODAL: Konfirmasi Hapus -->
    <ConfirmDeleteDialog
        v-model:open="showConfirmDialog"
        :message="confirmMessage"
        @confirm="runConfirmedAction"
    />
</template>