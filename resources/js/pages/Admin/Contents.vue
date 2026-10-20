<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
import Pagination from '../../components/Pagination.vue';
import type { Content, Paginated } from '../../types';
import { labels } from '../../i18n';
const props = defineProps<{
    items: Paginated<Content>;
    filters: { q?: string; type?: string; status?: string };
}>();
const filters = reactive({
    q: props.filters.q || '',
    type: props.filters.type || '',
    status: props.filters.status || '',
});
const selected = ref<number[]>([]);
const busy = ref(false);
function search() {
    selected.value = [];
    router.get('/admin/contents', filters, { preserveState: true });
}
function bulk(action: string, ids = selected.value) {
    if (!ids.length) return;
    if (action === 'trash' && !confirm('Pindahkan konten terpilih ke sampah?')) return;
    busy.value = true;
    router.post(
        '/admin/contents/bulk',
        { action, ids },
        {
            preserveScroll: true,
            onFinish: () => (busy.value = false),
            onSuccess: () => (selected.value = []),
        },
    );
}
function move(item: Content, delta: number) {
    router.patch(
        `/admin/contents/${item.id}/order`,
        { sort_order: Math.max(0, item.sort_order + delta) },
        { preserveScroll: true },
    );
}
</script>
<template>
    <AdminLayout title="Konten"
        ><div class="actions">
            <Link href="/admin/contents/create" class="button">+ Buat konten</Link>
        </div>
        <form class="filters panel" @submit.prevent="search">
            <label class="grow"
                >Cari<input v-model="filters.q" placeholder="Judul konten" maxlength="120" /></label
            ><label
                >Jenis<select v-model="filters.type">
                    <option value="">Semua</option>
                    <option v-for="(label, key) in labels" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select></label
            ><label
                >Status<select v-model="filters.status">
                    <option value="">Semua aktif</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="trash">Sampah</option>
                </select></label
            ><button class="button secondary">Terapkan</button>
        </form>
        <div v-if="selected.length" class="actions bulk-bar">
            <span>{{ selected.length }} dipilih</span
            ><button
                v-if="filters.status !== 'trash'"
                class="button small"
                :disabled="busy"
                @click="bulk('publish')"
            >
                Terbitkan</button
            ><button
                v-if="filters.status !== 'trash'"
                class="button secondary small"
                :disabled="busy"
                @click="bulk('unpublish')"
            >
                Jadikan draft</button
            ><button
                class="button secondary small"
                :disabled="busy"
                @click="bulk(filters.status === 'trash' ? 'restore' : 'trash')"
            >
                {{ filters.status === 'trash' ? 'Pulihkan sebagai draft' : 'Ke sampah' }}
            </button>
        </div>
        <div class="panel table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pilih</th>
                        <th>Konten</th>
                        <th>Status</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items.data" :key="item.id">
                        <td>
                            <input
                                v-model="selected"
                                type="checkbox"
                                :value="item.id"
                                :aria-label="'Pilih ' + item.title"
                            />
                        </td>
                        <td>
                            <small>{{ labels[item.type] }}</small
                            ><br /><strong>{{ item.title }}</strong
                            ><span v-if="item.featured" class="accent"> ★</span>
                        </td>
                        <td>
                            <span class="tag">{{ item.deleted_at ? 'Sampah' : item.status }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <button
                                    class="icon-button"
                                    :aria-label="'Majukan ' + item.title"
                                    :disabled="item.sort_order === 0 || !!item.deleted_at"
                                    @click="move(item, -1)"
                                >
                                    −</button
                                >{{ item.sort_order
                                }}<button
                                    class="icon-button"
                                    :aria-label="'Mundurkan ' + item.title"
                                    :disabled="!!item.deleted_at"
                                    @click="move(item, 1)"
                                >
                                    +
                                </button>
                            </div>
                        </td>
                        <td>
                            <Link
                                v-if="!item.deleted_at"
                                :href="`/admin/contents/${item.id}/edit`"
                                class="text-link"
                                >Edit →</Link
                            ><button
                                v-else
                                class="text-link"
                                :disabled="busy"
                                @click="bulk('restore', [item.id])"
                            >
                                Pulihkan
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length">
                        <td colspan="5">Belum ada konten dalam tampilan ini.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination
            :current="items.current_page"
            :last="items.last_page"
            :previous="items.prev_page_url"
            :next="items.next_page_url"
    /></AdminLayout>
</template>
