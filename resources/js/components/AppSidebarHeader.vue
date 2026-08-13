<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import DarkModeToggle from '@/components/DarkModeToggle.vue';
import SearchBar from '@/components/SearchBar.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();

// Search hanya tampil di halaman Program dan Arsip
const showSearch = computed(() => {
    const url = page.url || '';
    return url.startsWith('/program') || url.startsWith('/arsip');
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <!-- Kiri: trigger sidebar + breadcrumb + search -->
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <SidebarTrigger class="-ml-1 shrink-0" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <SearchBar v-if="showSearch" />
        </div>

        <!-- Kanan: tombol dark mode -->
        <DarkModeToggle />
    </header>
</template>