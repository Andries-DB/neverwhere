<template>
    <Head :title="company.company" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex md:flex-row gap-4 md:gap-0 flex-col md:items-center items-start justify-between"
        >
            <h1 class="text-black font-bold text-4xl">{{ company.company }}</h1>

            <div class="flex gap-2">
                <PrimaryButton
                    @click="toggleEdit"
                    v-if="show_sort === 'settings'"
                >
                    <i class="fas fa-edit mr-2"></i>Pas aan
                </PrimaryButton>
                <PrimaryButton
                    v-if="editCompany && show_sort === 'settings'"
                    @click="saveCompany"
                >
                    <i class="fas fa-check mr-2"></i>Sla op
                </PrimaryButton>
                <SecondaryButton
                    v-if="show_sort === 'settings'"
                    @click="deleteCompany"
                >
                    <i class="fas fa-trash-alt mr-2"></i>Verwijder
                </SecondaryButton>
                <PrimaryButton
                    @click="toggleAddUser"
                    v-if="show_sort === 'users'"
                >
                    + Voeg nieuwe gebruiker toe
                </PrimaryButton>
                <PrimaryButton
                    @click="toggleAddSource"
                    v-if="show_sort === 'sources'"
                >
                    + Voeg nieuwe bron toe
                </PrimaryButton>
            </div>
        </div>

        <div
            class="flex-col flex-wrap gap-4 mt-2 bg-transparant rounded-md pt-5 pl-2 text-black"
        >
            <div class="flex gap-2">
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'settings'
                            ? 'bg-gray-200'
                            : 'bg-gray-300',
                    ]"
                    @click="changeSort('settings')"
                >
                    Instellingen
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'sources' ? 'bg-gray-200' : 'bg-gray-300',
                    ]"
                    @click="changeSort('sources')"
                >
                    Bronnen
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'users' ? 'bg-gray-200' : 'bg-gray-300',
                    ]"
                    @click="changeSort('users')"
                >
                    Gebruikers
                </button>
            </div>
        </div>

        <div v-if="this.show_sort === 'settings'">
            <!-- Default settings -->
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="name" value="Naam*" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.company"
                        :disabled="!editCompany"
                        placeholder="Naam"
                    />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
            </div>
        </div>

        <div v-if="this.show_sort === 'sources'" class="mt-6">
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Naam</th>
                        <th scope="col" class="px-6 py-3">Webhook</th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Aanpassen</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-for="source in this.company.sources"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 flex items-center font-medium text-gray-900 whitespace-nowrap"
                        >
                            <span
                                class="inline-block w-4 h-4 rounded mr-2"
                                :style="{ backgroundColor: source.hex_color }"
                            ></span>
                            {{ source.name }}
                        </th>
                        <td class="px-6 py-4 truncate max-w-[300px]">
                            {{ source.webhook }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <a
                                :href="
                                    route('company.source.read', {
                                        guid: this.company.guid,
                                        source_id: source.id,
                                    })
                                "
                                class="font-medium text-blue-600 hover:underline"
                                >Pas aan</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-if="this.company.sources.length < 1"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            colspan="3"
                        >
                            Geen resutaten gevonden
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="this.show_sort === 'users'">
            <div class="overflow-x-auto mt-6">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Naam</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Bronnen</th>

                            <th scope="col" class="px-6 py-3">
                                <span class="sr-only">Aanpassen</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-for="user in company.users"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            >
                                {{ user.name }}
                            </th>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="source in user.sources"
                                        :key="source.id"
                                        class="px-2 py-1 rounded text-black text-sm text-center font-medium min-w-[80px]"
                                        :style="{
                                            backgroundColor:
                                                source.hex_color || '#999',
                                        }"
                                    >
                                        {{ source.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    :href="
                                        route('company.user.read', {
                                            guid: company.guid,
                                            user_guid: user.guid,
                                        })
                                    "
                                    class="font-medium text-blue-600 hover:underline"
                                    >Pas aan</a
                                >
                            </td>
                        </tr>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-if="this.company.users.length < 1"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                colspan="4"
                            >
                                Geen resultaten gevonden
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>

    <Create
        :close="toggleAddUser"
        :show="addUser"
        :company="this.company"
        :sources="this.company.sources"
    />
    <CreateSources
        :close="toggleAddSource"
        :show="addSource"
        :company="this.company"
    />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import Create from "./Users/Create.vue";
import CreateSources from "./Sources/Create.vue";

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
        Create,
        CreateSources,
    },
    props: {
        company: Object,
        // sources: Array,
    },
    data() {
        return {
            form: useForm({
                company: this.company.company,
            }),
            show_sort: "settings",
            editCompany: false,
            addSource: false,
            addUser: false,
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Bedrijven", href: "/companies" },
                {
                    title: this.company.company,
                    href: "/companies/" + this.company.guid,
                },
            ],
        };
    },
    methods: {
        changeSort(sort) {
            this.show_sort = sort;

            const data = {
                sort,
                companyGuid: this.company.guid,
                timestamp: new Date().getTime(),
            };

            localStorage.setItem("selected_sort", JSON.stringify(data));
        },
        saveCompany() {
            this.form.patch(
                route("company.update", {
                    guid: this.company.guid,
                }),
                {
                    // onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        deleteCompany() {
            this.form.delete(
                route("company.delete", {
                    id: this.company.guid,
                }),
                {
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleEdit() {
            this.editCompany = !this.editCompany;
        },
        toggleAddUser() {
            this.addUser = !this.addUser;
        },
        toggleAddSource() {
            this.addSource = !this.addSource;
        },
    },
    computed: {},
    mounted() {
        const stored = localStorage.getItem("selected_sort");

        if (stored) {
            const data = JSON.parse(stored);
            const now = new Date().getTime();
            const oneHour = 60 * 60 * 1000;

            if (
                now - data.timestamp < oneHour &&
                data.companyGuid === this.company.guid
            ) {
                this.show_sort = data.sort;
            } else {
                this.show_sort = "settings";
            }
        } else {
            this.show_sort = "settings";
        }
    },
};
</script>
