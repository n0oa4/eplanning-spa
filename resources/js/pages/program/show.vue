<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { watch } from 'vue'

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

/* FORM KEGIATAN */
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
        useForm().delete(`/activity/${id}`, {
            preserveScroll: true
        })
    }
}

/* FORM SUB KEGIATAN */
const subForm = useForm({
    kode_sub_kegiatan: '',
    nama_sub_kegiatan: '',
    pagu_anggaran: '',
    sumber_dana: '',
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
    sumber_dana: '',
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
    editForm.sumber_dana = sub.sumber_dana
    editForm.indikator = sub.indikator
    editForm.target = sub.target
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
    if (confirm('Yakin ingin menghapus?')) {
        useForm().delete(`/sub/${id}`, {
            preserveScroll: true
        })
    }
}

watch(() => subForm.pagu_anggaran, (value) => {
    let pagu = parseFloat(value) || 0

    let n1 = pagu * 1.1
    let n2 = n1 * 1.1

    subForm.n1 = Math.round(n1)
    subForm.n2 = Math.round(n2)
})

watch(() => editForm.pagu_anggaran, (value) => {
    let pagu = parseFloat(value) || 0

    let n1 = pagu * 1.1
    let n2 = n1 * 1.1

    editForm.n1 = Math.round(n1)
    editForm.n2 = Math.round(n2)
})

</script>

<template>
    <div class="mb-4">
    <Link
        href="/program"
        class="bg-gray-600 text-white px-4 py-2"
    >
        ← Kembali ke Daftar Program
    </Link>
</div>
<div>
    <!-- MODE VIEW -->
<div v-if="!editingProgram">

    <h1 class="text-2xl font-bold mb-2">
        {{ program.nama_program }}
    </h1>

    <div class="space-x-2 mb-4">
        <button
            v-if="program.status === 'draft'"
            @click="editingProgram = true"
            class="bg-yellow-500 px-3 py-1 text-black"
        >
            Edit Program
        </button>

        <button
            v-if="program.status === 'draft' && program.activities.length === 0"
            @click="deleteProgram"
            class="bg-red-600 px-3 py-1 text-white"
        >
            Hapus Program
        </button>
    </div>

</div>

<!-- MODE EDIT -->
<div v-else class="mb-4 space-y-2">
    <input
        v-model="programForm.kode_program"
        class="border p-2 w-full"
    />

    <input
        v-model="programForm.nama_program"
        class="border p-2 w-full"
    />

    <input
        v-model="programForm.tahun"
        type="number"
        class="border p-2 w-full"
    />

    <div class="space-x-2">
        <button
            @click="updateProgram"
            class="bg-green-500 px-3 py-1"
        >
            Simpan
        </button>

        <button
            @click="editingProgram = false"
            class="bg-gray-500 px-3 py-1"
        >
            Batal
        </button>
    </div>

</div>



    <p class="mb-4">Tahun: {{ program.tahun }}</p>

    <div class="bg-green-700 p-3 text-white mb-4">
        <strong>Total Pagu Program:</strong>
        Rp. {{ program.total_pagu?.toLocaleString('id-ID') || 0 }}
    </div>

    <!-- ============================= -->
    <!-- TAMBAH KEGIATAN -->
    <!-- ============================= -->
    <h2
    v-if="program.status === 'draft'"
    class="text-xl font-semibold mb-2">Tambah Kegiatan</h2>

    <form
    v-if="program.status === 'draft'"
    @submit.prevent="submitActivity" class="mb-6">
        <input
            v-model="activityForm.kode_kegiatan"
            type="text"
            placeholder="Kode Kegiatan (contoh: 27.01.2.01)"
            class="border p-2 mr-2"
        />

        <input
            v-model="activityForm.nama_kegiatan"
            type="text"
            placeholder="Nama Kegiatan"
            class="border p-2 mr-2"
        />
        <button
            v-if="program.status === 'draft'"
            class="bg-blue-500 text-white px-4 py-2">
            Tambah
        </button>
    </form>

    <!-- ============================= -->
    <!-- DAFTAR KEGIATAN -->
    <!-- ============================= -->
    <h2 class="text-xl font-semibold mb-2">Daftar Kegiatan</h2>

    <div v-if="program.activities.length === 0">
        Belum ada kegiatan
    </div>

    <div
    v-for="activity in program.activities"
    :key="activity.id"
    class="border p-4 mb-6"
>

    <!-- MODE VIEW -->
    <div v-if="editingActivityId !== activity.id">

        <h3 class="font-semibold mb-2">
            {{ activity.nama_kegiatan }}
        </h3>

        <div class="text-sm text-green-400 mb-2">
            Total Pagu:
            Rp {{ activity.total_pagu?.toLocaleString('id-ID') || 0 }}
        </div>


        <div class="space-x-2 mb-4">
            <button
                v-if="program.status === 'draft'"
                @click="startEditActivity(activity)"
                class="bg-yellow-500 px-3 py-1 text-black"
            >
                Edit
            </button>

            <button
                v-if="program.status === 'draft'"
                @click="deleteActivity(activity.id)"
                class="bg-red-600 px-3 py-1 text-white"
            >
                Hapus
            </button>
        </div>

    </div>

    <!-- MODE EDIT -->
    <div v-else class="mb-4 space-y-2">
        <input
            v-model="activityEditForm.kode_kegiatan"
            class="border p-2 w-full"
        />

        <input
            v-model="activityEditForm.nama_kegiatan"
            class="border p-2 w-full"
        />

        <div class="space-x-2">
            <button
                @click="updateActivity"
                class="bg-green-500 px-3 py-1"
            >
                Simpan
            </button>

            <button
                @click="editingActivityId = null"
                class="bg-gray-500 px-3 py-1"
            >
                Batal
            </button>
        </div>
    </div>

    <!-- FORM SUB & DAFTAR SUB TETAP DI SINI -->

        <!-- ============================= -->
        <!-- FORM SUB KEGIATAN -->
        <!-- ============================= -->
        <form
            v-if="program.status === 'draft'"
            @submit.prevent="submitSub(activity.id)"
            class="space-y-2 mb-4"
        >
            <input v-model="subForm.kode_sub_kegiatan"
                type="text"
                placeholder="Kode Sub Kegiatan"
                class="border p-2 w-full"
            />

            <input v-model="subForm.nama_sub_kegiatan"
                type="text"
                placeholder="Nama Sub Kegiatan"
                class="border p-2 w-full"
            />

            <input v-model="subForm.pagu_anggaran"
                type="number"
                placeholder="Pagu Anggaran"
                class="border p-2 w-full"
            />

            <input v-model="subForm.indikator"
                type="text"
                placeholder="Indikator"
                class="border p-2 w-full"
            />

            <input v-model="subForm.target"
                type="text"
                placeholder="Target"
                class="border p-2 w-full"
            />

            <input v-model="subForm.prioritas_provinsi"
            type="text"
            placeholder="Prioritas Provinsi" class="border p-2 w-full"
            />

            <input v-model="subForm.prioritas_kabupaten"
            type="text"
            placeholder="Prioritas Kabupaten" class="border p-2 w-full"
            />

            <input v-model="subForm.bidang_urusan"
            type="text"
            placeholder="Bidang Urusan" class="border p-2 w-full"
            />

            <input v-model="subForm.n1" type="number"
            placeholder="N-1" readonly class="bg-gray-25"
            />

            <input v-model="subForm.n2" type="number"
            placeholder="N-2" readonly class="bg-gray-25"
            />

            <button
                v-if="program.status === 'draft'"
                class="bg-green-500 text-white px-4 py-2">
                Tambah Sub
            </button>
        </form>

        <!-- ============================= -->
        <!-- DAFTAR SUB KEGIATAN -->
        <!-- ============================= -->

        <div v-if="activity.sub_activities && activity.sub_activities.length">

            <div
            v-for="sub in activity.sub_activities"
            :key="sub.id"
            class="border p-3 mb-3 bg-gray-800"
        >

            <!-- MODE VIEW -->
            <div v-if="editingId !== sub.id">
                <div><strong>Sub:</strong> {{ sub.nama_sub_kegiatan }}</div>
                <div><strong>Pagu:</strong> {{ sub.pagu_anggaran }}</div>
                <div><strong>Indikator:</strong> {{ sub.indikator }}</div>
                <div><strong>Target:</strong> {{ sub.target }}</div>

                <div class="mt-2 space-x-2">
                    <button
                        v-if="program.status === 'draft'"
                        @click="startEdit(sub)"
                        class="bg-yellow-500 px-3 py-1 text-black"
                    >
                        Edit
                    </button>

                    <button
                        v-if="program.status === 'draft'"
                        @click="deleteSub(sub.id)"
                        class="bg-red-600 px-3 py-1 text-white"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <!-- MODE EDIT -->
            <div v-else class="space-y-2">
                <input v-model="editForm.kode_sub_kegiatan" class="border p-2 w-full" />
                <input v-model="editForm.nama_sub_kegiatan" class="border p-2 w-full" />
                <input v-model="editForm.pagu_anggaran" type="number" class="border p-2 w-full" />
                <input v-model="editForm.indikator" class="border p-2 w-full" />
                <input v-model="editForm.target" class="border p-2 w-full" />

                <div class="space-x-2">
                    <button
                        @click="updateSub"
                        class="bg-green-500 px-3 py-1"
                    >
                        Simpan
                    </button>

                    <button
                        @click="editingId = null"
                        class="bg-gray-500 px-3 py-1"
                    >
                        Batal
                    </button>
                </div>
            </div>

        </div>

</div>

<div v-else class="text-gray-400">
    Belum ada sub kegiatan
</div>
    </div>
</div>
</template>
