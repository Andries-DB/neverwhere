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

            <div class="relative overflow-x-auto sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Datum</th>
                            <th scope="col" class="px-6 py-3">Gebruiker</th>
                            <th scope="col" class="px-6 py-3">Bericht</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-for="log in this.logs"
                            :key="log.id"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            >
                                {{ log.created_at }}
                            </th>
                            <td class="px-6 py-4">
                                {{ log.user.firstname }} {{ log.user.name }}
                            </td>
                            <td class="px-6 py-4">{{ log.message }}</td>
                        </tr>
                        <tr
                            class="bg-white border-b border-gray-200"
                            v-if="this.logs.length < 1"
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

export default {
    name: "",
    components: { Head, AuthenticatedLayout },
    props: {
        logs: Array,
    },
    data() {
        return {};
    },
    methods: {},
    computed: {},
};
</script>
