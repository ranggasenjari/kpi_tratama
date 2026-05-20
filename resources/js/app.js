import '../css/app.css';

import { createInertiaApp, Link } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

const appName = 'PT. TRATAMA KREATIF INDONESIA';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
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
