<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { Shared, Entry } from '../types';
import { entryLabels } from '../i18n';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
defineProps<{ entries: Entry[] }>();
const page = usePage<Shared>();
const avatar = computed(() => page.props.siteMedia.find((m) => m.slot === 'avatar'));
const cv = computed(() => page.props.siteMedia.find((m) => m.slot === 'cv'));
</script>
<template>
    <SiteLayout
        ><Meta title="Tentang" />
        <section class="page-intro">
            <p class="eyebrow">DI BALIK KARYA</p>
            <h1>{{ page.props.site.name || 'Sebuah perjalanan belajar.' }}</h1>
            <p>
                {{
                    page.props.site.headline ||
                    'Mengenal proses, minat, dan pengalaman yang membentuk setiap karya.'
                }}
            </p>
        </section>
        <section class="bento">
            <div class="panel about-tile">
                <img
                    v-if="avatar"
                    class="avatar"
                    :src="avatar.url"
                    :alt="page.props.site.name || 'Foto profil'"
                    width="160"
                    height="160"
                />
                <p class="preserve">
                    {{
                        page.props.site.bio || 'Profil lengkap akan ditambahkan oleh pemilik situs.'
                    }}
                </p>
                <p v-if="page.props.site.location" class="muted">
                    ⌖ {{ page.props.site.location }}
                </p>
                <a v-if="cv" :href="cv.url" class="button">Unduh CV ↓</a>
            </div>
            <div class="panel about-tile">
                <p class="eyebrow">RASA INGIN TAHU</p>
                <h2>Minat riset</h2>
                <p class="preserve">
                    {{
                        page.props.site.research_interests ||
                        'Topik dan bidang riset akan hadir di sini.'
                    }}
                </p>
            </div>
        </section>
        <section v-for="kind in ['experience', 'education', 'skill']" :key="kind" class="section">
            <h2>{{ entryLabels[kind] }}</h2>
            <div v-if="entries.some((e) => e.kind === kind)" class="timeline">
                <article
                    v-for="entry in entries.filter((e) => e.kind === kind)"
                    :key="entry.id"
                    class="panel"
                >
                    <p class="eyebrow">{{ entry.subtitle }}</p>
                    <h3>{{ entry.title }}</h3>
                    <p class="preserve">{{ entry.body }}</p>
                    <a v-if="entry.url" :href="entry.url" target="_blank" rel="noopener noreferrer"
                        >Selengkapnya ↗</a
                    >
                </article>
            </div>
            <p v-else class="muted">Belum ada informasi yang ditambahkan.</p>
        </section></SiteLayout
    >
</template>
