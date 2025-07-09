<template>
    <Head :title="user.firstname + ' ' + user.name" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <template #title>Sources</template>

        <div
            class="flex md:flex-row gap-4 md:gap-0 flex-col items-start justify-between"
        >
            <div class="flex flex-col">
                <div
                    v-if="user.sources && user.sources.length"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <div
                        v-for="source in user.sources"
                        :key="source.id"
                        class="px-2 py-[1px] rounded-md text-xs font-medium border transition-all duration-200 flex items-center gap-1"
                        :style="{
                            backgroundColor: source.hex_color,
                            borderColor: source.hex_color,
                            color: '#1e293b',
                        }"
                    >
                        <span>{{ source.name }}</span>
                    </div>
                </div>
                <h1 class="text-black font-bold text-4xl">
                    {{ user.firstname }} {{ user.name }}
                </h1>
            </div>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i class="fas fa-edit mr-2"></i> Pas aan
                </PrimaryButton>
                <PrimaryButton v-if="editUser" @click="saveUser">
                    <i class="fas fa-check mr-2"></i>Sla op
                </PrimaryButton>
                <SecondaryButton @click="deleteUser">
                    <i class="fas fa-trash-alt mr-2"></i>Verwijder
                </SecondaryButton>
            </div>
        </div>

        <div>
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="firstname" value="Voornaam*" />
                    <TextInput
                        id="firstname"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.firstname"
                        :disabled="!editUser"
                        placeholder="Voornaam"
                    />
                    <InputError class="mt-2" :message="form.errors.firstname" />
                </div>
                <div class="w-full mt-2">
                    <InputLabel for="name" value="Naam*" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        :disabled="!editUser"
                        placeholder="Naam"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="email" value="Email*" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        :disabled="!editUser"
                        placeholder="Color"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
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
        user: Object,
        sources: Array,
    },
    data() {
        return {
            editUser: false,
            form: useForm({
                name: this.user.name,
                firstname: this.user.firstname,
                email: this.user.email,
                source_ids: this.user.sources.map((source) => source.id),
            }),
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Gebruikers", href: "/users" },
                {
                    title: this.user.name,
                    href: "/users/" + this.user.guid,
                },
            ],
        };
    },
    methods: {
        deleteUser() {
            this.form.delete(
                route("user.delete", {
                    guid: this.user.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        saveUser() {
            this.form.patch(
                route("user.update", {
                    guid: this.user.guid,
                }),
                {
                    onSuccess: () => location.reload(),
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleSource(id) {
            const index = this.form.source_ids.indexOf(id);
            if (index > -1) {
                this.form.source_ids.splice(index, 1);
            } else {
                this.form.source_ids.push(id);
            }
        },
        isSelected(id) {
            return this.form.source_ids.includes(id);
        },
        toggleEdit() {
            this.editUser = !this.editUser;
        },
    },
    computed: {},
};
</script>
