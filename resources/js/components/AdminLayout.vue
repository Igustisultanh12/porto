<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { Shared } from '../types';
import Meta from './Meta.vue';
defineProps<{ title: string }>();
const page = usePage<Shared>();
</script>
<template>
    <Meta :title="title" noindex /><a class="skip" href="#main">Lewati ke konten</a>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <Link href="/admin" class="brand"
                ><span class="brand-mark">p<span>•</span></span
                ><span>Studio<small>portofolio & riset</small></span></Link
            >
            <p class="eyebrow">RUANG KERJA</p>
            <nav aria-label="Navigasi admin">
                <Link
                    v-for="[url, label] in [
                        ['/admin', 'Ringkasan'],
                        ['/admin/contents', 'Konten'],
                        ['/admin/taxonomies', 'Kategori & tag'],
                        ['/admin/settings', 'Profil & situs'],
                        ['/admin/messages', 'Inbox'],
                    ]"
                    :key="url"
                    :href="url"
                    :class="{ active: page.url.split('?')[0] === url }"
                    >{{ label }} <span>↗</span></Link
                >
            </nav>
            <div class="sidebar-bottom">
                <Link href="/" target="_blank">Lihat situs ↗</Link>
                <p>{{ page.props.auth.user?.name }}</p>
                <Link href="/admin/logout" method="post" as="button" class="text-link"
                    >Keluar →</Link
                >
            </div>
        </aside>
        <main id="main" class="admin-main">
            <header class="admin-heading">
                <div>
                    <p class="eyebrow">STUDIO / KELOLA</p>
                    <h1>{{ title }}</h1>
                </div>
                <span class="pill"><span class="dot"></span> ADMIN</span>
            </header>
            <div v-if="page.props.flash.success" role="status" class="notice">
                {{ page.props.flash.success }}
            </div>
            <slot />
        </main>
    </div>
</template>
