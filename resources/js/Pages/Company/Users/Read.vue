<template>
    <Head :title="user.firstname + ' ' + user.name" />
    <AuthenticatedLayout>
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
                        placeholder="Email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <!-- User Groups Section -->
                <div class="flex flex-col gap-2 mt-4">
                    <label class="text-sm font-medium text-slate-700">
                        Gebruikersgroepen
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="userGroup in company.usergroups"
                            :key="userGroup.id"
                            @click="editUser && selectUserGroup(userGroup)"
                            class="px-3 py-1 rounded-md text-sm font-medium border transition-all duration-200 flex items-center gap-1"
                            :class="[
                                selectedUserGroups.some(
                                    (g) => g.id === userGroup.id
                                )
                                    ? 'bg-blue-100 border-blue-300 text-blue-700'
                                    : 'bg-white border-gray-300 text-gray-700',
                                editUser
                                    ? 'cursor-pointer'
                                    : 'opacity-50 cursor-default',
                            ]"
                        >
                            <span>{{ userGroup.name }}</span>
                            <span class="text-xs opacity-75"
                                >({{ userGroup.sources.length }} bronnen)</span
                            >
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" v-if="editUser">
                        Selecteer een gebruikersgroep om automatisch de
                        bijbehorende bronnen te laden
                    </p>
                </div>

                <!-- Sources Section -->
                <div class="flex flex-col gap-2 mt-4">
                    <label class="text-sm font-medium text-slate-700">
                        Koppel bronnen
                        <span
                            v-if="selectedUserGroups.length"
                            class="text-xs text-blue-600 font-normal"
                        >
                            (Geladen van:
                            {{
                                selectedUserGroups
                                    .map((g) => g.name)
                                    .join(", ")
                            }})
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="source in company.sources"
                            :key="source.id"
                            @click="editUser && toggleSource(source.id)"
                            :class="[
                                'px-3 py-1 rounded-md text-sm font-medium border transition-all duration-200 flex items-center gap-1',
                                editUser
                                    ? 'cursor-pointer'
                                    : 'opacity-50 cursor-default',
                            ]"
                            :style="{
                                backgroundColor: isSelected(source.id)
                                    ? source.hex_color
                                    : '#f8fafc',
                                borderColor: source.hex_color,
                                color: isSelected(source.id)
                                    ? '#1e293b'
                                    : '#475569',
                            }"
                        >
                            <span>{{ source.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-medium">Rapporten</h2>

                <PrimaryButton @click="toggleAddReport">
                    + Voeg nieuw rapport toe
                </PrimaryButton>
            </div>
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Naam</th>
                        <th scope="col" class="px-6 py-3">Link</th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Aanpassen</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-for="report in user.reports"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 flex items-center font-medium text-gray-900 whitespace-nowrap"
                        >
                            {{ report.name }}
                        </th>
                        <td class="px-6 py-4 truncate max-w-[300px]">
                            {{ report.link }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a
                                :href="
                                    route('user.report.read', {
                                        guid: this.company.guid,
                                        user_guid: this.user.guid,
                                        report_guid: report.guid,
                                    })
                                "
                                class="font-medium text-blue-600 hover:underline"
                                >Pas aan</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-if="user.reports.length < 1"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            colspan="3"
                        >
                            Geen resultaten gevonden
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>

    <Create
        :close="toggleAddReport"
        :show="addReport"
        :user="user"
        sort="User"
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
import Create from "../Reports/Create.vue";

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
    },
    props: {
        company: Object,
        user: Object,
    },
    data() {
        return {
            editUser: false,
            addReport: false,
            selectedUserGroups: [], // meerdere selecties mogelijk
            form: useForm({
                name: this.user.name,
                firstname: this.user.firstname,
                email: this.user.email,
                source_ids: this.user.sources.map((source) => source.id),
                user_group_ids: [],
            }),
        };
    },
    mounted() {
        if (this.user.usergroups && this.user.usergroups.length) {
            this.selectedUserGroups = this.company.usergroups.filter(
                (companyGroup) =>
                    this.user.usergroups.some(
                        (userGroup) => userGroup.id === companyGroup.id
                    )
            );

            // Zet form.user_group_ids
            this.form.user_group_ids = this.selectedUserGroups.map((g) => g.id);

            // Voeg alle bronnen van deze groepen toe aan de source_ids
            const allSources = this.selectedUserGroups.flatMap((g) =>
                g.sources.map((s) => s.id)
            );
            this.form.source_ids = [
                ...new Set([...this.form.source_ids, ...allSources]),
            ];
        }
    },
    methods: {
        deleteUser() {
            this.form.delete(
                route("company.user.delete", {
                    guid: this.company.guid,
                    user_guid: this.user.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        saveUser() {
            this.form.patch(
                route("company.user.update", {
                    guid: this.company.guid,
                    user_guid: this.user.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        selectUserGroup(userGroup) {
            const index = this.selectedUserGroups.findIndex(
                (g) => g.id === userGroup.id
            );

            if (index > -1) {
                // Als al geselecteerd, verwijder uit selectie
                this.selectedUserGroups.splice(index, 1);
            } else {
                // Anders toevoegen
                this.selectedUserGroups.push(userGroup);
            }

            // Update form.user_group_ids
            this.form.user_group_ids = this.selectedUserGroups.map((g) => g.id);

            // Verzamel alle unieke source_ids van geselecteerde groepen
            const allSources = this.selectedUserGroups.flatMap((g) =>
                g.sources.map((s) => s.id)
            );
            this.form.source_ids = [...new Set(allSources)]; // verwijder duplicaten
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
        toggleAddReport() {
            this.addReport = !this.addReport;
        },
    },
    computed: {},
};
</script>
