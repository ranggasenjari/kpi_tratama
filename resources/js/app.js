import '../css/app.css';

import { createInertiaApp, Link } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

createInertiaApp({
    title: (title) => (title ? `${title} - KPI Tratama` : 'KPI Tratama'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Link', Link)
            .mount(el);
    },
    progress: {
        color: '#2563eb',
    },
});
