<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/logo-kabupaten.png"
        alt="Logo Kabupaten Banggai"
        :class="className"
        v-bind="$attrs"
    />
</template>