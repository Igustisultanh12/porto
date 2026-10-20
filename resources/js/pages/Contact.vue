<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import type { Shared } from '../types';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
import FormErrors from '../components/FormErrors.vue';
const props = defineProps<{ submissionId: string }>();
const page = usePage<Shared>();
const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    website: '',
    submission_id: props.submissionId,
});
function submit() {
    form.post('/kontak', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('name', 'email', 'subject', 'message');
            form.submission_id = crypto.randomUUID();
        },
    });
}
</script>
<template>
    <SiteLayout
        ><Meta title="Kontak" />
        <section class="page-intro">
            <p class="eyebrow">MARI TERHUBUNG</p>
            <h1>Ide bagus dimulai<br />dari percakapan<span class="accent">.</span></h1>
            <p>Untuk kolaborasi, diskusi riset, atau sekadar bertukar pemikiran.</p>
        </section>
        <div class="contact-grid">
            <aside>
                <h2>Apa yang sedang<br />kamu pikirkan?</h2>
                <p class="muted">
                    Ceritakan konteks dan tujuanmu agar percakapan bisa dimulai dengan baik.
                </p>
                <a
                    v-if="page.props.site.contact_email"
                    :href="'mailto:' + page.props.site.contact_email"
                    class="text-link"
                    >{{ page.props.site.contact_email }} ↗</a
                >
                <p class="privacy-note">
                    Nama, email, dan pesanmu hanya digunakan untuk menanggapi percakapan ini.
                </p>
            </aside>
            <form class="panel form-panel" @submit.prevent="submit">
                <FormErrors :errors="form.errors" />
                <div class="form-grid">
                    <label
                        >Nama<input
                            v-model="form.name"
                            required
                            maxlength="100"
                            autocomplete="name" /></label
                    ><label
                        >Email<input
                            v-model="form.email"
                            required
                            type="email"
                            maxlength="255"
                            autocomplete="email"
                    /></label>
                </div>
                <label
                    >Subjek<input
                        v-model="form.subject"
                        required
                        maxlength="160"
                        placeholder="Kolaborasi, diskusi riset, atau lainnya" /></label
                ><label
                    >Pesan<textarea
                        v-model="form.message"
                        required
                        minlength="10"
                        maxlength="10000"
                        rows="7"
                        placeholder="Ceritakan idemu…"
                    ></textarea>
                </label>
                <div class="honeypot" aria-hidden="true">
                    <label
                        >Website<input v-model="form.website" tabindex="-1" autocomplete="off"
                    /></label>
                </div>
                <button class="button" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan…' : 'Kirim pesan ↗' }}
                </button>
            </form>
        </div></SiteLayout
    >
</template>
