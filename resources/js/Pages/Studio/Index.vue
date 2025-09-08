<template>
    <Head title="Rapporten" />
    <AuthenticatedLayout>
        <div class="flex justify-between items-end">
            <div class="w-[90%]">
                <label class="block mb-2 text-sm font-medium text-gray-700"
                    >Dashboard</label
                >
                <div class="relative" v-click-outside="closeSourceDropdown">
                    <button
                        type="button"
                        @click="toggleSourceDropdown"
                        class="w-full inline-flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 rounded-md border border-gray-300 transition-colors"
                        :class="{ 'border-red-500': form.errors.source_id }"
                    >
                        <span class="truncate">{{
                            selectedSource
                                ? selectedSource.name
                                : "Selecteer bron"
                        }}</span>
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

                    <!-- Dashboard Dropdown -->
                    <div
                        v-if="isSourceOpen"
                        class="absolute z-50 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200"
                    >
                        <div class="py-1 max-h-60 overflow-auto">
                            <div
                                v-for="source in this.sources"
                                :key="source.id"
                                @click="selectSource(source)"
                                class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{
                                    'bg-gray-100 text-gray-900':
                                        selectedSource &&
                                        selectedSource.id === source.id,
                                }"
                            >
                                <div class="flex items-center">
                                    <span class="truncate">{{
                                        source.name
                                    }}</span>
                                </div>
                                <svg
                                    v-if="
                                        selectedSource &&
                                        selectedSource.id === source.id
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

                            <!-- Empty state -->
                            <div
                                v-if="
                                    !this.sources || this.sources.length === 0
                                "
                                class="px-3 py-2 text-sm text-gray-500"
                            >
                                Geen sources beschikbaar
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <PrimaryButton type="submit" :disabled="this.form.processing"
                >Sla op</PrimaryButton
            >
        </div>

        <div v-if="selectedSource" class="w-full">
            <form @submit.prevent="updateSource">
                <div class="w-full mt-4">
                    <InputLabel for="model" value="Model*" />
                    <textarea
                        class="w-full h-[500px] resize-vertical overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm py-2"
                        placeholder="Typ hier je tekst..."
                        v-model="this.form.model"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.model" />
                </div>

                <InputLabel for="model" value="Suggestievragen" class="mt-4" />

                <div
                    v-for="(suggestion, index) in form.suggestions"
                    :key="suggestion.id"
                    class="grid grid-cols-12 gap-4 items-center py-2"
                >
                    <div class="col-span-11">
                        <TextInput
                            v-model="suggestion.question"
                            placeholder="Vraag"
                            class="w-full"
                        />
                    </div>

                    <div class="col-span-1 flex justify-end">
                        <button
                            @click.prevent="
                                saveCombination(suggestion, 'suggestions')
                            "
                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                        >
                            <template
                                v-if="
                                    loadingItems[
                                        'save-suggestions-' + suggestion.id
                                    ]
                                "
                            >
                                <svg
                                    class="animate-spin w-5 h-5 text-blue-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                            </template>
                            <template v-else>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.5 3.75h-9A2.25 2.25 0 005.25 6v12A2.25 2.25 0 007.5 20.25h9a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0016.5 3.75z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 3.75v4.5h6v-4.5M9 12h6"
                                    />
                                </svg>
                            </template>
                        </button>
                        <button
                            @click.prevent="
                                removeCombination(suggestion.id, 'suggestions')
                            "
                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                        >
                            <template
                                v-if="
                                    loadingItems[
                                        'delete-suggestions-' + suggestion.id
                                    ]
                                "
                            >
                                <!-- Spinner -->
                                <svg
                                    class="animate-spin w-5 h-5 text-red-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                            </template>
                            <template v-else>
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div class="w-full flex justify-end">
                    <button
                        @click.prevent="addCombination('suggestions')"
                        class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                    >
                        <svg
                            class="w-5 h-5 rotate-45"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <InputLabel for="model" value="Knowledge vragen" class="mt-4" />

                <div
                    v-for="(knowledge, index) in form.knowledge"
                    :key="knowledge.id"
                    class="grid grid-cols-12 gap-2 items-center py-2"
                >
                    <div class="col-span-1">
                        <TextInput
                            v-model="knowledge.key"
                            placeholder="Key"
                            class="w-full"
                        />
                    </div>

                    <div class="col-span-4">
                        <TextInput
                            v-model="knowledge.description"
                            placeholder="Omschrijving"
                            class="w-full"
                        />
                    </div>

                    <div class="col-span-6">
                        <TextInput
                            v-model="knowledge.query"
                            placeholder="Query"
                            class="w-full"
                        />
                    </div>

                    <div class="col-span-1 flex justify-end">
                        <button
                            @click.prevent="
                                saveCombination(knowledge, 'knowledge')
                            "
                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                        >
                            <template
                                v-if="
                                    loadingItems[
                                        'save-knowledge-' + knowledge.id
                                    ]
                                "
                            >
                                <svg
                                    class="animate-spin w-5 h-5 text-blue-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                            </template>
                            <template v-else>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16.5 3.75h-9A2.25 2.25 0 005.25 6v12A2.25 2.25 0 007.5 20.25h9a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0016.5 3.75z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 3.75v4.5h6v-4.5M9 12h6"
                                    />
                                </svg>
                            </template>
                        </button>
                        <button
                            @click.prevent="
                                removeCombination(knowledge.id, 'knowledge')
                            "
                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                        >
                            <template
                                v-if="
                                    loadingItems[
                                        'delete-knowledge-' + knowledge.id
                                    ]
                                "
                            >
                                <!-- Spinner -->
                                <svg
                                    class="animate-spin w-5 h-5 text-red-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                            </template>
                            <template v-else>
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                <div class="w-full flex justify-end">
                    <button
                        @click.prevent="addCombination('knowledge')"
                        class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                    >
                        <svg
                            class="w-5 h-5 rotate-45"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <Snackbar ref="snackbar" />
    </AuthenticatedLayout>
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Snackbar from "@/Components/Snackbar.vue";
import TextInput from "@/Components/TextInput.vue";
export default {
    name: "ReportsIndex",
    components: {
        Head,
        AuthenticatedLayout,
        InputLabel,
        InputError,
        PrimaryButton,
        Snackbar,
        TextInput,
    },
    props: {
        sources: Array,
    },
    data() {
        return {
            selectedSource: null,
            isSourceOpen: false,
            form: useForm({
                source_id: "",
                model: "",
                suggestions: [],
                knowledge: [],
            }),
            loadingItems: {}, // { "suggestion-1": true }
        };
    },
    methods: {
        addCombination(type) {
            if (type === "suggestions") {
                this.form.suggestions.push({
                    question: "",
                    db_id: null,
                    id: this.form.suggestions.length + 1,
                });
            } else if (type === "knowledge") {
                this.form.knowledge.push({
                    key: "",
                    description: "",
                    query: "",
                    db_id: null,
                    id: this.form.knowledge.length + 1,
                });
            }
        },
        removeCombination(id, type) {
            const key = `delete-${type}-${id}`;
            this.loadingItems = { ...this.loadingItems, [key]: true }; // loading aan

            const collection =
                type === "suggestions"
                    ? this.form.suggestions
                    : this.form.knowledge;
            const item = collection.find((s) => s.id === id);

            if (!item) {
                this.loadingItems = { ...this.loadingItems, [key]: false };
                return;
            }

            const removeLocally = () => {
                const updated = collection.filter((s) => s.id !== id);
                updated.forEach((s, index) => (s.id = index + 1));

                if (type === "suggestions") this.form.suggestions = updated;
                else this.form.knowledge = updated;

                this.$refs.snackbar.show(
                    type === "suggestions"
                        ? "De vraag is succesvol verwijderd"
                        : "De knowledge is succesvol verwijderd",
                    "success"
                );

                this.loadingItems = { ...this.loadingItems, [key]: false };
            };

            if (item.db_id === null) {
                removeLocally();
            } else {
                const routeName =
                    type === "suggestions"
                        ? "studio.delete.suggestion"
                        : "studio.delete.knowledge";

                axios
                    .delete(route(routeName, item.db_id), {
                        data: { source_id: this.form.source_id },
                    })
                    .then(removeLocally)
                    .catch((error) => {
                        console.error(error);
                        this.$refs.snackbar.show(
                            "Er is een fout opgetreden bij het verwijderen",
                            "error"
                        );
                        this.loadingItems = {
                            ...this.loadingItems,
                            [key]: false,
                        };
                    });
            }
        },
        selectSource(source) {
            this.selectedSource = source;
            this.form.source_id = source.id;
            this.form.model = source.model;

            this.form.suggestions = [];
            this.form.knowledge = [];

            if (source.suggestions.length === 0) {
                this.form.suggestions.push({
                    question: "",
                    db_id: null,
                    id: 1,
                });
            } else {
                source.suggestions.forEach((suggestion, index) => {
                    this.form.suggestions.push({
                        question: suggestion.question,
                        db_id: suggestion.id,
                        id: index + 1,
                    });
                });
            }

            if (source.knowledge.length === 0) {
                this.form.knowledge.push({
                    key: "",
                    description: "",
                    query: "",
                    db_id: null,
                    id: 1,
                });
            } else {
                source.knowledge.forEach((knowledge, index) => {
                    this.form.knowledge.push({
                        key: knowledge.key,
                        description: knowledge.description,
                        query: knowledge.query,
                        db_id: knowledge.id,
                        id: index + 1,
                    });
                });
            }

            this.closeSourceDropdown();
        },
        toggleSourceDropdown() {
            this.isSourceOpen = !this.isSourceOpen;
        },
        closeSourceDropdown() {
            this.isSourceOpen = false;
        },
        updateSource() {
            this.form.patch(route("studio.patch"), {
                onSuccess: () => {
                    this.$refs.snackbar.show(
                        "De bron is succesvol opgeslagen",
                        "success"
                    );
                },
                onError: (errors) => {
                    this.$refs.snackbar.show(
                        "Er is een fout opgetreden bij het opslaan van de bron: " +
                            Object.values(errors).join(" "),
                        "error"
                    );
                },
            });
        },
        saveCombination(item, type) {
            const key = `save-${type}-${item.id}`;
            this.loadingItems = { ...this.loadingItems, [key]: true }; // loading aan

            const routeName =
                type === "suggestions"
                    ? "studio.patch.suggestion"
                    : "studio.patch.knowledge";

            const payload =
                type === "suggestions"
                    ? { suggestion: item, source_id: this.form.source_id }
                    : { knowledge: item, source_id: this.form.source_id };

            axios
                .patch(route(routeName), payload)
                .then((response) => {
                    this.$refs.snackbar.show(
                        type === "suggestions"
                            ? "De vraag is succesvol opgeslagen"
                            : "De knowledge is succesvol opgeslagen",
                        "success"
                    );

                    if (response.data?.suggestion || response.data?.knowledge) {
                        // Update db_id in het lokale item
                        const updated =
                            response.data.suggestion || response.data.knowledge;
                        item.db_id = updated.id ?? item.db_id;
                    }

                    this.loadingItems = { ...this.loadingItems, [key]: false }; // loading uit
                })
                .catch((error) => {
                    if (error.response?.data?.errors) {
                        this.$refs.snackbar.show(
                            Object.values(error.response.data.errors)
                                .flat()
                                .join(", "),
                            "error"
                        );
                    } else {
                        this.$refs.snackbar.show(
                            "Er is een fout opgetreden bij het opslaan",
                            "error"
                        );
                    }

                    this.loadingItems = { ...this.loadingItems, [key]: false }; // loading uit
                });
        },
    },
};
</script>
