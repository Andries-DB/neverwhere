import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { AllCommunityModule, ModuleRegistry } from "ag-grid-community";

import i18n from "./i18n";
import { loadLocaleMessages } from "./lang";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

// Register ag-Grid Community modules globally
ModuleRegistry.registerModules([AllCommunityModule]);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    async setup({ el, App, props, plugin }) {
        const initialLocale = props.initialPage.props.locale || "en";
        await loadLocaleMessages(initialLocale);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
