<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({
    layout: AppLayout
})

const form = useForm({
    kode_program: '',
    nama_program: '',
    tahun: new Date().getFullYear(),
})

const submit = () => {
    form.post('/program')
}
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">Tambah Program</h1>

        <form @submit.prevent="submit">
            <div>
                <label>Kode Program</label>
                <input v-model="form.kode_program" type="text" class="border p-2 w-full"/>
            </div>

            <div>
                <label>Nama Program</label>
                <input v-model="form.nama_program" type="text" class="border p-2 w-full"/>
            </div>

            <div class="mb-3">
                <label>Tahun</label>
                <input v-model="form.tahun" type="number" class="border p-2 w-full"/>
            </div>

            <button class="bg-blue-500 text-white px-4 py-2">
                Simpan
            </button>
        </form>
    </div>
</template>
