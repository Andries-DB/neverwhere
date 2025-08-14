<template>
    <Head title="Instellingen" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Instellingen</h1>
            <p class="text-gray-600 mt-1">
                Beheer je persoonlijke voorkeuren en account instellingen
            </p>
        </div>

        <!-- Settings Form -->
        <div>
            <form @submit.prevent="saveSettings">
                <div class="flex flex-col gap-8">
                    <!-- Language Section -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Taal & Regio
                        </h2>

                        <!-- Language Selector -->
                        <div
                            class="mb-6"
                            v-click-outside="closeLanguageDropdown"
                        >
                            <button
                                type="button"
                                @click="toggleLanguageDropdown"
                                class="w-full max-w-xs inline-flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 rounded-md border border-gray-300 transition-colors"
                                :class="{
                                    'border-red-500': form.errors.language,
                                }"
                            >
                                <div class="flex items-center">
                                    <span class="text-lg mr-2">{{
                                        selectedLanguage.flag
                                    }}</span>
                                    <span class="truncate">{{
                                        selectedLanguage.name
                                    }}</span>
                                </div>
                                <div class="ml-2 flex flex-col">
                                    <svg
                                        class="h-3 w-3 -mb-1"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <svg
                                        class="h-3 w-3"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </button>

                            <!-- Language Dropdown -->
                            <div
                                v-if="isLanguageDropdownOpen"
                                class="absolute z-50 mt-1 w-full max-w-xs rounded-lg bg-white shadow-lg border border-gray-200"
                            >
                                <div class="py-1">
                                    <div
                                        v-for="language in availableLanguages"
                                        :key="language.code"
                                        @click="selectLanguage(language)"
                                        class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                        :class="{
                                            'bg-gray-100 text-gray-900':
                                                selectedLanguage.code ===
                                                language.code,
                                        }"
                                    >
                                        <div class="flex items-center">
                                            <span class="text-lg mr-2">{{
                                                language.flag
                                            }}</span>
                                            <span class="truncate">{{
                                                language.name
                                            }}</span>
                                        </div>
                                        <svg
                                            v-if="
                                                selectedLanguage.code ===
                                                language.code
                                            "
                                            class="h-4 w-4 text-gray-900"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Decimal Separator -->
                        <div>
                            <label
                                class="block mb-2 text-sm font-medium text-gray-700"
                            >
                                Decimaal scheidingsteken
                            </label>
                            <div
                                class="flex rounded-md overflow-hidden border border-gray-300 w-fit"
                            >
                                <button
                                    type="button"
                                    @click="form.decimal_seperator = 'comma'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.decimal_seperator === 'comma'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Komma (,)
                                </button>
                                <button
                                    type="button"
                                    @click="form.decimal_seperator = 'point'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.decimal_seperator === 'point'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Punt (.)
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Voorbeeld:
                                {{
                                    form.number_format === "comma"
                                        ? "1" +
                                          "," +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : form.number_format === "point"
                                        ? "1" +
                                          "." +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : form.number_format === "space"
                                        ? "1" +
                                          " " +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : "1234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                }}56
                            </p>
                        </div>

                        <!-- Thousand Separator -->
                        <div class="mt-4">
                            <label
                                class="block mb-2 text-sm font-medium text-gray-700"
                            >
                                Duizend scheidingsteken
                            </label>
                            <div
                                class="flex rounded-md overflow-hidden border border-gray-300 w-fit"
                            >
                                <button
                                    type="button"
                                    @click="form.number_format = 'comma'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.number_format === 'comma'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Komma (,)
                                </button>
                                <button
                                    type="button"
                                    @click="form.number_format = 'point'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.number_format === 'point'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Punt (.)
                                </button>
                                <button
                                    type="button"
                                    @click="form.number_format = 'space'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.number_format === 'space'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Spatie ( )
                                </button>
                                <button
                                    type="button"
                                    @click="form.number_format = 'none'"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium focus:outline-none min-w-[80px]',
                                        form.number_format === 'none'
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100',
                                    ]"
                                >
                                    Geen
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Voorbeeld:
                                {{
                                    form.number_format === "comma"
                                        ? "1" +
                                          "," +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : form.number_format === "point"
                                        ? "1" +
                                          "." +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : form.number_format === "space"
                                        ? "1" +
                                          " " +
                                          "234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                        : "1234" +
                                          (form.decimal_seperator === "comma"
                                              ? ","
                                              : ".")
                                }}56
                            </p>
                        </div>
                    </div>

                    <!-- Errors -->
                    <div
                        v-if="hasErrors"
                        class="flex flex-col gap-2 text-sm text-red-600 border-t border-gray-200 pt-6"
                    >
                        <div
                            class="bg-red-50 border border-red-200 rounded-md p-3"
                        >
                            <h3 class="font-medium text-red-800 mb-2">
                                Er zijn fouten opgetreden:
                            </h3>
                            <ul class="list-disc list-inside space-y-1">
                                <li
                                    v-for="(error, key) in form.errors"
                                    :key="key"
                                    class="text-red-700"
                                >
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div
                        v-if="showSuccessMessage"
                        class="bg-green-50 border border-green-200 rounded-md p-3 text-sm text-green-700"
                    >
                        Instellingen succesvol opgeslagen!
                    </div>

                    <!-- Save Button -->
                    <div class="pt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Opslaan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { changeLocale } from "@/lang";

export default {
    components: {
        Head,
        AuthenticatedLayout,
    },
    props: {
        mustVerifyEmail: Boolean,
        status: String,
        settings: Object, // Added settings prop
    },

    directives: {
        clickOutside: {
            beforeMount(el, binding) {
                el.clickOutsideEvent = (event) => {
                    if (!(el === event.target || el.contains(event.target))) {
                        binding.value(event);
                    }
                };
                document.addEventListener("click", el.clickOutsideEvent);
            },
            unmounted(el) {
                document.removeEventListener("click", el.clickOutsideEvent);
            },
        },
    },

    data() {
        return {
            isLanguageDropdownOpen: false,
            availableLanguages: [
                { code: "nl", name: "Nederlands", flag: "🇳🇱" },
                { code: "fr", name: "Frans", flag: "🇫🇷" },
                { code: "en", name: "Engels", flag: "🇬🇧" },
            ],
            selectedLanguage: {},

            dateFormats: [
                { value: "d-m-Y", example: "31-12-2023" },
                { value: "Y-m-d", example: "2023-12-31" },
                { value: "d/m/Y", example: "31/12/2023" },
            ],

            form: useForm({
                // Get lang from user settings

                language: this.settings.locale || "nl",
                decimal_seperator: this.settings.decimal_seperator || "comma",
                number_format: this.settings.number_format || "comma",
                date_format: "d-m-Y",
                time_format: "24",
                processing: false,
                errors: {},
            }),

            showSuccessMessage: false,
        };
    },

    computed: {
        hasErrors() {
            return Object.keys(this.form.errors).length > 0;
        },
    },

    mounted() {
        this.selectedLanguage = this.availableLanguages.find(
            (lang) => lang.code === this.form.language
        );

        console.log("User settings:", this.settings);
        console.log("Selected language:", this.selectedLanguage);
    },

    methods: {
        toggleLanguageDropdown() {
            console.log(this.$page);
            this.isLanguageDropdownOpen = !this.isLanguageDropdownOpen;
        },

        closeLanguageDropdown() {
            this.isLanguageDropdownOpen = false;
        },

        selectLanguage(language) {
            this.selectedLanguage = language;
            this.form.language = language.code;
            this.closeLanguageDropdown();
        },

        saveSettings() {
            this.form.processing = true;
            this.form.errors = {};
            this.showSuccessMessage = false;

            // Simuleer een API-aanroep
            setTimeout(() => {
                this.form.patch(route("profile.update"), {
                    onSuccess: () => {
                        this.showSuccessMessage = true;
                        this.form.processing = false;
                    },
                    onError: (errors) => {
                        this.form.errors = errors;
                        this.form.processing = false;
                    },
                });
            }, 1000);
        },
    },
};
</script>
