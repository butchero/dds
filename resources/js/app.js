import { createApp } from 'vue/dist/vue.esm-bundler.js';
import AccountFormDialog from './Components/AccountFormDialog.vue';
import AppointmentForm from './Components/AppointmentForm.vue';
import CatalogLayoutToggle from './Components/CatalogLayoutToggle.vue';
import HeaderAccountLinks from './Components/HeaderAccountLinks.vue';
import ProductView from './Components/ProductView.vue';

const storageKey = 'cookie_consent';

document.querySelectorAll('[data-cookies-open]').forEach((button) => {
    button.addEventListener('click', () => {
        window.dispatchEvent(new Event('cookies-open'));
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

document.querySelectorAll('[data-appointment-form]').forEach((element) => {
    const payload = element.querySelector('[data-appointment-payload]');

    createApp(AppointmentForm, JSON.parse(payload?.textContent || '{}')).mount(element);
});

document.querySelectorAll('[data-account-modal]').forEach((element) => {
    const body = element.querySelector('[data-dialog-body]');

    createApp(AccountFormDialog, {
        initiallyOpen: element.dataset.open === '1',
        label: element.dataset.label,
        title: element.dataset.title,
        triggerClass: element.dataset.triggerClass ?? '',
        body: body?.innerHTML ?? '',
        cancelLabel: element.dataset.cancelLabel,
        closeLabel: element.dataset.closeLabel,
    }).mount(element);
});

document.querySelectorAll('[data-product-view]').forEach((element) => {
    const payload = JSON.parse(element.querySelector('[data-product-payload]')?.textContent || '{}');
    const price = element.querySelector('[data-product-price]')?.innerHTML ?? '';

    createApp(ProductView, { ...payload, price }).mount(element);
});

document.querySelectorAll('[data-mobile-menu]').forEach((element) => {
    createApp({
        components: { HeaderAccountLinks },
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
            window.addEventListener('cookies-open', this.onOpen);
        },
        beforeUnmount() {
            window.removeEventListener('cookies-open', this.onOpen);
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
