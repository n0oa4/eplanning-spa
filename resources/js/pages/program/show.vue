<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    program: Object
})

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

/* ====================== FORM KEGIATAN ====================== */
const activityForm = useForm({
    kode_kegiatan: '',
    nama_kegiatan: ''
})

const submitActivity = () => {
    activityForm.post(`/program/${props.program.id}/activity`, {
        preserveScroll: true,
        onSuccess: () => activityForm.reset()
    })
}

const editingActivityId = ref(null)

const activityEditForm = useForm({
    kode_kegiatan: '',
    nama_kegiatan: ''
})

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
        }
    })
}

const deleteActivity = (id) => {
    if (confirm('Yakin ingin menghapus kegiatan ini?')) {
        useForm().delete(`/activity/${id}`, { preserveScroll: true })
    }
}

/* ====================== FORM SUB KEGIATAN ====================== */
const subForm = useForm({
    kode_sub_kegiatan: '',
    nama_sub_kegiatan: '',
    pagu_anggaran: '',
    indikator: '',
    target: '',
    prioritas_provinsi: '',
    prioritas_kabupaten: '',
    bidang_urusan: '',
    n1: '',
    n2: ''
})

const submitSub = (activityId) => {
    subForm.post(`/activity/${activityId}/sub`, {
        preserveScroll: true,
        onSuccess: () => subForm.reset()
    })
}

const editingId = ref(null)

const editForm = useForm({
    kode_sub_kegiatan: '',
    nama_sub_kegiatan: '',
    pagu_anggaran: '',
    indikator: '',
    target: '',
    prioritas_provinsi: '',
    prioritas_kabupaten: '',
    bidang_urusan: '',
    n1: '',
    n2: ''
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
        }
    })
}

const deleteSub = (id) => {
    if (confirm('Yakin ingin menghapus sub kegiatan ini?')) {
        useForm().delete(`/sub/${id}`, { preserveScroll: true })
    }
}

// Auto hitung N+1 dan N+2
watch(() => subForm.pagu_anggaran, (value) => {
    const pagu = parseFloat(value) || 0
    subForm.n1 = Math.round(pagu * 1.1)
    subForm.n2 = Math.round(pagu * 1.21)
})

