<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import ActivityFormDialog from '@/components/ActivityFormDialog.vue'
import SubActivityFormDialog from '@/components/SubActivityFormDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ program: Object })

const editingProgram = ref(false)

const programForm = useForm({
    kode_program: props.program.kode_program,
    nama_program: props.program.nama_program,
    tahun: props.program.tahun
})

const updateProgram = () => {
    programForm.put(`/program/${props.program.id}`, {
        preserveScroll: true,
        onSuccess: () => editingProgram.value = false
    })
}

const deleteProgram = () => {
    if (confirm('Yakin ingin menghapus program ini?')) {
        useForm().delete(`/program/${props.program.id}`)
    }
}

/* ====================== KEGIATAN ====================== */

// Modal Tambah Kegiatan
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
    if (confirm('Yakin ingin menghapus kegiatan ini?')) {
        useForm().delete(`/activity/${id}`, {
            preserveScroll: true,
            onSuccess: () => router.reload({ preserveScroll: true })
        })
    }
}

/* ====================== SUB KEGIATAN ====================== */

// Modal Tambah Sub Kegiatan — simpan activity id mana yang sedang dibuka
const showSubDialog = ref(false)
const activeActivityIdForSub = ref(null)

const openSubDialog = (activityId) => {
    activeActivityIdForSub.value = activityId
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
    if (confirm('Yakin ingin menghapus sub kegiatan ini?')) {
        useForm().delete(`/sub/${id}`, {
            preserveScroll: true,
            onSuccess: () => router.reload({ preserveScroll: true })
        })
    }
}

// Auto hitung N+1 dan N+2 (untuk form Edit Sub yang masih inline)
watch(() => editForm.pagu_anggaran, (value) => {
    let pagu = parseFloat(value) || 0
    editForm.n1 = Math.round(pagu * 1.1)
    editForm.n2 = Math.round(pagu * 1.21)
})
</script>

