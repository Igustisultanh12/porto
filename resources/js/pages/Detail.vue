<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
import type { Content } from '../types';
import { labels, publicationLabels } from '../i18n';
const props = defineProps<{ content: Content; citation: string; preview: boolean }>();
const copied = ref('');
const cover = computed(() => props.content.media.find((m) => m.slot === 'cover'));
async function copy() {
    try {
        await navigator.clipboard.writeText(props.citation);
        copied.value = 'Sitasi disalin.';
    } catch {
        copied.value = 'Salin teks sitasi secara manual di bawah.';
    }
}
</script>
<template>
    <SiteLayout
        ><Meta
            :title="content.seo_title || content.title"
            :description="content.seo_description || content.summary"
            :image="cover?.url"
            :noindex="preview"
        />
        <div v-if="preview" class="notice">
            Preview admin · Status: {{ content.status }}.
            <Link :href="`/admin/contents/${content.id}/edit`">Kembali ke editor →</Link>
        </div>
        <article class="detail">
            <Link :href="'/karya/' + content.type" class="text-link"
                >← Semua {{ labels[content.type].toLowerCase() }}</Link
            >
            <header class="page-intro">
                <p class="eyebrow">
                    {{ labels[content.type] }}
                    <span v-if="content.year"> / {{ content.year }}</span>
                </p>
                <h1>{{ content.title }}</h1>
                <p>{{ content.summary }}</p>
                <div class="tags">
                    <span v-for="tag in content.taxonomies" :key="tag.id">{{ tag.name }}</span>
                </div>
            </header>
            <img
                v-if="cover"
                class="detail-cover"
                :src="cover.url"
                :alt="content.title"
                width="1440"
                height="880"
            />
            <div class="detail-grid">
                <div>
                    <section v-if="content.type === 'research'" class="panel metadata">
                        <p v-if="content.metadata.authors?.length">
                            <strong>Penulis</strong
                            ><span v-for="(author, i) in content.metadata.authors" :key="i"
                                >{{ author.name
                                }}<small v-if="author.affiliation">
                                    · {{ author.affiliation }}</small
                                ><br
                            /></span>
                        </p>
                        <p v-if="content.metadata.venue">
                            <strong>Venue / jurnal</strong>{{ content.metadata.venue }}
                        </p>
                        <p v-if="content.publication_type">
                            <strong>Jenis</strong>{{ publicationLabels[content.publication_type] }}
                        </p>
                        <p v-if="content.metadata.academic_status">
                            <strong>Status akademik</strong>{{ content.metadata.academic_status }}
                        </p>
                        <p v-if="content.metadata.publication_date">
                            <strong>Tanggal publikasi</strong
                            >{{ content.metadata.publication_date }}
                        </p>
                        <a
                            v-if="content.metadata.doi"
                            :href="`https://doi.org/${content.metadata.doi}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            >DOI: {{ content.metadata.doi }} ↗</a
                        >
                    </section>
                    <div class="prose" v-html="content.body_html"></div>
                    <template v-if="content.type === 'project'"
                        ><section
                            v-for="field in ['problem', 'approach', 'result'] as const"
                            :key="field"
                            class="prose"
                        >
                            <template v-if="content.metadata[field]"
                                ><h2>
                                    {{
                                        {
                                            problem: 'Tantangan',
                                            approach: 'Pendekatan',
                                            result: 'Hasil & pelajaran',
                                        }[field]
                                    }}
                                </h2>
                                <p class="preserve">{{ content.metadata[field] }}</p></template
                            >
                        </section></template
                    >
                    <div class="gallery">
                        <img
                            v-for="media in content.media.filter((m) => m.slot === 'gallery')"
                            :key="media.id"
                            :src="media.url"
                            :alt="media.name"
                            width="960"
                            height="640"
                            loading="lazy"
                        />
                    </div>
                    <section v-if="content.type === 'research'" class="panel citation">
                        <h2>Sitasi</h2>
                        <p class="preserve">{{ citation }}</p>
                        <div class="actions">
                            <button class="button secondary" @click="copy">Salin sitasi</button
                            ><a
                                v-if="!preview"
                                :href="`/riset/${content.slug}/bibtex`"
                                class="text-link"
                                >Unduh BibTeX ↓</a
                            >
                        </div>
                        <span role="status">{{ copied }}</span>
                    </section>
                </div>
                <aside class="panel detail-aside">
                    <p class="eyebrow">LEBIH DEKAT</p>
                    <template v-if="content.metadata.role"
                        ><h3>Peran</h3>
                        <p>{{ content.metadata.role }}</p></template
                    ><template v-if="content.metadata.period"
                        ><h3>Periode</h3>
                        <p>{{ content.metadata.period }}</p></template
                    >
                    <div class="stack">
                        <template
                            v-for="[key, label] in [
                                ['demo_url', 'Lihat demo'],
                                ['repository_url', 'Repository'],
                                ['dataset_url', 'Dataset'],
                                ['code_url', 'Kode riset'],
                            ]"
                            :key="key"
                            ><a
                                v-if="content.metadata[key as keyof typeof content.metadata]"
                                :href="
                                    String(content.metadata[key as keyof typeof content.metadata])
                                "
                                class="button secondary"
                                target="_blank"
                                rel="noopener noreferrer"
                                >{{ label }} ↗</a
                            ></template
                        ><a
                            v-for="media in content.media.filter((m) => m.slot === 'pdf')"
                            :key="media.id"
                            :href="media.url"
                            class="button"
                            >Unduh PDF ↓</a
                        ><Link href="/kontak" class="text-link">Diskusikan karya ini →</Link>
                    </div>
                </aside>
            </div>
        </article>
    </SiteLayout>
</template>
