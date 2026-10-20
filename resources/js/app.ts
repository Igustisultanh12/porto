import '../css/portfolio.css';
import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');
createInertiaApp({
    resolve: async (name) => (await pages[`./pages/${name}.vue`]()).default,
    title: (title) => (title ? `${title} · Portofolio` : 'Portofolio'),
});
