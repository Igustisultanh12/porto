<script setup lang="ts">
import { useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
import FormErrors from '../../components/FormErrors.vue';
import MediaManager from '../../components/MediaManager.vue';
import type { Site, Entry, Shared } from '../../types';
import { entryLabels } from '../../i18n';
const props = defineProps<{ settings: Site; entries: Entry[] }>();
const page = usePage<Shared>();
const form = useForm({
    name: '',
    headline: '',
    bio: '',
    location: '',
    research_interests: '',
    contact_email: '',
    seo_title: '',
    seo_description: '',
    ...props.settings,
});
const entry = useForm({
    id: null as number | null,
    kind: 'skill',
    title: '',
    subtitle: '',
    body: '',
    url: '',
    sort_order: 0,
});
const password = useForm({ current_password: '', password: '', password_confirmation: '' });
function edit(item: Entry) {
    Object.assign(entry, item);
    document.getElementById('entry-title')?.focus();
}
function remove(id: number) {
    if (confirm('Hapus entri profil ini?'))
        router.delete('/admin/entries/' + id, { preserveScroll: true });
}
</script>
<template>
    <AdminLayout title="Profil & situs"
        ><div class="editor-grid">
            <div class="stack">
                <form
                    class="panel form-panel"
                    @submit.prevent="form.put('/admin/settings', { preserveScroll: true })"
                >
                    <h2>Identitas & profil</h2>
                    <FormErrors :errors="form.errors" />
                    <div class="form-grid">
                        <label>Nama tampilan<input v-model="form.name" maxlength="100" /></label
                        ><label>Lokasi<input v-model="form.location" maxlength="150" /></label>
                    </div>
                    <label>Headline<input v-model="form.headline" maxlength="180" /></label
                    ><label
                        >Bio<textarea
                            v-model="form.bio"
                            rows="6"
                            maxlength="10000"
                        ></textarea></label
                    ><label
                        >Minat riset<textarea
                            v-model="form.research_interests"
                            rows="3"
                            maxlength="2000"
                        ></textarea></label
                    ><label>Email publik<input v-model="form.contact_email" type="email" /></label
                    ><label
                        >Judul SEO default<input v-model="form.seo_title" maxlength="180" /></label
                    ><label
                        >Deskripsi SEO default<textarea
                            v-model="form.seo_description"
                            rows="3"
                            maxlength="320"
                        ></textarea></label
                    ><button class="button" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan…' : 'Simpan profil' }}
                    </button>
                </form>
                <section class="panel form-panel">
                    <h2>Pengalaman, pendidikan & tautan</h2>
                    <form
                        class="stack"
                        @submit.prevent="
                            entry.post('/admin/entries', {
                                preserveScroll: true,
                                onSuccess: () => entry.reset(),
                            })
                        "
                    >
                        <FormErrors :errors="entry.errors" />
                        <div class="form-grid">
                            <label
                                >Jenis<select v-model="entry.kind">
                                    <option
                                        v-for="(label, key) in entryLabels"
                                        :key="key"
                                        :value="key"
                                    >
                                        {{ label }}
                                    </option>
                                </select></label
                            ><label
                                >Urutan<input
                                    v-model="entry.sort_order"
                                    type="number"
                                    min="0"
                                    max="100000"
                            /></label>
                        </div>
                        <label
                            >Judul<input
                                id="entry-title"
                                v-model="entry.title"
                                required
                                maxlength="180" /></label
                        ><label
                            >Subjudul / periode<input
                                v-model="entry.subtitle"
                                maxlength="255" /></label
                        ><label
                            >Deskripsi<textarea
                                v-model="entry.body"
                                rows="3"
                                maxlength="5000"
                            ></textarea></label
                        ><label>URL (opsional)<input v-model="entry.url" type="url" /></label>
                        <div class="actions">
                            <button class="button secondary" :disabled="entry.processing">
                                {{ entry.id ? 'Perbarui entri' : '+ Tambah entri' }}</button
                            ><button
                                v-if="entry.id"
                                type="button"
                                class="text-link"
                                @click="entry.reset()"
                            >
                                Batal edit
                            </button>
                        </div>
                    </form>
                    <div class="entry-list">
                        <article v-for="item in entries" :key="item.id">
                            <div>
                                <small>{{ entryLabels[item.kind] }} · {{ item.sort_order }}</small>
                                <h3>{{ item.title }}</h3>
                                <p>{{ item.subtitle }}</p>
                            </div>
                            <div class="actions">
                                <button class="text-link" @click="edit(item)">Edit</button
                                ><button class="text-link danger" @click="remove(item.id)">
                                    Hapus
                                </button>
                            </div>
                        </article>
                    </div>
                </section>
                <form
                    class="panel form-panel"
                    @submit.prevent="
                        password.put('/admin/password', {
                            preserveScroll: true,
                            onSuccess: () => password.reset(),
                        })
                    "
                >
                    <h2>Keamanan akun</h2>
                    <FormErrors :errors="password.errors" /><label
                        >Kata sandi saat ini<input
                            v-model="password.current_password"
                            type="password"
                            autocomplete="current-password"
                            required /></label
                    ><label
                        >Kata sandi baru<input
                            v-model="password.password"
                            type="password"
                            autocomplete="new-password"
                            minlength="12"
                            required /></label
                    ><label
                        >Ulangi kata sandi baru<input
                            v-model="password.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                    /></label>
                    <p class="muted">
                        Minimal 12 karakter dengan huruf besar, huruf kecil, dan angka.
                    </p>
                    <button class="button secondary" :disabled="password.processing">
                        Ganti kata sandi
                    </button>
                </form>
            </div>
            <aside><MediaManager :items="page.props.siteMedia" /></aside></div
    ></AdminLayout>
</template>
