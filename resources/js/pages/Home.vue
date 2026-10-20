<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
import ContentCard from '../components/ContentCard.vue';
import type { Shared, Content, Entry } from '../types';
import { labels } from '../i18n';
defineProps<{ featured: Content[]; entries: Entry[]; counts: Record<string, number> }>();
const page = usePage<Shared>();
const cv = computed(() => page.props.siteMedia.find((m) => m.slot === 'cv'));
</script>
<template>
    <SiteLayout
        ><Meta />
        <section class="hero">
            <div class="hero-copy">
                <p class="pill"><span class="dot"></span> RUANG KARYA & EKSPLORASI</p>
                <h1>
                    {{ page.props.site.headline || 'Dari rasa ingin tahu, menjadi'
                    }}<span v-if="!page.props.site.headline" class="gradient-text"
                        >karya bermakna.</span
                    >
                </h1>
                <p class="hero-description">
                    {{
                        page.props.site.bio ||
                        'Sebuah ruang untuk mendokumentasikan proyek, berbagi riset, dan merangkai ide yang terus berkembang.'
                    }}
                </p>
                <div class="actions">
                    <Link class="button" href="/karya/project">Jelajahi karya <span>↗</span></Link
                    ><Link class="button secondary" href="/karya/research"
                        >Lihat riset <span>→</span></Link
                    ><a v-if="cv" :href="cv.url" class="text-link">Unduh CV ↓</a>
                </div>
                <div v-if="Object.keys(counts).length" class="stats">
                    <div v-for="(count, key) in counts" :key="key">
                        <strong>{{ count }}</strong
                        ><span>{{ labels[key as keyof typeof labels] }}</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <div class="orbital">
                    <div class="orbit one"></div>
                    <div class="orbit two"></div>
                    <div class="core">✳</div>
                    <div class="float-chip chip-code">
                        &lt;/&gt;<small>build with purpose</small>
                    </div>
                    <div class="float-chip chip-research">◎<small>stay curious</small></div>
                    <span class="star star-one">✦</span><span class="star star-two">✧</span>
                </div>
                <div class="visual-caption">
                    <span>01 / EKSPERIMEN TANPA BATAS</span><span>↗</span>
                </div>
            </div>
        </section>
        <section class="intro-strip">
            <span>Berpikir terbuka.</span><span class="muted">Membangun dengan teliti.</span
            ><span>Terus belajar. <b>✳</b></span>
        </section>
        <section class="section">
            <div class="section-head">
                <div>
                    <p class="eyebrow">PILIHAN TERKURASI</p>
                    <h2>Karya & eksplorasi terbaru<span class="accent">.</span></h2>
                </div>
                <Link href="/karya/project" class="text-link">Semua proyek ↗</Link>
            </div>
            <div v-if="featured.length" class="card-grid">
                <ContentCard v-for="item in featured" :key="item.id" :item="item" />
            </div>
            <div v-else class="empty panel">
                <span>✧</span>
                <h3>Ruang untuk karya berikutnya.</h3>
                <p>Proyek dan riset unggulan akan hadir di sini setelah diterbitkan.</p>
                <Link href="/karya/research" class="text-link">Jelajahi ruang riset →</Link>
            </div>
        </section>
        <section class="bento section">
            <div class="panel about-tile">
                <p class="eyebrow">DI BALIK KARYA</p>
                <h2>Proses sama berharganya<br />dengan hasil.</h2>
                <p>
                    {{
                        page.props.site.research_interests ||
                        'Temukan cerita, bidang yang ditekuni, dan perjalanan belajar di balik setiap karya.'
                    }}
                </p>
                <Link href="/tentang" class="text-link">Kenali lebih dekat ↗</Link>
            </div>
            <div class="panel skill-tile">
                <p class="eyebrow">PERANGKAT & PENGETAHUAN</p>
                <h3>Hal yang ditekuni</h3>
                <div v-if="entries.some((e) => e.kind === 'skill')" class="tags large">
                    <span
                        v-for="entry in entries.filter((e) => e.kind === 'skill')"
                        :key="entry.id"
                        >{{ entry.title }}</span
                    >
                </div>
                <p v-else class="muted">Bidang keahlian akan ditambahkan seiring perjalanan.</p>
                <span class="tile-flower" aria-hidden="true">✳</span>
            </div>
        </section>
        <section class="contact-banner">
            <p class="eyebrow">PUNYA IDE MENARIK?</p>
            <h2>Mari mulai sebuah<br /><span class="gradient-text">percakapan.</span></h2>
            <Link class="button" href="/kontak">Kirim pesan ↗</Link>
        </section>
    </SiteLayout>
</template>
