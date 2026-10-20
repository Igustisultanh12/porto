<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../components/AdminLayout.vue';
import FormErrors from '../../components/FormErrors.vue';
import type { Taxonomy } from '../../types';
import { taxonomyLabels } from '../../i18n';
defineProps<{ items: Taxonomy[] }>();
const form = useForm({ id: null as number | null, kind: 'category', name: '' });
function remove(id: number) {
    if (confirm('Hapus taksonomi dan lepaskan dari semua konten?'))
        router.delete('/admin/taxonomies/' + id, { preserveScroll: true });
}
</script>
<template>
    <AdminLayout title="Kategori & tag"
        ><form
            class="panel filters"
            @submit.prevent="
                form.post('/admin/taxonomies', {
                    preserveScroll: true,
                    onSuccess: () => form.reset(),
                })
            "
        >
            <FormErrors :errors="form.errors" /><label
                >Jenis<select v-model="form.kind">
                    <option v-for="(label, key) in taxonomyLabels" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select></label
            ><label class="grow">Nama<input v-model="form.name" required maxlength="100" /></label
            ><button class="button" :disabled="form.processing">
                {{ form.id ? 'Perbarui' : '+ Tambahkan' }}</button
            ><button v-if="form.id" type="button" class="text-link" @click="form.reset()">
                Batal
            </button>
        </form>
        <div class="panel table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id">
                        <td>{{ taxonomyLabels[item.kind] }}</td>
                        <td>{{ item.name }}</td>
                        <td class="actions">
                            <button class="text-link" @click="Object.assign(form, item)">
                                Edit</button
                            ><button class="text-link danger" @click="remove(item.id)">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!items.length">
                        <td colspan="3">Belum ada kategori, tag, teknologi, atau topik.</td>
                    </tr>
                </tbody>
            </table>
        </div></AdminLayout
    >
</template>
