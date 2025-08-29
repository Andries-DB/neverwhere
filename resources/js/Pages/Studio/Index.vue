<template>
    <Head title="Rapporten" />
    <AuthenticatedLayout>
        <div>
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
                        selectedSource ? selectedSource.name : "Selecteer bron"
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
                                <span class="truncate">{{ source.name }}</span>
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
                            v-if="!this.sources || this.sources.length === 0"
                            class="px-3 py-2 text-sm text-gray-500"
                        >
                            Geen sources beschikbaar
                        </div>
                    </div>
                </div>
            </div>
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
                            @click.prevent="addCombination()"
                            v-if="index + 1 === form.suggestions.length"
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
                        <button
                            @click.prevent="removeCombination(suggestion.id)"
                            class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                            :disabled="form.suggestions.length === 1"
                            :class="{
                                'opacity-40 cursor-not-allowed hover:text-gray-400 hover:bg-transparent':
                                    form.suggestions.length === 1,
                            }"
                        >
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
                        </button>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <PrimaryButton
                        type="submit"
                        :disabled="this.form.processing"
                        >Sla op</PrimaryButton
                    >
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
            }),
        };
    },
    methods: {
        addCombination() {
            this.form.suggestions.push({
                question: "",
                id: this.form.suggestions.length + 1,
            });
        },
        removeCombination(id) {
            this.form.suggestions = this.form.suggestions.filter(
                (suggestion) => suggestion.id !== id
            );

            this.form.suggestions.forEach((suggestion, index) => {
                suggestion.id = index + 1;
            });
        },
        selectSource(source) {
            this.selectedSource = source;
            this.form.source_id = source.id;
            this.form.model = source.model;

            this.form.suggestions = [];

            if (source.suggestions.length === 0) {
                this.form.suggestions.push({
                    question: "",
                    id: 1,
                });
            } else {
                source.suggestions.forEach((suggestion, index) => {
                    this.form.suggestions.push({
                        question: suggestion.question,
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
    },
};
</script>
