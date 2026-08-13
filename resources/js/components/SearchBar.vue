<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, X, FolderKanban, ListChecks, FileText } from 'lucide-vue-next'

const query = ref('')
const results = ref([])
const isOpen = ref(false)
const isLoading = ref(false)
const containerRef = ref(null)
let debounceTimer = null

async function doSearch(q) {
    if (q.trim().length < 2) {
        results.value = []
        isOpen.value = false
        return
    }

    isLoading.value = true
    try {
        const res = await fetch(`/search?q=${encodeURIComponent(q)}`)
        const data = await res.json()
        results.value = data.results || []
        isOpen.value = true
    } catch (e) {
        results.value = []
    } finally {
        isLoading.value = false
    }
}

watch(query, (val) => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => doSearch(val), 350)
})

function clearSearch() {
    query.value = ''
    results.value = []
    isOpen.value = false
}

function targetPage(status) {
    return status === 'disetujui' ? '/arsip' : '/program'
}

function selectResult(item) {
    const page = targetPage(item.status)

    const params = new URLSearchParams()
    if (item.type === 'program') {
        params.set('highlight_program', item.program_id)
    } else if (item.type === 'kegiatan') {
        params.set('highlight_program', item.program_id)
        params.set('highlight_activity', item.activity_id)
    } else if (item.type === 'sub_kegiatan') {
        params.set('highlight_program', item.program_id)
        params.set('highlight_activity', item.activity_id)
        params.set('highlight_sub', item.sub_activity_id)
    }

    clearSearch()
    router.visit(`${page}?${params.toString()}`)
}

function typeLabel(type) {
    return { program: 'Program', kegiatan: 'Kegiatan', sub_kegiatan: 'Sub Kegiatan' }[type] || type
}

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
    <div ref="containerRef" class="relative w-full max-w-xs">
        <div class="relative">
            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
            <input
                v-model="query"
                @focus="results.length && (isOpen = true)"
                type="text"
                placeholder="Cari program, kegiatan, sub kegiatan..."
                class="w-full rounded-md border border-input bg-background pl-9 pr-8 py-1.5 text-sm
                       text-foreground placeholder:text-muted-foreground
                       focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring transition-colors"
            />
            <button
                v-if="query"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition"
            >
                <X class="h-4 w-4" />
            </button>
        </div>

        <!-- Dropdown hasil -->
        <div
            v-if="isOpen"
            class="absolute left-0 top-full mt-1.5 w-full min-w-[20rem] rounded-lg border border-border bg-popover shadow-lg z-50 max-h-80 overflow-y-auto"
        >
            <div v-if="isLoading" class="px-4 py-3 text-sm text-muted-foreground">
                Mencari...
            </div>

            <div v-else-if="results.length === 0" class="px-4 py-3 text-sm text-muted-foreground">
                Tidak ada hasil ditemukan
            </div>

            <button
                v-for="item in results"
                :key="`${item.type}-${item.id}`"
                @click="selectResult(item)"
                class="w-full flex items-start gap-3 px-3 py-2.5 text-left hover:bg-accent transition-colors border-b border-border last:border-b-0"
            >
                <component
                    :is="item.type === 'program' ? FolderKanban : item.type === 'kegiatan' ? ListChecks : FileText"
                    class="h-4 w-4 mt-0.5 shrink-0 text-blue-500"
                />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-popover-foreground truncate">{{ item.label }}</span>
                        <span class="text-[10px] uppercase tracking-wide text-muted-foreground shrink-0">{{ typeLabel(item.type) }}</span>
                    </div>
                    <div class="text-xs text-muted-foreground truncate">
                        <span v-if="item.type === 'sub_kegiatan'">{{ item.program_name }} &rsaquo; {{ item.activity_name }}</span>
                        <span v-else-if="item.type === 'kegiatan'">{{ item.program_name }}</span>
                        <span v-else>{{ item.sublabel }}</span>
                    </div>
                </div>
                <span
                    v-if="item.status === 'disetujui'"
                    class="text-[10px] px-1.5 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 shrink-0"
                >
                    Arsip
                </span>
            </button>
        </div>
    </div>
</template>
