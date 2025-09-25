<template>
    <Head title="Studio" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <div
                class="flex justify-between flex-col md:flex-row gap-2 items-end w-full"
            >
                <div class="md:w-[80%] w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        {{ $t("labels.dashboard") }}</label
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
                                    : $t("studio.select")
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
                                        !this.sources ||
                                        this.sources.length === 0
                                    "
                                    class="px-3 py-2 text-sm text-gray-500"
                                >
                                    {{ $t("labels.noresult") }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-1">
                    <PrimaryButton
                        @click="updateSource"
                        :disabled="this.form.processing"
                        v-if="editModal"
                    >
                        <i class="fas fa-check mr-2"></i
                        >{{ $t("buttons.save") }}
                    </PrimaryButton>

                    <PrimaryButton @click="toggleEdit">
                        <i
                            :class="[
                                editModal ? 'fas fa-times' : 'fas fa-edit',
                                'mr-2',
                            ]"
                        ></i>
                        {{
                            editModal
                                ? $t("buttons.cancel")
                                : $t("buttons.edit")
                        }}
                    </PrimaryButton>
                </div>
            </div>

            <div v-if="selectedSource" class="w-full">
                <form @submit.prevent="updateSource" class="w-full">
                    <InputLabel for="model" :value="$t('labels.model') + '*'" />
                    <textarea
                        class="w-full h-96 resize-vertical p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm py-2"
                        :placeholder="$t('labels.textarea') + '...'"
                        v-model="this.form.model"
                        :disabled="!editModal"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.model" />
                </form>

                <div class="flex justify-between mt-4">
                    <InputLabel
                        for="model"
                        :value="$t('studio.suggestion_questions')"
                    />

                    <button
                        @click.prevent="toggleOpenSugModal('add')"
                        class="w-10 h-10 flex items-center justify-center text-white bg-blue-600 hover:text-white hover:bg-blue-400 rounded-md transition-colors"
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

                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                {{ $t("labels.question") }}
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3"
                                title="Aanpassen"
                            ></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-for="s in this.suggestions"
                            :key="s.id"
                        >
                            <td
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap w-full"
                            >
                                {{ s.question }}
                            </td>

                            <td class="px-6 py-4">
                                <button
                                    @click.prevent="
                                        toggleOpenSugModal('edit', s)
                                    "
                                    class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                                >
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
                                </button>
                            </td>
                        </tr>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-if="this.suggestions.length < 1"
                        >
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center"
                                colspan="6"
                            >
                                <div class="py-8">
                                    <svg
                                        class="mx-auto h-12 w-12 text-gray-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-5m-8 0H4"
                                        />
                                    </svg>
                                    <h3
                                        class="mt-2 text-sm font-medium text-gray-900"
                                    >
                                        {{ $t("labels.noresults") }}
                                    </h3>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-between mt-6">
                    <InputLabel
                        for="model"
                        value="Knowledge vragen"
                        class="mt-4"
                    />

                    <button
                        @click.prevent="toggleOpenModal('add')"
                        class="w-10 h-10 flex items-center justify-center text-white bg-blue-600 hover:text-white hover:bg-blue-400 rounded-md transition-colors"
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
                <div class="overflow-x-auto">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-sm text-left rtl:text-right">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0"
                            >
                                <tr>
                                    <th class="px-6 py-3">
                                        {{ $t("labels.key") }}
                                    </th>
                                    <th class="px-6 py-3">
                                        {{ $t("labels.description") }}
                                    </th>
                                    <th class="px-6 py-3">
                                        {{ $t("labels.query") }}
                                    </th>
                                    <th
                                        class="px-6 py-3"
                                        title="Aanpassen"
                                    ></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="bg-white border-b border-gray-200"
                                    v-for="k in this.knowledge"
                                    :key="k.id"
                                >
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                    >
                                        {{ k.key }}
                                    </td>
                                    <td class="px-6 py-4 truncate max-w-xs">
                                        {{ k.description }}
                                    </td>
                                    <td class="px-6 py-4 truncate max-w-xs">
                                        {{ k.query }}
                                    </td>
                                    <td class="px-6 py-4 flex justify-end">
                                        <button
                                            @click.prevent="
                                                toggleOpenModal('edit', k)
                                            "
                                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-md transition-colors"
                                        >
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
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="this.knowledge.length < 1">
                                    <td
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center"
                                        colspan="6"
                                    >
                                        <div class="py-8">
                                            <!-- empty state SVG en tekst -->
                                            <h3
                                                class="mt-2 text-sm font-medium text-gray-900"
                                            >
                                                {{ $t("labels.noresults") }}
                                            </h3>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <ModalKnowledge
        :show="showModal"
        :close="toggleOpenModal"
        :type="selectedType"
        :mode="selectedString"
        :source_id="this.form.source_id"
        @submitted="handleSubmitted"
        @deleted="handleDeleted"
        maxWidth="7xl"
    />

    <ModalSuggestion
        :show="showSugModal"
        :close="toggleOpenSugModal"
        :type="selectedSugType"
        :mode="selectedSugString"
        :source_id="this.form.source_id"
        @submitted="handleSugSubmitted"
        @deleted="handleSugDeleted"
        maxWidth="3xl"
    />
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Snackbar from "@/Components/Snackbar.vue";
import TextInput from "@/Components/TextInput.vue";
import ModalKnowledge from "@/Components/Modals/ModalKnowledge.vue";
import ModalSuggestion from "@/Components/Modals/ModalSuggestion.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
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
        ModalKnowledge,
        ModalSuggestion,
        SecondaryButton,
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
            }),
            knowledge: [],
            suggestions: [],
            selectedString: "",
            selectedType: null,
            showModal: false,
            selectedSugString: "",
            selectedSugType: null,
            showSugModal: false,
            editModal: false,
        };
    },
    methods: {
        updateSource() {
            this.form.patch(route("studio.patch", this.form.source_id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.editModal = false;
                },
                onError: () => {},
            });
        },
        handleSubmitted(data) {
            const index = this.knowledge.findIndex((k) => k.id === data.id);
            if (index !== -1) {
                this.knowledge[index] = data;
            } else {
                this.knowledge.push(data);
            }
        },
        handleDeleted(id) {
            const index = this.knowledge.findIndex((k) => k.id === id);
            if (index !== -1) {
                this.knowledge.splice(index, 1);
            }
        },
        toggleOpenModal(type = null, item = null) {
            this.showModal = !this.showModal;
            this.selectedString = type;
            this.selectedType = item;
        },
        handleSugSubmitted(data) {
            const index = this.suggestions.findIndex((s) => s.id === data.id);
            if (index !== -1) {
                this.suggestions[index] = data;
            } else {
                this.suggestions.push(data);
            }
        },
        handleSugDeleted(id) {
            const index = this.suggestions.findIndex((k) => k.id === id);
            if (index !== -1) {
                this.suggestions.splice(index, 1);
            }
        },
        toggleOpenSugModal(type = null, item = null) {
            this.showSugModal = !this.showSugModal;
            this.selectedSugString = type;
            this.selectedSugType = item;
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

            this.knowledge = source.knowledge;
            this.suggestions = source.suggestions;

            this.closeSourceDropdown();
        },
        toggleSourceDropdown() {
            this.isSourceOpen = !this.isSourceOpen;
        },
        closeSourceDropdown() {
            this.isSourceOpen = false;
        },
        toggleEdit() {
            this.editModal = !this.editModal;
        },
    },
};
</script>
