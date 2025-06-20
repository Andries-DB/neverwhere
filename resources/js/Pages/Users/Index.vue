<template>
    <Head title="Gebruikers" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Gebruikers
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Beheer gebruikers en hun toegang tot het platform
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2" v-show="true">
                <template v-if="hasActiveFilters">
                    <span
                        v-if="searchQuery"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                        Zoeken: "{{ searchQuery }}"
                        <button
                            @click="
                                searchQuery = '';
                                handleSearch();
                            "
                            class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-500"
                        >
                            <svg
                                class="w-2 h-2"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 8 8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.5"
                                    d="m1 1 6 6m0-6-6 6"
                                />
                            </svg>
                        </button>
                    </span>

                    <span
                        v-if="selectedRole"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                    >
                        Rol:
                        <span class="capitalize ml-0.5">{{
                            selectedRole
                        }}</span>
                        <button
                            @click="
                                selectedRole = '';
                                handleRoleFilter();
                            "
                            class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500"
                        >
                            <svg
                                class="w-2 h-2"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 8 8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.5"
                                    d="m1 1 6 6m0-6-6 6"
                                />
                            </svg>
                        </button>
                    </span>

                    <span
                        v-if="selectedCompany"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                    >
                        Bedrijf: {{ getCompanyName(selectedCompany) }}
                        <button
                            @click="
                                selectedCompany = '';
                                handleCompanyFilter();
                            "
                            class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full text-purple-400 hover:bg-purple-200 hover:text-purple-500"
                        >
                            <svg
                                class="w-2 h-2"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 8 8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-width="1.5"
                                    d="m1 1 6 6m0-6-6 6"
                                />
                            </svg>
                        </button>
                    </span>
                </template>
            </div>

            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <svg
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            type="text"
                            v-model="searchQuery"
                            @input="handleSearch"
                            placeholder="Zoek gebruikers..."
                            class="h-10 w-full rounded-md border border-gray-200 bg-white pl-10 pr-3 text-sm placeholder:text-gray-500 focus:border-gray-900 focus:outline-none focus:ring-0 focus:ring-gray-900"
                        />
                    </div>
                </div>

                <!-- Role Filter -->
                <select
                    v-model="selectedRole"
                    @change="handleRoleFilter"
                    class="h-10 w-full sm:w-[180px] rounded-md border border-gray-200 bg-white px-3 text-sm focus:border-gray-900 focus:outline-none focus:ring-0 focus:ring-gray-900"
                >
                    <option value="">Alle rollen</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <!-- Company Filter -->
                <select
                    v-model="selectedCompany"
                    @change="handleCompanyFilter"
                    class="h-10 w-full sm:w-[180px] rounded-md border border-gray-200 bg-white px-3 text-sm focus:border-gray-900 focus:outline-none focus:ring-0 focus:ring-gray-900"
                >
                    <option value="">Alle bedrijven</option>
                    <option
                        v-for="company in availableCompanies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.company }}
                    </option>
                </select>

                <!-- Clear Filters -->
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="h-10 rounded-md border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-900"
                >
                    Reset
                </button>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right">
                    <caption
                        class="p-5 text-lg font-semibold text-left rtl:text-right bg-white"
                    >
                        Gebruikersoverzicht
                        <p class="mt-1 text-sm font-normal text-gray-500">
                            Bekijk een overzicht van alle gebruikers binnen het
                            platform. Ontdek wie actief zijn, met wie je kunt
                            samenwerken of ervaringen kunt delen.
                            <span
                                v-if="filteredUsers.length !== users.length"
                                class="font-medium text-blue-600"
                            >
                                ({{ filteredUsers.length }} van
                                {{ users.length }} gebruikers getoond)
                            </span>
                        </p>
                    </caption>
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Naam</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Bedrijf</th>
                            <th scope="col" class="px-6 py-3">Rol</th>
                            <th scope="col" class="px-6 py-3">
                                <span class="sr-only">Aanpassen</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-for="user in filteredUsers"
                            :key="user.id"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            >
                                {{ user.id }}
                            </th>
                            <td class="px-6 py-4">{{ user.name }}</td>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4">
                                <a
                                    v-if="user.companies.length > 0"
                                    :href="
                                        route(
                                            'company.read',
                                            user.companies[0].guid
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 hover:underline"
                                >
                                    {{ user.companies[0]?.company }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                    :class="{
                                        'bg-blue-100 text-blue-800':
                                            user.role === 'admin',
                                        'bg-gray-100 text-gray-800':
                                            user.role === 'user',
                                        'bg-green-100 text-green-800':
                                            user.role === 'manager',
                                    }"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    :href="route('user.read', user.guid)"
                                    class="font-medium text-blue-600 hover:underline"
                                >
                                    Pas aan
                                </a>
                            </td>
                        </tr>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-if="filteredUsers.length < 1"
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
                                        Geen gebruikers gevonden
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{
                                            hasActiveFilters
                                                ? "Probeer andere zoektermen of filters."
                                                : "Er zijn nog geen gebruikers toegevoegd."
                                        }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    name: "Users",
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
    },
    props: {
        users: Array,
    },
    data() {
        return {
            addCompany: false,
            searchQuery: "",
            selectedRole: "",
            selectedCompany: "",
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Gebruikers", href: "/users" },
            ],
        };
    },
    computed: {
        filteredUsers() {
            let filtered = this.users;

            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(
                    (user) =>
                        user.name.toLowerCase().includes(query) ||
                        user.email.toLowerCase().includes(query) ||
                        (user.companies.length > 0 &&
                            user.companies[0].company
                                .toLowerCase()
                                .includes(query))
                );
            }

            // Apply role filter
            if (this.selectedRole) {
                filtered = filtered.filter(
                    (user) => user.role === this.selectedRole
                );
            }

            // Apply company filter
            if (this.selectedCompany) {
                filtered = filtered.filter(
                    (user) =>
                        user.companies.length > 0 &&
                        user.companies[0].id == this.selectedCompany
                );
            }

            return filtered;
        },
        availableCompanies() {
            const companies = [];
            const seen = new Set();

            this.users.forEach((user) => {
                if (user.companies.length > 0) {
                    const company = user.companies[0];
                    if (!seen.has(company.id)) {
                        companies.push(company);
                        seen.add(company.id);
                    }
                }
            });

            return companies.sort((a, b) => a.company.localeCompare(b.company));
        },
        hasActiveFilters() {
            return (
                this.searchQuery || this.selectedRole || this.selectedCompany
            );
        },
    },
    methods: {
        toggleAddCompany() {
            this.addCompany = !this.addCompany;
        },

        clearFilters() {
            this.searchQuery = "";
            this.selectedRole = "";
            this.selectedCompany = "";
        },
        getCompanyName(companyId) {
            const company = this.availableCompanies.find(
                (c) => c.id == companyId
            );
            return company ? company.company : "";
        },
    },
};
</script>
