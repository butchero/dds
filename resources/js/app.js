import { createApp } from 'vue/dist/vue.esm-bundler.js';
import CatalogLayoutToggle from './Components/CatalogLayoutToggle.vue';

const storageKey = 'dds-cookie-consent';

document.querySelectorAll('[data-cookies-open]').forEach((button) => {
    button.addEventListener('click', () => {
        window.dispatchEvent(new Event('dds-cookies-open'));
    });
});

document.querySelectorAll('[data-catalog-layout-toggle]').forEach((element) => {
    createApp(CatalogLayoutToggle, {
        gridUrl: element.dataset.gridUrl,
        listUrl: element.dataset.listUrl,
        layout: element.dataset.layout,
        gridLabel: element.dataset.gridLabel,
        listLabel: element.dataset.listLabel,
    }).mount(element);
});

document.querySelectorAll('[data-mobile-menu]').forEach((element) => {
    createApp({
        data: () => ({ open: false }),
    }).mount(element);
});

document.querySelectorAll('[data-cookies]').forEach((element) => {
    createApp({
        data: () => ({ visible: false, details: false }),
        mounted() {
            if (!this.read()) {
                this.visible = true;
            }
            window.addEventListener('dds-cookies-open', this.onOpen);
        },
        beforeUnmount() {
            window.removeEventListener('dds-cookies-open', this.onOpen);
        },
        methods: {
            onOpen() {
                this.visible = true;
                this.details = true;
            },
            read() {
                try {
                    return JSON.parse(localStorage.getItem(storageKey) || 'null');
                } catch {
                    return null;
                }
            },
            save(choice) {
                const value = {
                    necessary: true,
                    statistics: choice === 'all',
                    decidedAt: new Date().toISOString(),
                };
                localStorage.setItem(storageKey, JSON.stringify(value));
                document.cookie = `${storageKey}=${encodeURIComponent(JSON.stringify(value))}; path=/; max-age=${60 * 60 * 24 * 180}; SameSite=Lax`;
                this.visible = false;
                this.details = false;
            },
        },
    }).mount(element);
});
