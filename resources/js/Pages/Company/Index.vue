<template>
    <Head title="Bedrijven" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Bedrijven</h2>
            <PrimaryButton @click="toggleAddCompany">
                + Voeg bedrijf toe
            </PrimaryButton>
        </div>
        <div class="relative overflow-x-auto sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right">
                <caption
                    class="p-5 text-lg font-semibold text-left rtl:text-right bg-white"
                >
                    Lijst van bedrijven
                    <p class="mt-1 text-sm font-normal text-gray-500">
                        Blader door een lijst met bedrijven die services en
                        tools aanbieden om je te helpen georganiseerd te
                        blijven, je bedrijf te laten groeien en verbonden te
                        blijven.
                    </p>
                </caption>
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Naam</th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Aanpassen</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-for="company in companies"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                        >
                            {{ company.id }}
                        </th>
                        <td class="px-6 py-4">{{ company.company }}</td>

                        <td class="px-6 py-4 text-right">
                            <a
                                :href="route('company.read', company.guid)"
                                class="font-medium text-blue-600 hover:underline"
                                >Pas aan</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-if="this.companies.length < 1"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                            colspan="3"
                        >
                            Geen resulaten gevonden
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>

    <Create :close="toggleAddCompany" :show="addCompany" />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Create from "./Create.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        Create,
        PrimaryButton,
    },
    props: {
        companies: Array,
    },
    data() {
        return {
            addCompany: false,
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Companies", href: "/companies" },
            ],
        };
    },
    methods: {
        toggleAddCompany() {
            this.addCompany = !this.addCompany;
        },
    },
    computed: {},
};
</script>
