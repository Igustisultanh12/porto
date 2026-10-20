<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import type { Media } from '../types';
import FormErrors from './FormErrors.vue';
const props = defineProps<{ contentId?: number; items: Media[] }>();
const form = useForm<{ content_id: number | null; slot: string; file: File | null }>({
    content_id: props.contentId ?? null,
    slot: props.contentId ? 'cover' : 'avatar',
    file: null,
});
function submit() {
    form.post('/admin/media', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset('file'),
    });
}
function remove(id: number) {
    if (confirm('Lepas berkas ini dari situs?'))
        router.delete('/admin/media/' + id, { preserveScroll: true });
}
</script>
<template>
    <section class="panel form-panel">
        <h2>Berkas & media</h2>
        <p class="muted">
            Gambar JPG, PNG, WebP atau PDF, maksimal 10 MB. Gambar dioptimalkan ke WebP, maksimal
            1920 piksel.
        </p>
        <form class="stack" @submit.prevent="submit">
            <FormErrors :errors="form.errors" /><label
                >Penggunaan<select v-model="form.slot">
                    <template v-if="contentId"
                        ><option value="cover">Cover</option>
                        <option value="gallery">Galeri</option>
                        <option value="pdf">Lampiran PDF</option></template
                    ><template v-else
                        ><option value="avatar">Foto profil</option>
                        <option value="logo">Logo</option>
                        <option value="favicon">Favicon</option>
                        <option value="cv">CV (PDF)</option></template
                    >
                </select></label
            ><label
                >Pilih berkas<input
                    type="file"
                    required
                    :accept="
                        ['pdf', 'cv'].includes(form.slot)
                            ? 'application/pdf'
                            : 'image/jpeg,image/png,image/webp'
                    "
                    @change="
                        form.file = ($event.target as HTMLInputElement).files?.[0] ?? null
                    " /></label
            ><progress v-if="form.progress" :value="form.progress.percentage" max="100"></progress
            ><button class="button secondary" :disabled="form.processing || !form.file">
                {{ form.processing ? 'Mengunggah…' : 'Unggah berkas' }}
            </button>
        </form>
        <div class="media-list">
            <article v-for="media in items" :key="media.id">
                <img
                    v-if="media.mime.startsWith('image/')"
                    :src="media.url"
                    :alt="media.name"
                    width="80"
                    height="60"
                    loading="lazy"
                />
                <div>
                    <span class="tag">{{ media.slot }}</span
                    ><a :href="media.url" target="_blank" rel="noopener">{{ media.name }}</a>
                </div>
                <button class="text-link danger" @click="remove(media.id)">Lepas</button>
            </article>
        </div>
    </section>
</template>
