<template>
    <Head :title="company.company" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex md:flex-row gap-4 md:gap-0 flex-col md:items-center items-start justify-between"
        >
            <h1 class="text-black font-bold text-4xl">{{ company.company }}</h1>

            <div class="flex gap-2">
                <template v-for="(btn, i) in buttons" :key="i">
                    <component
                        :is="
                            btn.type === 'secondary'
                                ? 'SecondaryButton'
                                : 'PrimaryButton'
                        "
                        v-if="
                            show_sort === btn.condition &&
                            (typeof btn.show === 'function'
                                ? btn.show()
                                : btn.show)
                        "
                        @click="btn.click"
                    >
                        <i v-if="btn.icon" :class="btn.icon + ' mr-2'"></i>
                        {{ btn.text }}
                    </component>
                </template>
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
                    {{ $t("labels.settings") }}
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'usergroups'
                            ? 'bg-gray-200'
                            : 'bg-gray-300',
                    ]"
                    @click="changeSort('usergroups')"
                >
                    {{ $t("labels.usersources") }}
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'sources' ? 'bg-gray-200' : 'bg-gray-300',
                    ]"
                    @click="changeSort('sources')"
                >
                    {{
                        $t("labels.sources").charAt(0).toUpperCase() +
                        $t("labels.sources").slice(1)
                    }}
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'reports' ? 'bg-gray-200' : 'bg-gray-300',
                    ]"
                    @click="changeSort('reports')"
                >
                    {{ $t("labels.reports") }}
                </button>
                <button
                    :class="[
                        'px-4 py-1 rounded-md text-center text-sm',
                        show_sort === 'users' ? 'bg-gray-200' : 'bg-gray-300',
                    ]"
                    @click="changeSort('users')"
                >
                    {{ $t("labels.users") }}
                </button>
            </div>
        </div>

        <div v-if="this.show_sort === 'settings'">
            <!-- Default settings -->
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="name" :value="$t('labels.name') + '*'" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.company"
                        :disabled="!editCompany"
                        :placeholder="$t('labels.name')"
                    />
                    <InputError class="mt-2" :message="form.errors.company" />
                </div>
            </div>
        </div>

        <div v-if="this.show_sort === 'sources'" class="mt-6">
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.name") }}
                        </th>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.webhook") }}
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">{{ $t("labels.edit") }}</span>
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
                                >{{ $t("buttons.edit") }}</a
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
                            {{ $t("labels.noresults") }}
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="this.show_sort === 'reports'" class="mt-6">
            <table class="w-full text-sm text-left rtl:text-right">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.name") }}
                        </th>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.link") }}
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">{{ $t("labels.edit") }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-for="report in this.company.reports"
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
                                    route('company.report.read', {
                                        guid: this.company.guid,
                                        report_guid: report.guid,
                                    })
                                "
                                class="font-medium text-blue-600 hover:underline"
                                >{{ $t("buttons.edit") }}</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-if="this.company.reports.length < 1"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            colspan="3"
                        >
                            {{ $t("labels.noresults") }}
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
                            <th scope="col" class="px-6 py-3">
                                {{ $t("labels.name") }}
                            </th>
                            <th scope="col" class="px-6 py-3">
                                {{ $t("labels.email") }}
                            </th>
                            <th scope="col" class="px-6 py-3">
                                {{
                                    $t("labels.sources")
                                        .charAt(0)
                                        .toUpperCase() +
                                    $t("labels.sources").slice(1)
                                }}
                            </th>

                            <th scope="col" class="px-6 py-3">
                                <span class="sr-only">{{
                                    $t("labels.edit")
                                }}</span>
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
                                {{ user.firstname }} {{ user.name }}
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
                                    >{{ $t("buttons.edit") }}</a
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
                                {{ $t("labels.noresults") }}
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="this.show_sort === 'usergroups'">
            <div class="overflow-x-auto mt-6">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                {{ $t("labels.name") }}
                            </th>
                            <th scope="col" class="px-6 py-3">
                                {{
                                    $t("labels.sources")
                                        .charAt(0)
                                        .toUpperCase() +
                                    $t("labels.sources").slice(1)
                                }}
                            </th>

                            <th scope="col" class="px-6 py-3">
                                <span class="sr-only">{{
                                    $t("labels.edit")
                                }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-for="group in company.usergroups"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            >
                                {{ group.name }}
                            </th>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="source in group.sources"
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
                                        route('company.usergroup.read', {
                                            guid: company.guid,
                                            usergroup_guid: group.guid,
                                        })
                                    "
                                    class="font-medium text-blue-600 hover:underline"
                                    >{{ $t("buttons.edit") }}</a
                                >
                            </td>
                        </tr>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-if="this.company.usergroups.length < 1"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                                colspan="4"
                            >
                                {{ $t("buttons.delete") }}
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

    <CreateReports
        :close="toggleAddReport"
        :show="addReport"
        :company="this.company"
    />

    <CreateGroups
        :close="toggleAddUserGroup"
        :show="addUserGroup"
        :company="this.company"
        :sources="this.company.sources"
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
import CreateReports from "./Reports/Create.vue";
import CreateGroups from "./UserGroups/Create.vue";

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
        CreateReports,
        CreateGroups,
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
            addReport: false,
            addUser: false,
            addUserGroup: false,
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
        toggleAddUserGroup() {
            this.addUserGroup = !this.addUserGroup;
        },
        toggleAddSource() {
            this.addSource = !this.addSource;
        },
        toggleAddReport() {
            this.addReport = !this.addReport;
        },
    },
    computed: {
        buttons() {
            return [
                {
                    condition: "settings",
                    show: true,
                    icon: "fas fa-edit",
                    text: "Pas aan",
                    click: this.toggleEdit,
                },
                {
                    condition: "settings",
                    show: this.editCompany,
                    icon: "fas fa-check",
                    text: "Sla op",
                    click: this.saveCompany,
                },
                {
                    condition: "settings",
                    show: true,
                    icon: "fas fa-trash-alt",
                    text: "Verwijder",
                    type: "secondary",
                    click: this.deleteCompany,
                },
                {
                    condition: "users",
                    show: true,
                    text: "+ Voeg nieuwe gebruiker toe",
                    click: this.toggleAddUser,
                },
                {
                    condition: "usergroups",
                    show: true,
                    text: "+ Voeg nieuwe groep toe",
                    click: this.toggleAddUserGroup,
                },
                {
                    condition: "sources",
                    show: true,
                    text: "+ Voeg nieuwe bron toe",
                    click: this.toggleAddSource,
                },
                {
                    condition: "reports",
                    show: true,
                    text: "+ Voeg nieuw rapport toe",
                    click: this.toggleAddReport,
                },
            ];
        },
    },
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
