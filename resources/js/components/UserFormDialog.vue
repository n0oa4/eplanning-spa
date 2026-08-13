<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    open: Boolean,
    user: { type: Object, default: null }, // null = mode Tambah, object = mode Edit
    roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:open'])

const isEdit = computed(() => !!props.user)

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
    role: '',
})

// Isi ulang form setiap kali dialog dibuka — populate data user jika mode Edit,
// atau reset bersih jika mode Tambah
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        form.clearErrors()
        if (props.user) {
            form.name = props.user.name
            form.password = ''
            form.password_confirmation = ''
            form.role = props.user.roles?.[0]?.name ?? ''
        } else {
            form.reset()
            form.role = props.roles?.[0] ?? ''
        }
    }
})

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            emit('update:open', false)
        },
    }

    if (isEdit.value) {
        form.put(`/admin/users/${props.user.id}`, options)
    } else {
        form.post('/admin/users', options)
    }
}

const close = () => {
    form.reset()
    form.clearErrors()
    emit('update:open', false)
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Akun' : 'Tambah Akun' }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Nama Pengguna
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p v-if="form.errors.name" class="text-xs text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Password
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                    <p v-if="isEdit" class="text-xs text-muted-foreground">
                        Kosongkan jika tidak ingin mengubah password
                    </p>
                    <p v-if="form.errors.password" class="text-xs text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Konfirmasi Password
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground placeholder:text-muted-foreground
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">
                        Role
                    </label>
                    <select
                        v-model="form.role"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm
                               text-foreground capitalize
                               focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring
                               transition-colors"
                    >
                        <option v-for="r in roles" :key="r" :value="r" class="capitalize">
                            {{ r }}
                        </option>
                    </select>
                    <p v-if="form.errors.role" class="text-xs text-destructive">
                        {{ form.errors.role }}
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