watch(() => editForm.pagu_anggaran, (value) => {
    const pagu = parseFloat(value) || 0
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

    <!-- Program Header & Edit -->
    <div v-if="!editingProgram">
        <h1 class="text-2xl font-bold mb-2">{{ program.nama_program }}</h1>
        <p class="mb-4">Tahun: {{ program.tahun }}</p>

        <div class="bg-green-700 p-4 text-white mb-6 rounded">
            <strong>Total Pagu Program:</strong>
            Rp. {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
        </div>

        <div class="space-x-3 mb-6" v-if="program.status === 'draft'">
            <button @click="editingProgram = true" class="bg-yellow-500 px-4 py-2 text-black font-medium">
                Edit Program
            </button>
            <button
                v-if="program.activities?.length === 0"
                @click="deleteProgram"
                class="bg-red-600 px-4 py-2 text-white font-medium">
                Hapus Program
            </button>
        </div>
    </div>

    <!-- Form Edit Program -->
    <div v-else class="mb-6 p-4 border rounded space-y-3 bg-gray-800">
        <input v-model="programForm.kode_program" class="border p-3 w-full rounded" placeholder="Kode Program" />
        <input v-model="programForm.nama_program" class="border p-3 w-full rounded" placeholder="Nama Program" />
        <input v-model="programForm.tahun" type="number" class="border p-3 w-full rounded" />

        <div class="space-x-3">
            <button @click="updateProgram" class="bg-green-600 text-white px-5 py-2 rounded">Simpan Perubahan</button>
            <button @click="editingProgram = false" class="bg-gray-500 px-5 py-2 rounded">Batal</button>
        </div>
    </div>

    <!-- Tambah Kegiatan -->
    <h2 v-if="program.status === 'draft'" class="text-xl font-semibold mb-3">Tambah Kegiatan</h2>
    <form v-if="program.status === 'draft'" @submit.prevent="submitActivity" class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input v-model="activityForm.kode_kegiatan" placeholder="Kode Kegiatan" class="border p-3 rounded" />
        <input v-model="activityForm.nama_kegiatan" placeholder="Nama Kegiatan" class="border p-3 rounded" />
        <button type="submit" class="bg-blue-600 text-white font-medium rounded">Tambah Kegiatan</button>
    </form>

    <!-- Daftar Kegiatan -->
    <h2 class="text-xl font-semibold mb-4">Daftar Kegiatan</h2>

    <div v-if="program.activities?.length === 0" class="text-gray-400 italic">
        Belum ada kegiatan untuk program ini.
    </div>

    <div v-for="activity in program.activities" :key="activity.id" class="border border-gray-700 p-5 mb-8 rounded bg-gray-900">

        <!-- Kegiatan View & Edit -->
        <div v-if="editingActivityId !== activity.id">
            <h3 class="font-semibold text-lg">{{ activity.nama_kegiatan }}</h3>
            <p class="text-green-400 text-sm mb-4">
                Total Pagu: Rp {{ activity.total_pagu?.toLocaleString('id-ID') || 0 }}
            </p>

            <div class="space-x-2 mb-5" v-if="program.status === 'draft'">
                <button @click="startEditActivity(activity)" class="bg-yellow-500 px-4 py-1 text-black">Edit</button>
                <button @click="deleteActivity(activity.id)" class="bg-red-600 px-4 py-1 text-white">Hapus</button>
            </div>
        </div>

        <!-- Edit Mode Kegiatan -->
        <div v-else class="mb-6 space-y-3">
            <input v-model="activityEditForm.kode_kegiatan" class="border p-3 w-full rounded" />
            <input v-model="activityEditForm.nama_kegiatan" class="border p-3 w-full rounded" />
            <div class="space-x-3">
                <button @click="updateActivity" class="bg-green-600 text-white px-5 py-2">Simpan</button>
                <button @click="editingActivityId = null" class="bg-gray-500 px-5 py-2">Batal</button>
            </div>
        </div>

        <!-- Form Tambah Sub Kegiatan -->
        <form v-if="program.status === 'draft'"
              @submit.prevent="submitSub(activity.id)"
              class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6 p-4 bg-gray-800 rounded">
            <input v-model="subForm.kode_sub_kegiatan" placeholder="Kode Sub Kegiatan" class="border p-3 rounded" />
            <input v-model="subForm.nama_sub_kegiatan" placeholder="Nama Sub Kegiatan" class="border p-3 rounded" />
            <input v-model="subForm.pagu_anggaran" type="number" placeholder="Pagu Anggaran" class="border p-3 rounded" />
            <input v-model="subForm.indikator" placeholder="Indikator" class="border p-3 rounded" />
            <input v-model="subForm.target" placeholder="Target" class="border p-3 rounded" />
            <input v-model="subForm.prioritas_provinsi" placeholder="Prioritas Provinsi" class="border p-3 rounded" />
            <input v-model="subForm.prioritas_kabupaten" placeholder="Prioritas Kabupaten" class="border p-3 rounded" />
            <input v-model="subForm.bidang_urusan" placeholder="Bidang Urusan" class="border p-3 rounded" />
            <input v-model="subForm.n1" type="number" readonly class="border p-3 rounded bg-gray-700" />
            <input v-model="subForm.n2" type="number" readonly class="border p-3 rounded bg-gray-700" />

            <button type="submit" class="bg-green-600 text-white py-3 rounded font-medium md:col-span-2">
                Tambah Sub Kegiatan
            </button>
        </form>

        <!-- Daftar Sub Kegiatan -->
        <div v-if="activity.subActivities?.length" class="mt-4">
            <div v-for="sub in activity.subActivities" :key="sub.id" class="border p-4 mb-4 bg-gray-800 rounded">
                <!-- View Mode -->
                <div v-if="editingId !== sub.id">
                    <div><strong>{{ sub.nama_sub_kegiatan }}</strong></div>
                    <div>Pagu: Rp {{ parseFloat(sub.pagu_anggaran).toLocaleString('id-ID') }}</div>
                    <div>Indikator: {{ sub.indikator }}</div>
                    <div>Target: {{ sub.target }}</div>

                    <div class="mt-3 space-x-2" v-if="program.status === 'draft'">
                        <button @click="startEdit(sub)" class="bg-yellow-500 px-4 py-1 text-black">Edit</button>
                        <button @click="deleteSub(sub.id)" class="bg-red-600 px-4 py-1 text-white">Hapus</button>
                    </div>
                </div>

                <!-- Edit Mode -->
                <div v-else class="space-y-3">
                    <input v-model="editForm.kode_sub_kegiatan" class="border p-3 w-full rounded" />
                    <input v-model="editForm.nama_sub_kegiatan" class="border p-3 w-full rounded" />
                    <input v-model="editForm.pagu_anggaran" type="number" class="border p-3 w-full rounded" />
                    <input v-model="editForm.indikator" class="border p-3 w-full rounded" />
                    <input v-model="editForm.target" class="border p-3 w-full rounded" />

                    <div class="space-x-3">
                        <button @click="updateSub" class="bg-green-600 text-white px-5 py-2">Simpan</button>
                        <button @click="editingId = null" class="bg-gray-500 px-5 py-2">Batal</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-gray-400 italic pl-4">
            Belum ada sub kegiatan untuk kegiatan ini.
        </div>
    </div>
</template>
