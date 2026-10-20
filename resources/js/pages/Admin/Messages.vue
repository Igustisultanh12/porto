<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
import Pagination from '../../components/Pagination.vue';
import type { Message, Paginated } from '../../types';
const props = defineProps<{
    items: Paginated<Message>;
    filters: { q?: string; status?: string };
}>();
const filters = reactive({ q: props.filters.q || '', status: props.filters.status || '' });
function action(id: number, action: string) {
    if (action === 'delete' && !confirm('Hapus pesan secara permanen?')) return;
    router.patch('/admin/messages/' + id, { action }, { preserveScroll: true });
}
</script>
<template>
    <AdminLayout title="Inbox"
        ><form
            class="panel filters"
            @submit.prevent="router.get('/admin/messages', filters, { preserveState: true })"
        >
            <label class="grow">Cari pesan<input v-model="filters.q" maxlength="120" /></label
            ><label
                >Status<select v-model="filters.status">
                    <option value="">Semua aktif</option>
                    <option value="unread">Belum dibaca</option>
                    <option value="archive">Arsip</option>
                </select></label
            ><button class="button secondary">Terapkan</button>
        </form>
        <div class="stack">
            <article v-for="message in items.data" :key="message.id" class="panel form-panel">
                <div class="section-head">
                    <div>
                        <span v-if="!message.read_at" class="tag">Belum dibaca</span>
                        <h2>{{ message.subject }}</h2>
                        <p>
                            {{ message.name }} ·
                            <a :href="'mailto:' + message.email">{{ message.email }}</a>
                        </p>
                        <small>{{ message.created_at.slice(0, 16).replace('T', ' ') }} UTC</small>
                    </div>
                </div>
                <p class="preserve">{{ message.message }}</p>
                <div class="actions">
                    <button
                        class="button secondary small"
                        @click="action(message.id, message.read_at ? 'unread' : 'read')"
                    >
                        {{ message.read_at ? 'Tandai belum dibaca' : 'Tandai dibaca' }}</button
                    ><button
                        class="text-link"
                        @click="action(message.id, message.archived_at ? 'unarchive' : 'archive')"
                    >
                        {{ message.archived_at ? 'Keluarkan dari arsip' : 'Arsipkan' }}</button
                    ><button class="text-link danger" @click="action(message.id, 'delete')">
                        Hapus
                    </button>
                </div>
            </article>
            <div v-if="!items.data.length" class="empty panel">
                <h2>Inbox bersih.</h2>
                <p>Pesan yang cocok dengan filter akan tampil di sini.</p>
            </div>
        </div>
        <Pagination
            :current="items.current_page"
            :last="items.last_page"
            :previous="items.prev_page_url"
            :next="items.next_page_url"
    /></AdminLayout>
</template>
