<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { Shared } from '../types';
const props = defineProps<{
    title?: string;
    description?: string;
    image?: string;
    noindex?: boolean;
}>();
const page = usePage<Shared>();
const canonical = computed(() => {
    const origin = page.props.origin as string;
    return origin + page.url.split('?')[0];
});
const favicon = computed(() => page.props.siteMedia.find((m) => m.slot === 'favicon')?.url);
const description = computed(
    () =>
        props.description ||
        page.props.site.seo_description ||
        'Portofolio, riset, dan catatan dalam satu ruang.',
);
const structured = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'WebPage',
        name: props.title || page.props.site.name || 'Portofolio',
        description: description.value,
        url: canonical.value,
    }).replace(/</g, '\\u003c'),
);
</script>
<template>
    <Head :title="title || page.props.site.seo_title || page.props.site.name || 'Portofolio'">
        <meta name="description" :content="description" /><link rel="canonical" :href="canonical" />
        <meta property="og:title" :content="title || page.props.site.name || 'Portofolio'" />
        <meta property="og:description" :content="description" /><meta
            property="og:type"
            content="website"
        /><meta property="og:url" :content="canonical" />
        <meta v-if="image" property="og:image" :content="`${page.props.origin}${image}`" />
        <meta name="twitter:card" :content="image ? 'summary_large_image' : 'summary'" />
        <meta v-if="noindex" name="robots" content="noindex,nofollow" /><link
            v-if="favicon"
            rel="icon"
            :href="favicon"
        />
        <component :is="'script'" v-if="!noindex" type="application/ld+json">{{
            structured
        }}</component>
    </Head>
</template>
