import axios from "axios";
import i18n from "./i18n";
import { router } from "@inertiajs/vue3"; // om props te herladen

export async function loadLocaleMessages(locale) {
    const { data } = await axios.get(`/lang/${locale}.json`);
    i18n.global.setLocaleMessage(locale, data);
    i18n.global.locale.value = locale;
}

export async function changeLocale(locale) {
    await axios.post("/locale", { locale });
    await loadLocaleMessages(locale);
    router.reload(); // herlaad Inertia props
}
