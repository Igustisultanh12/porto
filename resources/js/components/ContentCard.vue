<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Content } from '../types';
import { labels } from '../i18n';
const props = defineProps<{ item: Content }>();
const cover = computed(() => props.item.media?.find((m) => m.slot === 'cover'));
</script>
<template>
    <article class="content-card panel">
        <Link :href="item.url" class="card-art" :class="item.type" tabindex="-1" aria-hidden="true"
            ><img v-if="cover" :src="cover.url" alt="" width="720" height="440" loading="lazy" />
            <div v-else class="abstract-art">
                <span>{{
                    item.type === 'research' ? '◎' : item.type === 'article' ? '✳' : '⌘'
                }}</span
                ><i></i><i></i></div
        ></Link>
        <div class="card-body">
            <div class="eyebrow">
                {{ labels[item.type] }}<span>{{ item.year || '' }}</span>
            </div>
            <h3>
                <Link :href="item.url">{{ item.title }} <span aria-hidden="true">↗</span></Link>
            </h3>
            <p>{{ item.summary }}</p>
            <div class="tags">
                <span v-for="tag in item.taxonomies?.slice(0, 3)" :key="tag.id">{{
                    tag.name
                }}</span>
            </div>
        </div>
    </article>
</template>
