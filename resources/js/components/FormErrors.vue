<script setup lang="ts">
defineProps<{ errors: Record<string, string | undefined> }>();
</script>
<template>
    <div v-if="Object.keys(errors).length" class="errors" role="alert">
        <strong>Periksa kembali isian berikut.</strong>
        <ul>
            <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
        </ul>
    </div>
</template>
