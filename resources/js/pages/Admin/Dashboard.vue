<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
defineProps<{
    counts: { content: number; draft: number; unread: number };
    activities: { id: number; action: string; subject: string; created_at: string }[];
}>();
</script>
<template>
    <AdminLayout title="Selamat datang di Studio."
        ><div class="admin-stats">
            <div
                v-for="[key, label] in [
                    ['content', 'Total konten'],
                    ['draft', 'Draft tersimpan'],
                    ['unread', 'Pesan belum dibaca'],
                ]"
                :key="key"
                class="panel"
            >
                <p>{{ label }}</p>
                <strong>{{ counts[key as keyof typeof counts] }}</strong>
            </div>
        </div>
        <section class="panel welcome-panel">
            <div>
                <p class="eyebrow">MULAI DARI SEBUAH IDE</p>
                <h2>Apa yang ingin kamu bagikan?</h2>
                <p>Dokumentasikan prosesmu. Terbitkan ketika sudah siap.</p>
            </div>
            <div class="actions">
                <Link class="button" href="/admin/contents/create?type=project">+ Proyek</Link
                ><Link class="button secondary" href="/admin/contents/create?type=research"
                    >+ Riset</Link
                ><Link class="button secondary" href="/admin/contents/create?type=article"
                    >+ Catatan</Link
                >
            </div>
        </section>
        <section class="section">
            <h2>Aktivitas terbaru</h2>
            <div class="panel table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="activity in activities" :key="activity.id">
                            <td>{{ activity.action }}</td>
                            <td>{{ activity.subject }}</td>
                            <td>{{ activity.created_at.slice(0, 16).replace('T', ' ') }} UTC</td>
                        </tr>
                        <tr v-if="!activities.length">
                            <td colspan="3">
                                Belum ada aktivitas. Mulai dengan melengkapi profil.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section></AdminLayout
    >
</template>
