<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import type { Shared } from '../types';
const page = usePage<Shared>();
const light = ref(false);
const logo = computed(() => page.props.siteMedia.find((m) => m.slot === 'logo'));
onMounted(() => {
    light.value = document.documentElement.classList.contains('light');
});
function toggle() {
    light.value = !light.value;
    document.documentElement.classList.toggle('light', light.value);
    try {
        localStorage.setItem('theme', light.value ? 'light' : 'dark');
    } catch {
        /* Storage may be disabled. */
    }
}
</script>
<template>
    <a class="skip" href="#main">Lewati ke konten</a>
    <div class="site-shell">
        <header class="site-header wrap">
            <Link href="/" class="brand"
                ><img v-if="logo" :src="logo.url" alt="" width="32" height="32" /><span
                    v-else
                    class="brand-mark"
                    >p<span>•</span></span
                ><span
                    >{{ page.props.site.name || 'Portofolio'
                    }}<small>karya & rasa ingin tahu</small></span
                ></Link
            >
            <nav aria-label="Navigasi utama">
                <Link
                    v-for="[url, label] in [
                        ['/', 'Beranda'],
                        ['/tentang', 'Tentang'],
                        ['/karya/project', 'Proyek'],
                        ['/karya/research', 'Riset'],
                        ['/karya/article', 'Catatan'],
                    ]"
                    :key="url"
                    :href="url"
                    :class="{ active: page.url.split('?')[0] === url }"
                    :aria-current="page.url.split('?')[0] === url ? 'page' : undefined"
                    >{{ label }}</Link
                >
            </nav>
            <div class="header-actions">
                <button
                    class="icon-button"
                    :aria-label="light ? 'Gunakan tema gelap' : 'Gunakan tema terang'"
                    @click="toggle"
                >
                    {{ light ? '◐' : '☼' }}</button
                ><Link href="/kontak" class="button small"
                    >Mari terhubung <span aria-hidden="true">↗</span></Link
                >
            </div>
        </header>
        <div v-if="page.props.flash.success" role="status" class="notice wrap">
            {{ page.props.flash.success }}
        </div>
        <main id="main" class="wrap"><slot /></main>
        <footer class="wrap site-footer">
            <div>
                <Link href="/" class="brand-text"
                    >{{ page.props.site.name || 'Portofolio' }}<span>✳</span></Link
                >
                <p>Ide, proses, dan hal-hal yang dipelajari.</p>
            </div>
            <div class="footer-links">
                <a
                    v-for="social in page.props.socials.filter((s) => s.url)"
                    :key="social.id"
                    :href="social.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    >{{ social.title }} ↗</a
                ><Link href="/kontak">Kontak ↗</Link>
            </div>
            <small>© {{ new Date().getFullYear() }} · Dibangun dengan rasa ingin tahu.</small>
        </footer>
    </div>
</template>
