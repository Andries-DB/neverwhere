<template>
    <Head title="Logs" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Logs</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Beheer de logs van de applicatie
                    </p>
                </div>
            </div>

            <!-- Search and Filter Controls -->

            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <label for="search" class="sr-only">Zoeken</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <svg
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            id="search"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Zoek in berichten, gebruikers..."
                            class="block w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-md leading-5"
                        />
                    </div>
                </div>

                <!-- Group Toggle -->
                <div class="flex items-center space-x-3">
                    <label class="flex items-center">
                        <input
                            v-model="groupByUser"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700"
                            >Groepeer op gebruiker</span
                        >
                    </label>
                </div>
            </div>

            <!-- Logs Table/Groups -->
            <div class="relative overflow-x-auto sm:rounded-lg">
                <!-- Grouped View -->
                <div v-if="groupByUser" class="space-y-4">
                    <div
                        v-for="(userLogs, userName) in groupedLogs"
                        :key="userName"
                        class="bg-white border rounded-lg overflow-hidden"
                    >
                        <!-- User Header -->
                        <div class="bg-slate-50 px-6 py-3 border-b">
                            <button
                                @click="toggleGroup(userName)"
                                class="w-full flex items-center justify-between text-left rounded px-2 py-1 -mx-2 -my-1 transition-colors"
                            >
                                <div class="flex items-center space-x-3">
                                    <svg
                                        :class="[
                                            'h-5 w-5 text-gray-500 transform transition-transform',
                                            expandedGroups[userName]
                                                ? 'rotate-90'
                                                : '',
                                        ]"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                    <h3
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ userName }}
                                    </h3>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                    >
                                        {{ userLogs.length }}
                                        {{
                                            userLogs.length === 1
                                                ? "log"
                                                : "logs"
                                        }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{
                                            expandedGroups[userName]
                                                ? "Inklappen"
                                                : "Uitklappen"
                                        }}
                                    </span>
                                </div>
                            </button>
                        </div>

                        <!-- User Logs (Collapsible) -->
                        <div
                            v-show="expandedGroups[userName]"
                            class="divide-y divide-gray-200"
                        >
                            <div
                                v-for="log in userLogs"
                                :key="log.id"
                                class="px-6 py-2 hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">
                                            {{ log.message }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ log.created_at }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Results for Grouped View -->
                    <div
                        v-if="Object.keys(groupedLogs).length === 0"
                        class="bg-white rounded-lg border p-8 text-center"
                    >
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            Geen logs gevonden
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{
                                hasActiveFilters
                                    ? "Probeer andere zoektermen."
                                    : "Er zijn nog geen logs beschikbaar."
                            }}
                        </p>
                    </div>
                </div>

                <!-- Table View -->
                <table v-else class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Datum</th>
                            <th scope="col" class="px-6 py-3">Gebruiker</th>
                            <th scope="col" class="px-6 py-3">Bericht</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200 hover:bg-gray-50"
                            v-for="log in filteredLogs"
                            :key="log.id"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            >
                                {{ log.created_at }}
                            </th>
                            <td class="px-6 py-4">
                                {{ getUserName(log.user) }}
                            </td>
                            <td class="px-6 py-4">{{ log.message }}</td>
                        </tr>

                        <!-- No Results Row -->
                        <tr
                            v-if="filteredLogs.length < 1"
                            class="bg-white border-b border-gray-200"
                        >
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center"
                                colspan="3"
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
                                        Geen logs gevonden
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{
                                            hasActiveFilters
                                                ? "Probeer andere zoektermen."
                                                : "Er zijn nog geen logs beschikbaar."
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

export default {
    name: "LogsPage",
    components: { Head, AuthenticatedLayout },
    props: {
        logs: Array,
    },
    data() {
        return {
            searchQuery: "",
            groupByUser: false,
            expandedGroups: {}, // Track which groups are expanded
        };
    },
    computed: {
        filteredLogs() {
            if (!this.searchQuery.trim()) {
                return this.logs;
            }

            const query = this.searchQuery.toLowerCase();
            return this.logs.filter((log) => {
                const userName = this.getUserName(log.user).toLowerCase();
                const message = log.message.toLowerCase();
                const date = log.created_at.toLowerCase();

                return (
                    userName.includes(query) ||
                    message.includes(query) ||
                    date.includes(query)
                );
            });
        },

        groupedLogs() {
            if (!this.groupByUser) return {};

            const grouped = {};
            this.filteredLogs.forEach((log) => {
                const userName = this.getUserName(log.user);
                if (!grouped[userName]) {
                    grouped[userName] = [];
                }
                grouped[userName].push(log);
            });

            // Sort logs within each group by date (newest first)
            Object.keys(grouped).forEach((userName) => {
                grouped[userName].sort(
                    (a, b) => new Date(b.created_at) - new Date(a.created_at)
                );
            });

            return grouped;
        },

        hasActiveFilters() {
            return this.searchQuery.trim() !== "";
        },
    },
    methods: {
        getUserName(user) {
            if (!user) return "Onbekende gebruiker";
            return (
                `${user.firstname || ""} ${user.name || ""}`.trim() ||
                "Onbekende gebruiker"
            );
        },

        toggleGroup(userName) {
            this.expandedGroups[userName] = !this.expandedGroups[userName];
        },

        expandAllGroups() {
            const grouped = this.groupedLogs;
            Object.keys(grouped).forEach((userName) => {
                this.expandedGroups[userName] = true;
            });
        },

        collapseAllGroups() {
            this.expandedGroups = {};
        },
    },
};
</script>
