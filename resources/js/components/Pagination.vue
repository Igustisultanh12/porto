<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
defineProps<{ current: number; last: number; previous: string | null; next: string | null }>();
</script>
<template>
    <nav v-if="last > 1" class="pagination" aria-label="Halaman hasil">
        <Link v-if="previous" :href="previous" class="button secondary">← Sebelumnya</Link
        ><span>Halaman {{ current }} dari {{ last }}</span
        ><Link v-if="next" :href="next" class="button secondary">Berikutnya →</Link>
    </nav>
</template>
