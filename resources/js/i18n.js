import { createI18n } from "vue-i18n";

const i18n = createI18n({
    legacy: false, // Composition API modus
    locale: "en", // default taal
    messages: {}, // wordt lazy geladen
});

export default i18n;
