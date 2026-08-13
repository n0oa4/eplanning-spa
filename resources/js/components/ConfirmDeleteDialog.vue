<script setup>
import { TriangleAlert } from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    open: Boolean,
    title: { type: String, default: 'Konfirmasi Hapus' },
    message: { type: String, default: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.' },
})

const emit = defineEmits(['update:open', 'confirm'])

const close = () => emit('update:open', false)

const confirmDelete = () => {
    emit('confirm')
    close()
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-sm z-[70]" overlay-class="z-[70]" :show-close-button="false">
            <div class="flex flex-col items-center text-center gap-4 py-2">

                <!-- Icon peringatan -->
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-950">
                    <TriangleAlert class="h-7 w-7 text-blue-600 dark:text-blue-400" />
                </div>

                <!-- Judul & pesan -->
                <div class="space-y-1.5">
                    <DialogHeader>
                        <DialogTitle class="text-center">{{ title }}</DialogTitle>
                    </DialogHeader>
                    <p class="text-sm text-muted-foreground">
                        {{ message }}
                    </p>
                </div>

                <!-- Tombol -->
                <div class="flex w-full gap-3 pt-2">
                    <button
                        type="button"
                        @click="close"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-medium
                               bg-secondary text-secondary-foreground
                               hover:bg-secondary/80 shadow transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-medium
                               bg-red-600 text-white
                               hover:bg-red-700 shadow transition"
                    >
                        Hapus
                    </button>
                </div>

            </div>
        </DialogContent>
    </Dialog>
</template>
