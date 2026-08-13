<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggle() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <button
        @click="toggle"
        :title="resolvedAppearance === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
        aria-label="Toggle dark mode"
        class="inline-flex items-center justify-center w-8 h-8 rounded-md
               text-muted-foreground hover:text-foreground
               hover:bg-accent transition-colors duration-200"
    >
        <Sun v-if="resolvedAppearance === 'dark'" class="w-4 h-4" />
        <Moon v-else class="w-4 h-4" />
    </button>
</template>