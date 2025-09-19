<template>
    <Head title="Bronnen" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">
                {{
                    $t("labels.sources").charAt(0).toUpperCase() +
                    $t("labels.sources").slice(1)
                }}
            </h2>
            <PrimaryButton @click="toggleAddSource">
                + {{ $t("buttons.add") }}
            </PrimaryButton>
        </div>

        <div class="relative overflow-x-auto sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right">
                <!-- <caption
                    class="p-5 text-lg font-semibold text-left rtl:text-right bg-white"
                >
                    Onze bronnen
                    <p class="mt-1 text-sm font-normal text-gray-500">
                        Koppel handige bronnen en tools direct aan je gebruiker
                        om hem te ondersteunen, informeren en begeleiden bij het
                        behalen van zijn doelen.
                    </p>
                </caption> -->
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.name") }}
                        </th>
                        <th scope="col" class="px-6 py-3">
                            {{ $t("labels.color") }}
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">
                                {{ $t("labels.edit") }}</span
                            >
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-for="source in sources"
                    >
                        <th
                            scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
                        >
                            {{ source.name }}
                        </th>
                        <td class="px-6 py-4 flex items-center uppercase">
                            <span
                                class="inline-block w-4 h-4 rounded mr-2"
                                :style="{ backgroundColor: source.hex_color }"
                            ></span>

                            {{ source.hex_color }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <a
                                :href="route('source.read', source.id)"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                {{ $t("buttons.edit") }}</a
                            >
                        </td>
                    </tr>
                    <tr
                        class="bg-white border-b border-gray-200"
                        v-if="this.sources.length < 1"
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
    </AuthenticatedLayout>

    <Create :close="toggleAddSource" :show="addSource" />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Create from "../Company/Sources/Create.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        Create,
        PrimaryButton,
    },
    props: {
        sources: Array,
    },
    data() {
        return {
            addSource: false,
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Bronnen", href: "/sources" },
            ],
        };
    },
    methods: {
        toggleAddSource() {
            this.addSource = !this.addSource;
        },
    },
    computed: {},
};
</script>
