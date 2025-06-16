<template>
    <Head :title="source.name" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <template #title>Sources</template>

        <div class="flex items-center justify-between">
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
                    <InputLabel for="color" value="Kleur*" />
                    <TextInput
                        id="color"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.hex_color"
                        :disabled="!editSource"
                        placeholder="Kleur"
                    />
                    <InputError class="mt-2" :message="form.errors.hex_color" />
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
    },
    data() {
        return {
            form: useForm({
                name: this.source.name,
                hex_color: this.source.hex_color,
                webhook: this.source.webhook,
            }),
            editSource: false,
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Sources", href: "/sources" },
                { title: this.source.name, href: "/sources/" + this.source.id },
            ],
        };
    },
    methods: {
        saveSource() {
            this.form.patch(
                route("source.update", {
                    id: this.source.id,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        deleteSource() {
            this.form.delete(
                route("source.delete", {
                    id: this.source.id,
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
