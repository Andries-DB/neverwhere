<template>
    <Head :title="source.name" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <template #title>Sources</template>

        <div
            class="flex md:flex-row gap-2 md:gap-0 flex-col md:items-center items-start justify-between"
        >
            <h1 class="text-black font-bold text-4xl">{{ source.name }}</h1>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i class="fas fa-edit mr-2"></i> Pas aan
                </PrimaryButton>
                <PrimaryButton v-if="editSource" @click="saveSource">
                    <i class="fas fa-check mr-2"></i>Sla op
                </PrimaryButton>
                <SecondaryButton @click="deleteSource">
                    <i class="fas fa-trash-alt mr-2"></i>Verwijder
                </SecondaryButton>
            </div>
        </div>

        <div>
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="name" value="Naam*" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        :disabled="!editSource"
                        placeholder="Naam"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="webhook" value="Webhook*" />
                    <TextInput
                        id="webhook"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.webhook"
                        :disabled="!editSource"
                        placeholder="Webhook"
                    />
                    <InputError class="mt-2" :message="form.errors.webhook" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="model" value="Model*" />
                    <textarea
                        class="w-full h-[500px] resize-vertical overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                        placeholder="Typ hier je tekst..."
                        v-model="form.model"
                        :disabled="!editSource"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.model" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        SecondaryButton,
        TextInput,
        InputError,
        InputLabel,
    },
    props: {
        source: Object,
        company: Object,
    },
    data() {
        return {
            form: useForm({
                name: this.source.name,
                hex_color: this.source.hex_color,
                webhook: this.source.webhook,
                model: this.source.model,
            }),
            editSource: false,
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Bedrijven", href: "/companies" },
                {
                    title: this.company.company,
                    href: "/companies/" + this.company.guid,
                },
                {
                    title: "Bronnen",
                    href: "/companies/" + this.company.guid,
                },
                {
                    title: this.source.name,
                    href:
                        "/companies/" +
                        this.company.guid +
                        "/sources/" +
                        this.source.id,
                },
            ],
        };
    },
    methods: {
        saveSource() {
            this.form.patch(
                route("company.source.update", {
                    guid: this.company.guid,
                    source_id: this.source.id,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        deleteSource() {
            this.form.delete(
                route("company.source.delete", {
                    guid: this.company.guid,
                    source_id: this.source.id,
                }),
                {
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleEdit() {
            this.editSource = !this.editSource;
        },
    },
    computed: {},
};
</script>
