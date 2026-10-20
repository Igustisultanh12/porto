<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
import FormErrors from '../../components/FormErrors.vue';
import MediaManager from '../../components/MediaManager.vue';
import type { Content, ContentType, Taxonomy, Metadata } from '../../types';
import { labels, publicationLabels, taxonomyLabels } from '../../i18n';
const props = defineProps<{ content: Content | null; type: ContentType; taxonomies: Taxonomy[] }>();
const savedPreview = ref(false);
const metadata: Metadata = {
    role: '',
    period: '',
    problem: '',
    approach: '',
    result: '',
    demo_url: '',
    repository_url: '',
    dataset_url: '',
    code_url: '',
    venue: '',
    doi: '',
    academic_status: '',
    publication_date: '',
    authors: [],
    ...props.content?.metadata,
};
const form = useForm({
    type: props.type,
    title: props.content?.title || '',
    slug: props.content?.slug || '',
    summary: props.content?.summary || '',
    body: props.content?.body || '',
    status: props.content?.status || 'draft',
    featured: props.content?.featured || false,
    sort_order: props.content?.sort_order || 0,
    year: props.content?.year ?? null,
    publication_type: props.content?.publication_type || '',
    metadata,
    seo_title: props.content?.seo_title || '',
    seo_description: props.content?.seo_description || '',
    taxonomy_ids: props.content?.taxonomies.map((t) => t.id) || [],
});
const title = computed(() => (props.content ? 'Edit ' + labels[form.type] : 'Buat konten'));
function save() {
    if (props.content) form.put('/admin/contents/' + props.content.id, { preserveScroll: true });
    else form.post('/admin/contents');
}
function remove() {
    if (props.content && confirm('Pindahkan konten ini ke sampah?'))
        router.delete('/admin/contents/' + props.content.id);
}
function swap(index: number, offset: number) {
    const a = form.metadata.authors || [];
    const next = index + offset;
    if (next < 0 || next >= a.length) return;
    [a[index], a[next]] = [a[next], a[index]];
}
</script>
<template>
    <AdminLayout :title="title"
        ><div class="editor-grid">
            <form class="stack" @submit.prevent="save">
                <FormErrors :errors="form.errors" />
                <section class="panel form-panel">
                    <div class="form-grid">
                        <label
                            >Jenis konten<select v-model="form.type">
                                <option v-for="(label, key) in labels" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select></label
                        ><label
                            >Status situs<select v-model="form.status">
                                <option value="draft">Draft — privat</option>
                                <option value="published">Published — publik</option>
                            </select></label
                        >
                    </div>
                    <label>Judul<input v-model="form.title" required maxlength="180" /></label
                    ><label
                        >Slug URL<input
                            v-model="form.slug"
                            maxlength="190"
                            placeholder="Otomatis dari judul jika dikosongkan" /></label
                    ><label
                        >{{ form.type === 'research' ? 'Abstrak' : 'Ringkasan'
                        }}<textarea v-model="form.summary" rows="4" maxlength="2000"></textarea>
                    </label>
                    <div class="actions">
                        <button type="button" class="text-link" @click="savedPreview = false">
                            Editor Markdown</button
                        ><button type="button" class="text-link" @click="savedPreview = true">
                            Pratinjau tersimpan
                        </button>
                    </div>
                    <label v-if="!savedPreview"
                        >Isi (Markdown)<textarea
                            v-model="form.body"
                            rows="16"
                            class="code-input"
                            maxlength="100000"
                            placeholder="## Cerita di balik karya"
                        ></textarea>
                    </label>
                    <div v-else>
                        <p class="notice">Pratinjau menampilkan versi terakhir yang disimpan.</p>
                        <div
                            class="prose"
                            v-html="content?.body_html || '<p>Belum ada isi tersimpan.</p>'"
                        ></div>
                    </div>
                </section>
                <section v-if="form.type === 'project'" class="panel form-panel">
                    <h2>Detail proyek</h2>
                    <div class="form-grid">
                        <label>Peran<input v-model="form.metadata.role" maxlength="200" /></label
                        ><label
                            >Periode<input
                                v-model="form.metadata.period"
                                placeholder="Contoh: Januari – Maret 2026"
                                maxlength="100" /></label
                        ><label>URL demo<input v-model="form.metadata.demo_url" type="url" /></label
                        ><label
                            >URL repository<input v-model="form.metadata.repository_url" type="url"
                        /></label>
                    </div>
                    <label
                        v-for="[key, label] in [
                            ['problem', 'Tantangan'],
                            ['approach', 'Pendekatan'],
                            ['result', 'Hasil & pelajaran'],
                        ]"
                        :key="key"
                        >{{ label
                        }}<textarea
                            v-model="form.metadata[key as 'problem' | 'approach' | 'result']"
                            rows="4"
                            maxlength="10000"
                        ></textarea>
                    </label>
                </section>
                <section v-if="form.type === 'research'" class="panel form-panel">
                    <h2>Metadata riset</h2>
                    <p class="muted">Status akademik terpisah dari status publikasi situs.</p>
                    <div class="form-grid">
                        <label
                            >Tahun<input
                                v-model="form.year"
                                type="number"
                                min="1900"
                                max="2100" /></label
                        ><label
                            >Jenis publikasi<select v-model="form.publication_type">
                                <option value="">Belum ditentukan</option>
                                <option
                                    v-for="(label, key) in publicationLabels"
                                    :key="key"
                                    :value="key"
                                >
                                    {{ label }}
                                </option>
                            </select></label
                        ><label
                            >Status akademik<select v-model="form.metadata.academic_status">
                                <option value="">Belum ditentukan</option>
                                <option
                                    v-for="status in [
                                        'preprint',
                                        'submitted',
                                        'accepted',
                                        'published',
                                    ]"
                                    :key="status"
                                >
                                    {{ status }}
                                </option>
                            </select></label
                        ><label
                            >Tanggal publikasi<input
                                v-model="form.metadata.publication_date"
                                type="date" /></label
                        ><label
                            >Venue / jurnal<input
                                v-model="form.metadata.venue"
                                maxlength="255" /></label
                        ><label
                            >DOI<input
                                v-model="form.metadata.doi"
                                placeholder="10.xxxx/identifier"
                                maxlength="255" /></label
                        ><label
                            >URL dataset<input
                                v-model="form.metadata.dataset_url"
                                type="url" /></label
                        ><label
                            >URL kode<input v-model="form.metadata.code_url" type="url"
                        /></label>
                    </div>
                    <h3>Penulis (sesuai urutan)</h3>
                    <div
                        v-for="(author, index) in form.metadata.authors"
                        :key="index"
                        class="author-row"
                    >
                        <label>Nama<input v-model="author.name" required maxlength="150" /></label
                        ><label
                            >Afiliasi<input v-model="author.affiliation" maxlength="255"
                        /></label>
                        <div class="actions">
                            <button
                                type="button"
                                class="icon-button"
                                :disabled="index === 0"
                                aria-label="Naikkan penulis"
                                @click="swap(index, -1)"
                            >
                                ↑</button
                            ><button
                                type="button"
                                class="icon-button"
                                :disabled="index === (form.metadata.authors?.length || 0) - 1"
                                aria-label="Turunkan penulis"
                                @click="swap(index, 1)"
                            >
                                ↓</button
                            ><button
                                type="button"
                                class="text-link danger"
                                @click="form.metadata.authors?.splice(index, 1)"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="button secondary"
                        @click="form.metadata.authors?.push({ name: '', affiliation: '' })"
                    >
                        + Penulis
                    </button>
                </section>
                <section class="panel form-panel">
                    <h2>Pengelompokan & tampilan</h2>
                    <p v-if="!taxonomies.length" class="muted">
                        Tambahkan kategori dan tag melalui menu
                        <Link href="/admin/taxonomies">Kategori & tag</Link>.
                    </p>
                    <div class="tags">
                        <label v-for="tag in taxonomies" :key="tag.id" class="checkbox tag"
                            ><input v-model="form.taxonomy_ids" type="checkbox" :value="tag.id" />{{
                                taxonomyLabels[tag.kind]
                            }}: {{ tag.name }}</label
                        >
                    </div>
                    <div class="form-grid">
                        <label
                            >Urutan (angka kecil lebih awal)<input
                                v-model="form.sort_order"
                                type="number"
                                min="0"
                                max="100000"
                                required /></label
                        ><label class="checkbox"
                            ><input v-model="form.featured" type="checkbox" /> Tampilkan di
                            beranda</label
                        >
                    </div>
                </section>
                <section class="panel form-panel">
                    <h2>SEO</h2>
                    <label
                        >Judul SEO<input
                            v-model="form.seo_title"
                            maxlength="180"
                            placeholder="Menggunakan judul konten jika kosong" /></label
                    ><label
                        >Deskripsi SEO<textarea
                            v-model="form.seo_description"
                            maxlength="320"
                            rows="3"
                            placeholder="Menggunakan ringkasan jika kosong"
                        ></textarea>
                    </label>
                </section>
                <div class="editor-save">
                    <button class="button" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan…' : 'Simpan konten' }}</button
                    ><Link
                        v-if="content"
                        :href="`/admin/contents/${content.id}/preview`"
                        class="button secondary"
                        target="_blank"
                        >Preview ↗</Link
                    ><button v-if="content" type="button" class="text-link danger" @click="remove">
                        Ke sampah
                    </button>
                </div>
            </form>
            <aside>
                <MediaManager v-if="content" :content-id="content.id" :items="content.media" />
                <div v-else class="panel form-panel">
                    <h2>Tambahkan berkas</h2>
                    <p class="muted">
                        Simpan konten terlebih dahulu untuk mengunggah cover, galeri, atau PDF.
                    </p>
                </div>
                <div class="panel form-panel">
                    <p class="eyebrow">CATATAN EDITOR</p>
                    <p>
                        Draft hanya dapat dilihat admin. Perubahan pada konten published langsung
                        terlihat di situs setelah disimpan.
                    </p>
                </div>
            </aside>
        </div></AdminLayout
    >
</template>
