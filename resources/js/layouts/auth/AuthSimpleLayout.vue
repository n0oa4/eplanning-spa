<script setup>
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import DarkModeToggle from '@/components/DarkModeToggle.vue';

defineProps({
    title: String,
    description: String,
});
</script>

<template>
    <div class="relative flex min-h-svh bg-background">

        <!-- Tombol dark mode pojok kanan atas -->
        <div class="absolute top-4 right-4 z-20">
            <DarkModeToggle />
        </div>

        <!-- Kiri — Dekorasi -->
        <div
            class="relative hidden md:flex md:w-1/2 flex-col px-12 py-16 gap-8 overflow-hidden"
            style="background: linear-gradient(170deg, #0f1f4d 0%, #1d4ed8 45%, #0ea5b8 100%);"
        >
            <div class="flex flex-col items-center gap-7 text-center max-w-sm mx-auto my-auto">

                <!-- Logo -->
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                    <AppLogoIcon class="size-10 object-contain" />
                </div>

                <!-- Teks -->
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold text-white tracking-tight">
                        E-Planning
                    </h1>
                    <p class="text-sm text-blue-50/90 leading-relaxed max-w-xs mx-auto">
                        Platform pencatatan dan pengarsipan rencana anggaran kegiatan secara terpusat, terstruktur, dan mudah diakses.
                    </p>
                </div>

                <!-- Kartu Fitur -->
                <div class="w-full grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15 p-4 text-center">
                        <div class="text-sm font-semibold text-white">Program</div>
                        <div class="text-xs text-blue-50/80 mt-1">Terstruktur</div>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15 p-4 text-center">
                        <div class="text-sm font-semibold text-white">Kegiatan</div>
                        <div class="text-xs text-blue-50/80 mt-1">Terdokumentasi</div>
                    </div>
                    <div class="rounded-xl bg-white/10 backdrop-blur-sm ring-1 ring-white/15 p-4 text-center">
                        <div class="text-sm font-semibold text-white">Arsip</div>
                        <div class="text-xs text-blue-50/80 mt-1">Mudah diakses</div>
                    </div>
                </div>

            </div>

            <!-- Card melayang di bawah, dengan jarak dari tepi -->
            <div class="relative mx-2 mb-2 mt-auto">
                <div class="bg-card rounded-2xl shadow-2xl px-6 py-5 flex items-center gap-4 ring-1 ring-black/5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-muted ring-1 ring-border">
                        <AppLogoIcon class="size-7 object-contain" />
                    </div>
                    <div class="text-left">
                        <div class="text-sm font-semibold text-card-foreground">Kabupaten Banggai</div>
                        <div class="text-xs text-muted-foreground">Sistem pencatatan anggaran resmi pemerintah daerah</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan — Form Login -->
        <div class="flex w-full flex-col items-center justify-center px-6 md:w-1/2 md:px-16 bg-background">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">

                    <!-- Judul -->
                    <div class="flex flex-col gap-1">
                        <h2 class="text-3xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                            Selamat datang
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>

                    <!-- Slot Form -->
                    <slot />

                </div>
            </div>
        </div>

    </div>
</template>
