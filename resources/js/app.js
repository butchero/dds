import { createApp } from 'vue/dist/vue.esm-bundler.js';

const storageKey = 'dds-cookie-consent';

document.querySelectorAll('[data-cookies-open]').forEach((button) => {
    button.addEventListener('click', () => {
        window.dispatchEvent(new Event('dds-cookies-open'));
    });
});

document.querySelectorAll('[data-mobile-menu]').forEach((element) => {
    createApp({
        data: () => ({ open: false }),
    }).mount(element);
});

document.querySelectorAll('[data-banner-rotator]').forEach((element) => {
    createApp({
        data: () => ({ current: 0, timer: null, started: false }),
        mounted() {
            this.started = true;
            this.start();
        },
        beforeUnmount() {
            this.stop();
        },
        methods: {
            start() {
                this.stop();
                const total = element.querySelectorAll('#rotator img').length;
                if (total < 2) {
                    return;
                }
                this.timer = setInterval(() => {
                    const count = element.querySelectorAll('#rotator img').length;
                    this.current = (this.current + 1) % count;
                }, 3000);
            },
            stop() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            choose(index) {
                this.stop();
                this.current = index;
            },
        },
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
