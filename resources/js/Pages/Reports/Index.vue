<template>
    <Head title="Bronnen" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="py-6">
            <!-- All Reports - User en Company op 1 lijn -->
            <div class="flex justify-between items-start mb-4">
                <!-- User Reports - Links -->
                <div class="flex gap-2 flex-wrap">
                    <div
                        v-for="report in userReports"
                        :key="report.id"
                        @click="selectReport(report)"
                        class="px-3 py-2 bg-white rounded border hover:shadow-sm transition-all cursor-pointer text-sm"
                        :class="{
                            'ring-2 ring-blue-500 bg-blue-50':
                                selectedReport?.id === report.id,
                        }"
                    >
                        {{ report.name }}
                    </div>
                </div>

                <!-- Company Reports - Rechts -->
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-for="report in companyReports"
                        :key="report.id"
                        @click="openExternalLink(report.link)"
                        :title="report.naam"
                        class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors cursor-pointer"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- iFrame voor geselecteerde user report -->
            <div v-if="selectedReport" class="w-full">
                <div class="bg-white rounded border">
                    <div class="p-3 border-b">
                        <h3 class="text-sm font-medium">
                            {{ selectedReport.name }}
                        </h3>
                    </div>
                    <div class="w-full h-full">
                        <iframe
                            :src="selectedReport.link"
                            class="w-full h-full"
                            frameborder="0"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>

            <!-- Placeholder als geen user report geselecteerd -->
            <div v-else-if="userReports.length > 0" class="w-full">
                <div
                    class="bg-gray-50 rounded border-2 border-dashed border-gray-300 p-8 text-center h-32"
                >
                    <p class="text-sm text-gray-600">
                        Selecteer een report om te bekijken
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

export default {
    name: "ReportsIndex",
    components: {
        Head,
        AuthenticatedLayout,
    },
    props: {
        reports: Array,
        breadcrumbs: Array,
    },
    data() {
        return {
            selectedReport: null,
        };
    },
    methods: {
        openExternalLink(url) {
            window.open(url, "_blank");
        },
        selectReport(report) {
            this.selectedReport = report;
        },
    },
    computed: {
        companyReports() {
            return this.reports.filter((report) => report.type === "company");
        },
        userReports() {
            return this.reports.filter((report) => report.type === "user");
        },
    },
};
</script>
