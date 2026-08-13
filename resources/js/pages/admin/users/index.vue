<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import UserFormDialog from '@/components/UserFormDialog.vue'
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue'

defineOptions({
    layout: AppLayout
})

const props = defineProps({
    users: Array,
    roles: Array,
})

/* ====================== MODAL TAMBAH / EDIT ====================== */
const showFormDialog = ref(false)
const selectedUser = ref(null) // null = mode Tambah, object = mode Edit

function openCreate() {
    selectedUser.value = null
    showFormDialog.value = true
}

function openEdit(user) {
    selectedUser.value = user
    showFormDialog.value = true
}

/* ====================== MODAL KONFIRMASI HAPUS ====================== */
const showDeleteDialog = ref(false)
const userToDelete = ref(null)

function confirmDelete(user) {
    userToDelete.value = user
    showDeleteDialog.value = true
}

function destroyUser() {
    if (!userToDelete.value) return

    router.delete(`/admin/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false
            userToDelete.value = null
        },
    })
}

function roleLabel(user) {
    return user.roles?.[0]?.name ?? '-'
}
</script>

<template>
    <div class="space-y-6 p-4">

        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold tracking-tight">
                Kelola Akun
            </h1>

            <button
                @click="openCreate"
                class="bg-green-500 text-white px-4 py-2 rounded-lg shadow hover:opacity-90 transition"
            >
                + Tambah Akun
            </button>
        </div>

        <!-- Tabel Akun -->
        <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-muted text-foreground">
                        <th class="p-3 text-left font-medium">Nama</th>
                        <th class="p-3 text-left font-medium">Role</th>
                        <th class="p-3 text-left font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="users.length === 0">
                        <td colspan="3" class="p-6 text-center text-muted-foreground">
                            Belum ada akun yang dibuat.
                        </td>
                    </tr>
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="border-t border-border hover:bg-accent/50 transition"
                    >
                        <td class="p-3 text-foreground">
                            {{ user.name }}
                        </td>
                        <td class="p-3 text-foreground capitalize">
                            {{ roleLabel(user) }}
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <button
                                    @click="openEdit(user)"
                                    class="bg-blue-500 text-white px-3 py-1.5 rounded-lg shadow hover:opacity-90 hover:shadow-md transition text-xs"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(user)"
                                    class="bg-red-500 text-white px-3 py-1.5 rounded-lg shadow hover:opacity-90 hover:shadow-md transition text-xs"
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Dialog Tambah / Edit Akun -->
        <UserFormDialog
            v-model:open="showFormDialog"
            :user="selectedUser"
            :roles="roles"
        />

        <!-- Dialog Konfirmasi Hapus -->
        <ConfirmDeleteDialog
            v-model:open="showDeleteDialog"
            :title="`Hapus akun ${userToDelete?.name ?? ''}?`"
            message="Tindakan ini tidak dapat dibatalkan."
            @confirm="destroyUser"
        />

    </div>
</template>