<template>
    <div class="mb-4">
        <Link href="/program" class="bg-gray-600 text-white px-4 py-2">
            ← Kembali ke Daftar Program
        </Link>
    </div>

    <!-- MODE VIEW PROGRAM -->
    <div v-if="!editingProgram">
        <h1 class="text-2xl font-bold mb-2">{{ program.nama_program }}</h1>
        <div class="space-x-2 mb-4">
            <button
                v-if="['draft', 'ditolak'].includes(program.status)"
                @click="editingProgram = true"
                class="bg-yellow-500 px-3 py-1 text-black"
            >
                Edit Program
            </button>
            <button
                v-if="['draft', 'ditolak'].includes(program.status) && program.activities.length === 0"
                @click="deleteProgram"
                class="bg-red-600 px-3 py-1 text-white"
            >
                Hapus Program
            </button>
        </div>
    </div>

    <!-- MODE EDIT PROGRAM -->
    <div v-else class="mb-4 space-y-2">
        <input v-model="programForm.kode_program" class="border p-2 w-full" />
        <input v-model="programForm.nama_program" class="border p-2 w-full" />
        <input v-model="programForm.tahun" type="number" class="border p-2 w-full" />
        <div class="space-x-2">
            <button @click="updateProgram" class="bg-green-500 px-3 py-1">Simpan</button>
            <button @click="editingProgram = false" class="bg-gray-500 px-3 py-1">Batal</button>
        </div>
    </div>

    <p class="mb-4">Tahun: {{ program.tahun }}</p>

    <div class="bg-green-700 p-4 text-white mb-6 rounded">
        <strong>Total Pagu Program:</strong>
        Rp. {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
    </div>

    <!-- DAFTAR KEGIATAN -->
    <div class="flex justify-between items-center mb-2">
        <h2 class="text-xl font-semibold">Daftar Kegiatan</h2>

        <button
            v-if="['draft', 'ditolak'].includes(program.status)"
            @click="showActivityDialog = true"
            class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition"
        >
            + Tambah Kegiatan
        </button>
    </div>

    <div v-if="program.activities.length === 0" class="text-gray-400">
        Belum ada kegiatan
    </div>

    <div v-for="activity in program.activities" :key="activity.id" class="border p-4 mb-6">
        <!-- View Mode Kegiatan -->
        <div v-if="editingActivityId !== activity.id">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold">{{ activity.nama_kegiatan }}</h3>

                <button
                    v-if="['draft', 'ditolak'].includes(program.status)"
                    @click="openSubDialog(activity.id)"
                    class="bg-green-500 text-white px-3 py-1 rounded-lg shadow hover:opacity-90 transition text-sm"
                >
                    + Tambah Sub
                </button>
            </div>
            <div class="text-sm text-green-400 mb-2">
                Total Pagu: Rp {{ activity.total_pagu?.toLocaleString('id-ID') || 0 }}
            </div>
            <div class="space-x-2 mb-4">
                <button v-if="['draft', 'ditolak'].includes(program.status)" @click="startEditActivity(activity)" class="bg-yellow-500 px-3 py-1 text-black">Edit</button>
                <button v-if="['draft', 'ditolak'].includes(program.status)" @click="deleteActivity(activity.id)" class="bg-red-600 px-3 py-1 text-white">Hapus</button>
            </div>
        </div>

        <!-- Edit Mode Kegiatan -->
        <div v-else class="mb-4 space-y-2">
            <input v-model="activityEditForm.kode_kegiatan" class="border p-2 w-full" />
            <input v-model="activityEditForm.nama_kegiatan" class="border p-2 w-full" />
            <div class="space-x-2">
                <button @click="updateActivity" class="bg-green-500 px-3 py-1">Simpan</button>
                <button @click="editingActivityId = null" class="bg-gray-500 px-3 py-1">Batal</button>
            </div>
        </div>

        <!-- DAFTAR SUB KEGIATAN -->
        <div v-if="activity.sub_activities && activity.sub_activities.length">
            <div v-for="sub in activity.sub_activities" :key="sub.id" class="border p-3 mb-3 bg-gray-800">
                <!-- View Mode Sub -->
                <div v-if="editingId !== sub.id">
                    <div><strong>Sub:</strong> {{ sub.nama_sub_kegiatan }}</div>
                    <div><strong>Pagu:</strong> {{ sub.pagu_anggaran }}</div>
                    <div><strong>Indikator:</strong> {{ sub.indikator }}</div>
                    <div><strong>Target:</strong> {{ sub.target }}</div>

                    <div class="mt-2 space-x-2">
                        <button v-if="['draft', 'ditolak'].includes(program.status)" @click="startEdit(sub)" class="bg-yellow-500 px-3 py-1 text-black">Edit</button>
                        <button v-if="['draft', 'ditolak'].includes(program.status)" @click="deleteSub(sub.id)" class="bg-red-600 px-3 py-1 text-white">Hapus</button>
                    </div>
                </div>

                <!-- Edit Mode Sub -->
                <div v-else class="space-y-2">
                    <input v-model="editForm.kode_sub_kegiatan" class="border p-2 w-full" placeholder="Kode Sub Kegiatan" />
                    <input v-model="editForm.nama_sub_kegiatan" class="border p-2 w-full" placeholder="Nama Sub Kegiatan" />
                    <input v-model="editForm.pagu_anggaran" type="number" class="border p-2 w-full" placeholder="Pagu Anggaran" />
                    <input v-model="editForm.indikator" class="border p-2 w-full" placeholder="Indikator" />
                    <input v-model="editForm.target" class="border p-2 w-full" placeholder="Target" />
                    <input v-model="editForm.prioritas_provinsi" class="border p-2 w-full" placeholder="Prioritas Provinsi" />
                    <input v-model="editForm.prioritas_kabupaten" class="border p-2 w-full" placeholder="Prioritas Kabupaten" />
                    <input v-model="editForm.bidang_urusan" class="border p-2 w-full" placeholder="Bidang Urusan" />
                    <input v-model="editForm.n1" type="number" class="border p-2 w-full bg-gray-100" placeholder="N+1" readonly />
                    <input v-model="editForm.n2" type="number" class="border p-2 w-full bg-gray-100" placeholder="N+2" readonly />
                    <div class="space-x-2">
                        <button @click="updateSub" class="bg-green-500 px-3 py-1">Simpan</button>
                        <button @click="editingId = null" class="bg-gray-500 px-3 py-1">Batal</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-400">
            Belum ada sub kegiatan
        </div>
    </div>

    <!-- MODAL: Tambah Kegiatan -->
    <ActivityFormDialog
        v-model:open="showActivityDialog"
        :program-id="program.id"
    />

    <!-- MODAL: Tambah Sub Kegiatan -->
    <SubActivityFormDialog
        v-model:open="showSubDialog"
        :activity-id="activeActivityIdForSub"
    />
</template>