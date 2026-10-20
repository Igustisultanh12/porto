<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
import ContentCard from '../components/ContentCard.vue';
import Pagination from '../components/Pagination.vue';
import type { Content, ContentType, Paginated, Taxonomy } from '../types';
import { labels, publicationLabels } from '../i18n';
const props = defineProps<{
    type: ContentType;
    items: Paginated<Content>;
    filters: { q?: string; taxonomy?: number; year?: number; publication_type?: string };
    taxonomies: Taxonomy[];
    years: number[];
}>();
const filters = reactive({
    q: props.filters.q || '',
    taxonomy: props.filters.taxonomy || '',
    year: props.filters.year || '',
    publication_type: props.filters.publication_type || '',
});
function search() {
    router.get('/karya/' + props.type, filters, { preserveState: true, preserveScroll: true });
}
function reset() {
    Object.assign(filters, { q: '', taxonomy: '', year: '', publication_type: '' });
    search();
}
</script>
<template>
    <SiteLayout
        ><Meta :title="labels[type]" />
        <section class="page-intro">
            <p class="eyebrow">ARSIP / {{ labels[type].toUpperCase() }}</p>
            <h1>
                {{
                    type === 'project'
                        ? 'Ide yang diwujudkan.'
                        : type === 'research'
                          ? 'Pertanyaan yang dieksplorasi.'
                          : 'Catatan sepanjang jalan.'
                }}
            </h1>
            <p>
                {{
                    type === 'project'
                        ? 'Kumpulan proyek, proses, dan pelajaran di baliknya.'
                        : type === 'research'
                          ? 'Publikasi, kajian, dan pengetahuan yang dibagikan.'
                          : 'Pemikiran, eksperimen, dan hal baru yang dipelajari.'
                }}
            </p>
        </section>
        <form class="filters panel" @submit.prevent="search">
            <label class="grow"
                >Cari {{ labels[type].toLowerCase()
                }}<input
                    v-model="filters.q"
                    placeholder="Ketik judul atau kata kunci…"
                    maxlength="120" /></label
            ><label
                >Topik / kategori<select v-model="filters.taxonomy">
                    <option value="">Semua</option>
                    <option v-for="tax in taxonomies" :key="tax.id" :value="tax.id">
                        {{ tax.name }}
                    </option>
                </select></label
            ><template v-if="type === 'research'"
                ><label
                    >Tahun<select v-model="filters.year">
                        <option value="">Semua</option>
                        <option v-for="year in years" :key="year">{{ year }}</option>
                    </select></label
                ><label
                    >Jenis<select v-model="filters.publication_type">
                        <option value="">Semua</option>
                        <option v-for="(label, key) in publicationLabels" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select></label
                ></template
            ><button class="button">Cari →</button
            ><button type="button" class="text-link" @click="reset">Reset</button>
        </form>
        <p class="result-count">{{ items.total }} {{ labels[type].toLowerCase() }} ditemukan</p>
        <div v-if="items.data.length" class="card-grid">
            <ContentCard v-for="item in items.data" :key="item.id" :item="item" />
        </div>
        <div v-else class="empty panel">
            <span>◎</span>
            <h2>Belum ada hasil.</h2>
            <p>Coba kata kunci lain atau kembali setelah konten baru diterbitkan.</p>
        </div>
        <Pagination
            :current="items.current_page"
            :last="items.last_page"
            :previous="items.prev_page_url"
            :next="items.next_page_url"
        />
    </SiteLayout>
</template>
