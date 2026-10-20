<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import SiteLayout from '../components/SiteLayout.vue';
import Meta from '../components/Meta.vue';
import FormErrors from '../components/FormErrors.vue';
const props = defineProps<{
    mode: 'login' | 'forgot' | 'reset';
    resetEnabled?: boolean;
    token?: string;
    email?: string;
}>();
const form = useForm({
    email: props.email || '',
    password: '',
    password_confirmation: '',
    remember: false,
    token: props.token || '',
});
function submit() {
    form.post(
        props.mode === 'login'
            ? '/admin/login'
            : props.mode === 'forgot'
              ? '/admin/forgot-password'
              : '/admin/reset-password',
        { onFinish: () => form.reset('password', 'password_confirmation') },
    );
}
</script>
<template>
    <SiteLayout
        ><Meta title="Akses admin" noindex />
        <section class="auth-card panel">
            <p class="eyebrow">PORTOFOLIO / STUDIO</p>
            <h1>
                {{
                    mode === 'login'
                        ? 'Selamat datang kembali.'
                        : mode === 'forgot'
                          ? 'Lupa kata sandi?'
                          : 'Buat kata sandi baru.'
                }}
            </h1>
            <p class="muted">
                {{
                    mode === 'login'
                        ? 'Masuk untuk mengelola karya dan risetmu.'
                        : 'Gunakan email akun admin yang terdaftar.'
                }}
            </p>
            <form class="stack" @submit.prevent="submit">
                <FormErrors :errors="form.errors" /><label
                    >Email<input
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username" /></label
                ><label v-if="mode !== 'forgot'"
                    >Kata sandi<input
                        v-model="form.password"
                        type="password"
                        required
                        :minlength="mode === 'reset' ? 12 : undefined"
                        :autocomplete="
                            mode === 'login' ? 'current-password' : 'new-password'
                        " /></label
                ><label v-if="mode === 'reset'"
                    >Ulangi kata sandi<input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password" /></label
                ><label v-if="mode === 'login'" class="checkbox"
                    ><input v-model="form.remember" type="checkbox" /> Ingat saya</label
                ><button class="button" :disabled="form.processing">
                    {{
                        form.processing ? 'Memproses…' : mode === 'login' ? 'Masuk →' : 'Kirim →'
                    }}</button
                ><Link v-if="mode === 'login' && resetEnabled" href="/admin/forgot-password"
                    >Lupa kata sandi?</Link
                ><Link v-if="mode !== 'login'" href="/admin/login">← Kembali masuk</Link>
            </form>
        </section></SiteLayout
    >
</template>
