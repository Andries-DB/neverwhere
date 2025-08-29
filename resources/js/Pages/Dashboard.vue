<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <main class="space-y-6 sm:space-y-8 px-4 sm:px-0">
            <div class="flex justify-between items-center w-full">
                <!-- Dashboard Dropdown -->
                <div class="relative" v-click-outside="closeDashboardDropdown">
                    <button
                        @click="toggleDashboardDropdown"
                        class="inline-flex items-center justify-between min-w-[200px] px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                        <span class="truncate">{{
                            selectedDashboard
                                ? selectedDashboard.name
                                : "Selecteer dashboard"
                        }}</span>
                        <div class="ml-2 flex flex-col">
                            <svg
                                class="h-3 w-3 -mb-1"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                class="h-3 w-3"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </button>

                    <!-- Dashboard Menu -->
                    <div
                        v-if="isDashboardOpen"
                        class="absolute z-50 mt-1 w-full min-w-[200px] rounded-lg bg-white shadow-lg"
                    >
                        <div class="py-1 max-h-60 overflow-auto">
                            <div
                                v-for="dashboard in all_dashboards"
                                :key="dashboard.guid"
                                @click="selectDashboard(dashboard)"
                                class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{
                                    'bg-gray-100 text-gray-900':
                                        selectedDashboard &&
                                        selectedDashboard.guid ===
                                            dashboard.guid,
                                }"
                            >
                                <span class="truncate">{{
                                    dashboard.name
                                }}</span>
                                <svg
                                    v-if="
                                        selectedDashboard &&
                                        selectedDashboard.guid ===
                                            dashboard.guid
                                    "
                                    class="h-4 w-4 text-gray-900"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="
                                    !all_dashboards ||
                                    all_dashboards.length === 0
                                "
                                class="px-3 py-2 text-sm text-gray-500"
                            >
                                Geen dashboards beschikbaar
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="relative" v-click-outside="closeSettingsDropdown">
                    <button
                        @click="toggleSettingsDropdown"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <circle cx="10" cy="3" r="1.5" />
                            <circle cx="10" cy="10" r="1.5" />
                            <circle cx="10" cy="17" r="1.5" />
                        </svg>
                    </button>

                    <!-- Settings Menu -->
                    <div
                        v-if="isSettingsDropdownOpen"
                        class="absolute right-0 top-full mt-1 w-52 bg-white rounded-md shadow-lg border border-slate-200 z-50"
                    >
                        <div>
                            <button
                                @click="toggleAddDashboard()"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                            >
                                <svg
                                    class="h-4 w-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                    />
                                </svg>
                                Maak nieuw dashboard
                            </button>
                            <button
                                @click="toggleExportDashbpard()"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                            >
                                <i class="far fa-file-pdf mr-[14px]"></i>

                                Exporteer dashboard
                            </button>
                            <button
                                @click="makeDefault(dashboard)"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                                v-if="!dashboard.default"
                            >
                                <svg
                                    class="h-4 w-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                    />
                                </svg>
                                Maak default
                            </button>

                            <hr class="my-1 border-gray-200" />

                            <button
                                @click="deleteDashboard(dashboard)"
                                class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors duration-150 flex items-center"
                            >
                                <svg
                                    class="h-4 w-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                                Verwijder
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welkomstbericht -->
            <section
                class="text-center text-gray-600 max-w-md mx-auto px-4 sm:px-0"
                v-if="!pinned_items?.length"
            >
                <p>
                    Welkom op je dashboard! Hier kun je grafieken en tabellen
                    toevoegen om snel inzicht te krijgen in je data. Begin met
                    het vastpinnen van items via de conversatie.
                </p>
            </section>

            <section v-if="pinned_items?.length" class="space-y-4">
                <div
                    ref="pinnedContainer"
                    class="grid grid-cols-1 md:grid-cols-2 gap-6 dashboard-content"
                >
                    <div
                        v-for="item in pinned_items"
                        :key="item.id"
                        :data-id="item.id"
                        :class="[
                            'bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow cursor-move ',
                            item.width === 'full'
                                ? 'col-span-2 '
                                : 'md:col-span-1 col-span-2',
                            'sortable-pinned',
                        ]"
                    >
                        <!-- Header -->
                        <div
                            class="drag-handle px-4 py-2 border-b border-gray-100"
                        >
                            <div class="flex justify-between items-center">
                                <div class="flex-1 mr-3">
                                    <!-- Editable Title -->
                                    <div v-if="editingTitleId !== item.id">
                                        <h3
                                            class="text-base font-medium text-gray-900 group-hover:text-blue-600 transition-colors duration-200 flex items-center gap-3"
                                        >
                                            <svg
                                                class="w-4 h-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                title="Sleep om te verplaatsen"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 8h16M4 16h16"
                                                ></path>
                                            </svg>
                                            {{ item.title }}
                                        </h3>
                                    </div>

                                    <!-- Edit Mode -->
                                    <div
                                        v-else
                                        class="flex items-center space-x-2"
                                    >
                                        <input
                                            ref="titleInput"
                                            v-model="editingTitle"
                                            @keydown.enter="saveTitle(item.id)"
                                            @keydown.escape="cancelEditingTitle"
                                            @blur="saveTitle(item.id)"
                                            class="flex-1 text-lg font-medium text-gray-900 bg-transparent border-blue-500 focus:outline-none focus:border-blue-600 transition-colors"
                                            :placeholder="getGraphTitle(item)"
                                        />
                                        <div class="flex space-x-1">
                                            <button
                                                @click="saveTitle(item.id)"
                                                class="p-1 text-green-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors"
                                                title="Opslaan"
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
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                            </button>
                                            <button
                                                @click="cancelEditingTitle"
                                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded transition-colors"
                                                title="Annuleren"
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
                                                        d="M6 18L18 6M6 6l12 12"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative">
                                    <button
                                        @click="toggleDropdown(item.id)"
                                        class="text-gray-400 hover:text-gray-600 transition-colors"
                                        title="Acties"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"
                                            />
                                        </svg>
                                    </button>

                                    <div
                                        v-if="this.openDropdown === item.id"
                                        class="absolute right-0 top-full mt-1 w-40 bg-white rounded-md shadow-lg border border-slate-200 z-50"
                                    >
                                        <!-- Titel bewerken optie -->
                                        <button
                                            @click="
                                                startEditingTitle(item);
                                                this.openDropdown = null;
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            Titel bewerken
                                        </button>

                                        <button
                                            @click="
                                                toggleGraphWidth(item);
                                                this.openDropdown = null;
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"
                                                />
                                            </svg>
                                            {{
                                                item.width === "full"
                                                    ? "Verklein"
                                                    : "Vergroot"
                                            }}
                                        </button>

                                        <button
                                            @click="
                                                toggleGraphRefresh(item);
                                                this.openDropdown = null;
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-all duration-150 flex items-center gap-2"
                                        >
                                            <svg
                                                class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-transform duration-200"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 4v6h6M20 20v-6h-6M4 20l5-5M20 4l-5 5"
                                                />
                                            </svg>
                                            <span>Refresh</span>
                                        </button>

                                        <button
                                            @click="
                                                duplicateGraph(item);
                                                this.openDropdown = null;
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                                        >
                                            <i
                                                class="far fa-clone mr-2 w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-transform duration-200"
                                            ></i>
                                            Dupliceer
                                        </button>

                                        <!-- Divider -->
                                        <div
                                            class="border-t border-slate-200 my-1"
                                        ></div>

                                        <!-- Losmaken optie -->
                                        <button
                                            @click="
                                                unpinGraph(item.id);
                                                this.openDropdown = null;
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors duration-150 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-red-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Losmaken
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div>
                            <div
                                class="w-full h-96 bg-white rounded border border-gray-100"
                                v-if="item.type === 'graph'"
                            >
                                <ag-charts
                                    v-if="
                                        getPinnedChartOptions(item).data?.length
                                    "
                                    class="w-full h-full"
                                    :options="getPinnedChartOptions(item)"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-gray-500"
                                >
                                    <div class="text-center">
                                        <i
                                            class="fas fa-chart-bar text-3xl mb-2"
                                        ></i>
                                        <p class="text-sm">
                                            Geen data beschikbaar voor grafiek
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="w-full h-96 bg-white"
                                v-if="item.type === 'table'"
                            >
                                <TableBuilder
                                    :message="item.message"
                                    sort="pinned"
                                    :col_def="item.col_def"
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="px-4 py-2 bg-gray-50 border-t border-gray-100 rounded-b-lg"
                        >
                            <div
                                class="flex justify-between items-center text-xs text-gray-500"
                            >
                                <span
                                    >{{ item.json?.length || 0 }} records</span
                                >
                                <span v-if="item.type == 'graph'">{{
                                    formatChartType(item?.sort_chart)
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>

    <RefreshGraph
        :show="showRefreshModal"
        :close="toggleGraphRefresh"
        :graph="selectedGraphRefresh"
        :sort="selectedSortRefresh"
    />

    <AddDashboard :show="addDashboard" :close="toggleAddDashboard" />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { format, isToday, parseISO } from "date-fns";
import { AgCharts } from "ag-charts-vue3";

import RefreshGraph from "@/Components/Modals/RefreshGraph.vue";
import Sortable from "sortablejs";
import AddDashboard from "@/Components/Modals/AddDashboard.vue";
import { changeLocale } from "@/lang";
import TableBuilder from "@/Components/TableBuilder.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        AgCharts,
        RefreshGraph,
        AddDashboard,
        TableBuilder,
    },
    props: {
        dashboard: Object,
        all_dashboards: Array,
        pinned_items: Array,
    },
    data() {
        return {
            addDashboard: false,
            isDashboardOpen: false,
            selectedDashboard: null,
            isSettingsDropdownOpen: false,
            openDropdown: null,
            selectedGraphRefresh: null,
            selectedSortRefresh: "",
            showRefreshModal: false,
            form: useForm({}),
            breadcrumbs: [{ title: "Dashboard", href: "/dashboard" }],
            availableChartTypes: [
                { value: "column", label: "Kolom" },
                { value: "bar", label: "Staaf" },
                { value: "line", label: "Lijn" },
                { value: "area", label: "Gebied" },
            ],
            aggregationTypes: [
                { value: "sum", label: "Som" },
                { value: "avg", label: "Gemiddelde" },
                { value: "count", label: "Aantal" },
                { value: "min", label: "Minimum" },
                { value: "max", label: "Maximum" },
            ],
            editingTitleId: null,
            editingTitle: "",
            savingTitleId: null,

            graphsSortable: null,
        };
    },
    methods: {
        setLang(l) {
            changeLocale(l);
        },
        // Update CSRF token na response
        updateCsrfToken(response) {
            const newToken = response.headers.get("X-CSRF-TOKEN");
            if (newToken) {
                document
                    .querySelector('meta[name="csrf-token"]')
                    .setAttribute("content", newToken);
            }
        },
        getCsrfToken() {
            return document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
        },

        // Simple functions
        toggleDropdown(id) {
            this.openDropdown = this.openDropdown === id ? null : id;
        },
        toggleAddDashboard() {
            this.addDashboard = !this.addDashboard;
        },
        async toggleExportDashbpard() {
            try {
                const response = await fetch(
                    "/pinboard/" + this.dashboard?.guid + "/export",
                    {
                        method: "POST",
                        headers: {
                            Accept: "application/json",

                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": this.getCsrfToken(),
                        },
                    }
                );

                if (!response.ok) {
                    const error = await response.json();
                    alert("Export failed: " + error.message);
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    let link = document.createElement("a");
                    link.href = result.file_url;
                    link.download = result.file_name || "quotes_export.xlsx";
                    document.body.appendChild(link);
                    link.click();
                    URL.revokeObjectURL(link.href);
                    this.updateCsrfToken(response);
                }

                this.loading = false;
            } catch (error) {
                console.error("Error generating PDF:", error);

                this.loading = false;
            }
        },

        createConversation() {
            this.form.post(route("conversation.create"), {
                onSuccess: () => {
                    location.reload();
                },
                onError: (errors) => {},
            });
        },
        async reorderItems(evt, items, updateFn) {
            const { newIndex, oldIndex } = evt;
            const movedItem = items.splice(oldIndex, 1)[0];
            items.splice(newIndex, 0, movedItem);

            try {
                await updateFn(
                    items.map((item, index) => ({
                        id: item.id,
                        display_order: index + 1,
                    }))
                );
            } catch (error) {
                console.error("Error updating order:", error);
                const revertedItem = items.splice(newIndex, 1)[0];
                items.splice(oldIndex, 0, revertedItem);
            }
        },

        toggleSettingsDropdown() {
            this.isSettingsDropdownOpen = !this.isSettingsDropdownOpen;
            this.isDashboardDropdownOpen = false;
        },
        closeSettingsDropdown() {
            this.isSettingsDropdownOpen = false;
        },

        // Graph functions
        formatDate(dateString) {
            const date = parseISO(dateString + "Z");

            if (isToday(date)) {
                return format(date, "HH:mm");
            } else {
                return format(date, "dd MMM");
            }
        },
        parseDate(value) {
            if (!value) return null;

            // Converteer naar string als het dat nog niet is
            const dateStr = String(value).trim();

            // Leeg of ongeldig
            if (!dateStr || dateStr === "null" || dateStr === "undefined")
                return null;

            // Probeer verschillende datum formaten
            const formats = [
                /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/, // 2023-12-01T10:30:00
                /^\d{4}-\d{2}-\d{2}/, // 2023-12-01
                /^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}/, // 01-12-2023 of 1/12/2023
                /^\d{1,2}[-\/]\d{1,2}[-\/]\d{2}/, // 01-12-23
                /^\d{13}$/, // 1640995200000
                /^\d{10}$/, // 1640995200
            ];

            // Controleer of het een geldig datum formaat lijkt
            const hasDateFormat = formats.some((format) =>
                format.test(dateStr)
            );

            if (hasDateFormat) {
                // Probeer als timestamp (milliseconds)
                if (/^\d{13}$/.test(dateStr)) {
                    const timestamp = parseInt(dateStr);
                    const date = new Date(timestamp);
                    return isNaN(date.getTime()) ? null : date;
                }

                // Probeer als timestamp (seconds)
                if (/^\d{10}$/.test(dateStr)) {
                    const timestamp = parseInt(dateStr) * 1000;
                    const date = new Date(timestamp);
                    return isNaN(date.getTime()) ? null : date;
                }

                // Probeer normale datum parsing
                const date = new Date(dateStr);

                // Controleer of datum geldig is en niet in de verre toekomst/verleden
                if (!isNaN(date.getTime())) {
                    const year = date.getFullYear();
                    if (year >= 1900 && year <= 2100) {
                        return date;
                    }
                }
            }

            return null;
        },
        isDateField(fieldName, data) {
            if (!data || data.length === 0) return false;

            // Check eerste 10 records (of minder als er minder zijn)
            const sampleSize = Math.min(10, data.length);
            let dateCount = 0;

            for (let i = 0; i < sampleSize; i++) {
                const value = data[i][fieldName];
                if (this.parseDate(value)) {
                    dateCount++;
                }
            }

            // Als meer dan 70% van de samples geldige datums zijn
            return dateCount / sampleSize > 0.7;
        },
        isNumericField(fieldName, data) {
            if (!data || data.length === 0) return false;

            // Check eerste paar records
            const sampleValues = data
                .slice(0, 5)
                .map((item) => item[fieldName])
                .filter((val) => val != null && val !== "");

            if (sampleValues.length === 0) return false;

            return sampleValues.every((value) => {
                const num = parseFloat(value);
                return !isNaN(num) && isFinite(num);
            });
        },
        getGraphTitle(graph) {
            if (graph.type !== "graph") {
                return "";
            }
            // Generate a meaningful title based on the graph configuration
            const yLabel = graph._y.charAt(0).toUpperCase() + graph._y.slice(1);
            const xLabel = graph._x.charAt(0).toUpperCase() + graph._x.slice(1);
            const aggLabel = graph._agg ? ` (${graph._agg})` : "";

            return `${yLabel} per ${xLabel}${aggLabel}`;
        },
        getGraphSubtitle(graph) {
            const chartType = this.formatChartType(graph.sort_chart);
            const recordCount = graph.json ? graph.json.length : 0;
            return `${chartType} • ${recordCount} records`;
        },
        formatChartType(chartType) {
            const types = {
                bar: "Staafdiagram",
                line: "Lijndiagram",
                pie: "Taartdiagram",
                column: "Kolomdiagram",
                area: "Vlakdiagram",
            };
            return types[chartType] || chartType;
        },
        getPinnedChartOptions(graph) {
            const chartType = graph.sort_chart || "column";
            const xAxis = graph._x;
            const yAxis = graph._y;
            const aggregation = graph._agg || "sum";
            const order = graph._order || "value_desc";

            if (!xAxis || !yAxis || !graph.json) {
                return {};
            }

            const chartData = this.prepareChartData(
                graph.json,
                xAxis,
                yAxis,
                aggregation
            );

            if (!chartData || chartData.length === 0) {
                console.log("No chart data available");
                return {};
            }

            // Sorteer en limiteer data met verbeterde functie
            const sortedData = this.sortChartData(
                chartData,
                xAxis,
                order
            ).slice(0, chartData.length);

            const title = this.generateChartTitle(xAxis, yAxis, aggregation);

            const baseOptions = {
                data: sortedData,

                padding: {
                    top: 20,
                    right: 20,
                    bottom: 20,
                    left: 20,
                },
            };

            const finalConfig = this.generateChartConfig(
                baseOptions,
                chartType,
                xAxis,
                yAxis
            );

            return finalConfig;
        },
        prepareChartData(data, xAxis, yAxis, aggregation) {
            if (!data || data.length === 0) {
                console.warn("Geen data beschikbaar voor chart");
                return [];
            }

            const isXAxisDate = this.isDateField(xAxis, data);

            if (yAxis === "__count") {
                return this.prepareCountData(data, xAxis, isXAxisDate);
            } else {
                return this.prepareAggregatedData(
                    data,
                    xAxis,
                    yAxis,
                    aggregation,
                    isXAxisDate
                );
            }
        },
        prepareCountData(data, xAxis, isXAxisDate) {
            const counts = {};

            data.forEach((item, index) => {
                let category = this.getCategoryValue(
                    item,
                    xAxis,
                    index,
                    isXAxisDate
                );

                if (category !== null) {
                    const categoryKey =
                        category instanceof Date
                            ? category.toISOString()
                            : String(category);
                    counts[categoryKey] = (counts[categoryKey] || 0) + 1;
                }
            });

            // Converteer naar array en sorteer
            let entries = Object.entries(counts);

            if (isXAxisDate) {
                // Sorteer datums chronologisch
                entries.sort(([a], [b]) => new Date(a) - new Date(b));
                return entries.slice(0, 20).map(([categoryKey, count]) => ({
                    category: new Date(categoryKey),
                    value: count,
                }));
            } else {
                // Sorteer op count (hoogste eerst)
                entries.sort(([, a], [, b]) => b - a);
                return entries.slice(0, 20).map(([category, count]) => ({
                    category: category,
                    value: count,
                }));
            }
        },
        prepareAggregatedData(data, xAxis, yAxis, aggregation, isXAxisDate) {
            const groups = {};

            data.forEach((item, index) => {
                let category = this.getCategoryValue(
                    item,
                    xAxis,
                    index,
                    isXAxisDate
                );

                if (category !== null) {
                    const categoryKey =
                        category instanceof Date
                            ? category.toISOString()
                            : String(category);
                    const value = this.parseNumericValue(item[yAxis]);

                    if (value !== null) {
                        if (!groups[categoryKey]) {
                            groups[categoryKey] = [];
                        }
                        groups[categoryKey].push(value);
                    }
                }
            });

            // Converteer naar geaggregeerde data
            let aggregatedData = Object.entries(groups)
                .map(([categoryKey, values]) => {
                    const aggregatedValue = this.aggregateValues(
                        values,
                        aggregation
                    );

                    return {
                        category: isXAxisDate
                            ? new Date(categoryKey)
                            : categoryKey,
                        value: aggregatedValue,
                    };
                })
                .filter(
                    (item) =>
                        item.value !== null &&
                        !isNaN(item.value) &&
                        isFinite(item.value)
                );

            return aggregatedData.slice(0, 20);
        },
        getCategoryValue(item, xAxis, index, isXAxisDate) {
            if (xAxis === "__index") {
                return `Item ${index + 1}`;
            }

            const rawValue = item[xAxis];

            // Controleer op lege waarden
            if (
                rawValue === null ||
                rawValue === undefined ||
                rawValue === ""
            ) {
                return "Onbekend";
            }

            if (isXAxisDate) {
                const dateValue = this.parseDate(rawValue);
                if (dateValue) {
                    return dateValue;
                } else {
                    console.warn("Kon datum niet parsen:", rawValue);
                    return null; // Skip invalid dates
                }
            } else {
                return String(rawValue);
            }
        },
        parseNumericValue(value) {
            if (value === null || value === undefined || value === "") {
                return null;
            }

            // Probeer als getal te parsen
            const numValue = parseFloat(value);

            // Controleer of het een geldig getal is
            if (isNaN(numValue) || !isFinite(numValue)) {
                return null;
            }

            return numValue;
        },
        sortChartData(chartData, xAxis, sortOrder) {
            // Bepaal of het datums zijn door te kijken naar het eerste item
            const isDateData =
                chartData.length > 0 && chartData[0].category instanceof Date;

            switch (sortOrder) {
                case "value_desc":
                    return chartData.sort((a, b) => b.value - a.value);

                case "value_asc":
                    return chartData.sort((a, b) => a.value - b.value);

                case "category_asc":
                    if (isDateData) {
                        return chartData.sort(
                            (a, b) => a.category - b.category
                        );
                    } else {
                        return chartData.sort((a, b) =>
                            String(a.category).localeCompare(
                                String(b.category),
                                "nl"
                            )
                        );
                    }

                case "category_desc":
                    if (isDateData) {
                        return chartData.sort(
                            (a, b) => b.category - a.category
                        );
                    } else {
                        return chartData.sort((a, b) =>
                            String(b.category).localeCompare(
                                String(a.category),
                                "nl"
                            )
                        );
                    }

                case "date_asc":
                    if (isDateData) {
                        return chartData.sort(
                            (a, b) => a.category - b.category
                        );
                    } else {
                        // Probeer alsnog te sorteren op datum als het geen Date object is
                        return chartData.sort((a, b) => {
                            const dateA = this.parseDate(a.category);
                            const dateB = this.parseDate(b.category);
                            if (dateA && dateB) {
                                return dateA - dateB;
                            }
                            return String(a.category).localeCompare(
                                String(b.category),
                                "nl"
                            );
                        });
                    }

                case "date_desc":
                    if (isDateData) {
                        return chartData.sort(
                            (a, b) => b.category - a.category
                        );
                    } else {
                        // Probeer alsnog te sorteren op datum als het geen Date object is
                        return chartData.sort((a, b) => {
                            const dateA = this.parseDate(a.category);
                            const dateB = this.parseDate(b.category);
                            if (dateA && dateB) {
                                return dateB - dateA;
                            }
                            return String(b.category).localeCompare(
                                String(a.category),
                                "nl"
                            );
                        });
                    }

                default:
                    // Fallback naar oude logica
                    if (isDateData) {
                        return chartData.sort(
                            (a, b) => a.category - b.category
                        );
                    } else {
                        return chartData.sort((a, b) => b.value - a.value);
                    }
            }
        },
        aggregateValues(values, aggregation) {
            const validValues = values.filter(
                (v) => v !== null && !isNaN(v) && isFinite(v)
            );

            if (validValues.length === 0) return 0;

            switch (aggregation) {
                case "sum":
                    return validValues.reduce((sum, val) => sum + val, 0);
                case "avg":
                    return (
                        validValues.reduce((sum, val) => sum + val, 0) /
                        validValues.length
                    );
                case "count":
                    return validValues.length;
                case "min":
                    return Math.min(...validValues);
                case "max":
                    return Math.max(...validValues);
                default:
                    return validValues.reduce((sum, val) => sum + val, 0);
            }
        },
        generateChartTitle(xAxis, yAxis, aggregation) {
            const xName = this.getFieldDisplayName(xAxis);
            const yName = this.getFieldDisplayName(yAxis);

            if (yAxis === "__count") {
                return `Aantal per ${xName}`;
            }

            const aggLabel =
                {
                    sum: "Totaal",
                    avg: "Gemiddelde",
                    count: "Aantal",
                    min: "Minimum",
                    max: "Maximum",
                }[aggregation] || "Totaal";

            return `${aggLabel} ${yName} per ${xName}`;
        },
        getFieldDisplayName(fieldName) {
            if (fieldName === "__index") return "Rij Nummer";
            if (fieldName === "__count") return "Aantal";

            // Capitalize first letter and replace underscores
            return fieldName
                .replace(/_/g, " ")
                .replace(/\b\w/g, (l) => l.toUpperCase());
        },
        generateChartConfig(baseOptions, chartType, xAxis, yAxis) {
            const xAxisTitle = this.getFieldDisplayName(xAxis);
            const yAxisTitle = this.getFieldDisplayName(yAxis);

            // Check of x-as datum bevat
            const isXAxisDate =
                baseOptions.data.length > 0 &&
                baseOptions.data[0].category instanceof Date;

            const maxLabelChars = 15;

            const xAxisConfig = {
                type: isXAxisDate ? "time" : "category",
                position: "bottom",
                title: { text: xAxisTitle },
                label: {
                    rotation:
                        !isXAxisDate && baseOptions.data.length > 10 ? -45 : 0,
                    fontSize: 11,
                    format: isXAxisDate ? "%d/%m/%Y" : undefined,
                    formatter: (params) => {
                        let val = params.value;
                        if (
                            typeof val === "string" &&
                            val.length > maxLabelChars
                        ) {
                            return val.slice(0, maxLabelChars) + "…";
                        }
                        return val;
                    },
                },
            };

            const yAxisConfig = {
                type: "number",
                position: "left",
                title: { text: yAxisTitle },
                label: {
                    fontSize: 11,
                },
            };

            const commonAxes = [xAxisConfig, yAxisConfig];

            const tooltipRenderer = ({ datum }) => {
                const xValue = isXAxisDate
                    ? datum.category.toLocaleDateString("nl-NL")
                    : datum.category;

                const yValue =
                    typeof datum.value === "number"
                        ? datum.value.toLocaleString("nl-NL")
                        : datum.value;

                return {
                    content: `${xAxisTitle}: ${xValue}<br/>${yAxisTitle}: ${yValue}`,
                };
            };

            // Basis serie configuratie
            const serieConfig = {
                xKey: "category",
                yKey: "value",
                tooltip: { renderer: tooltipRenderer },
            };

            switch (chartType) {
                case "bar":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "bar",
                                fill: "#3B82F6",
                            },
                        ],
                    };

                case "line":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "line",
                                stroke: "#3B82F6",
                                strokeWidth: 2,
                                marker: {
                                    enabled: true,
                                    fill: "#3B82F6",
                                    size: 6,
                                },
                            },
                        ],
                    };

                case "area":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "area",
                                fill: "rgba(59, 130, 246, 0.3)",
                                stroke: "#3B82F6",
                                strokeWidth: 2,
                            },
                        ],
                    };

                default: // column
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "bar",
                                fill: "#3B82F6",
                            },
                        ],
                    };
            }
        },
        unpinGraph(graphId) {
            this.form.delete(route("conversation.unpinItem", graphId), {
                onSuccess: () => {
                    location.reload();
                },
                onError: (errors) => {},
            });
        },
        duplicateGraph(graph) {
            this.form.post(route("conversation.duplicateItem", graph.id), {
                onSuccess: () => {
                    location.reload();
                },
                onError: (errors) => {},
            });
        },
        startEditingTitle(graph) {
            this.editingTitleId = graph.id;
            this.editingTitle = graph.title;

            this.$nextTick(() => {
                if (this.$refs.titleInput) {
                    this.$refs.titleInput.focus();
                    this.$refs.titleInput.select();
                }
            });
        },
        cancelEditingTitle() {
            this.editingTitleId = null;
            this.editingTitle = "";
        },
        async saveTitle(graphId) {
            if (this.savingTitleId) return;

            const graph = this.pinned_items.find((g) => g.id === graphId);
            if (!graph) return;

            // Als de titel niet is veranderd, gewoon annuleren
            const originalTitle = graph.title || this.getGraphTitle(graph);
            if (this.editingTitle.trim() === originalTitle) {
                this.cancelEditingTitle();
                return;
            }

            this.savingTitleId = graphId;

            try {
                const titleForm = useForm({
                    title: this.editingTitle.trim(),
                });

                await new Promise((resolve, reject) => {
                    titleForm.patch(
                        route("conversation.updateItemTitle", graphId),
                        {
                            onSuccess: () => {
                                // Update de lokale data
                                graph.title = this.editingTitle.trim();
                                this.cancelEditingTitle();
                                resolve();
                            },
                            onError: (errors) => {
                                console.error("Error updating title:", errors);
                                reject(errors);
                            },
                        }
                    );
                });
            } catch (error) {
                console.error("Failed to save title:", error);
            } finally {
                this.savingTitleId = null;
            }
        },
        toggleGraphRefresh(graph) {
            this.selectedGraphRefresh = graph;
            this.selectedSortRefresh = "graph";
            this.showRefreshModal = !this.showRefreshModal;
        },
        async toggleGraphWidth(graph) {
            const newWidth = graph.width === "full" ? "half" : "full";

            try {
                await fetch(route("conversation.updateItemWidth", graph.id), {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify({ width: newWidth }),
                });

                graph.width = newWidth;
            } catch (error) {
                console.error("Fout bij bijwerken breedte:", error);
            }
        },
        async updateGraphOrder(reorderedGraphs) {
            const response = await fetch(route("dashboard.updateGraphOrder"), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                },
                body: JSON.stringify({ graphs: reorderedGraphs }),
            });
            if (!response.ok) {
                throw new Error("Failed to update graph order");
            }

            this.updateCsrfToken(response);
            // // Update local display_order values
            reorderedGraphs.forEach(({ id, display_order }) => {
                const graph = this.pinned_items.find((g) => g.id === id);
                if (graph) {
                    graph.display_order = display_order;
                }
            });
        },
        async makeDefault(dashboard) {
            this.$inertia.post(route("dashboard.makeDefault"), {
                dashboard: dashboard,
            });
        },
        deleteDashboard(dashboard) {
            this.$inertia.delete(route("dashboard.delete"), {
                preserveState: false,
                data: {
                    dashboard: dashboard,
                },
            });
        },
        // SortableJS
        destroySortables() {
            if (this.graphsSortable) {
                this.graphsSortable.destroy();
                this.graphsSortable = null;
            }
        },
        initializeDragAndDrop() {
            this.$nextTick(() => {
                this.initializeGraphsSortable();
            });
        },
        initializeGraphsSortable() {
            const pinnedContainer = this.$refs.pinnedContainer;
            if (pinnedContainer && this.pinned_items.length > 0) {
                this.graphsSortable = Sortable.create(pinnedContainer, {
                    animation: 150,
                    ghostClass: "sortable-ghost",
                    chosenClass: "sortable-chosen",
                    dragClass: "sortable-drag",
                    handle: ".drag-handle",
                    onEnd: (evt) => {
                        this.onGraphReorder(evt);
                    },
                });
            }
        },
        async onGraphReorder(evt) {
            this.reorderItems(evt, this.pinned_items, this.updateGraphOrder);
        },

        toggleDashboardDropdown() {
            this.isDashboardOpen = !this.isDashboardOpen;
        },
        closeDashboardDropdown() {
            this.isDashboardOpen = false;
        },
        selectDashboard(dashboard) {
            this.selectedDashboard = dashboard;
            this.isDashboardOpen = false;

            this.$inertia.visit(route("dashboard", dashboard.guid));
        },
    },
    beforeUnmount() {
        this.destroySortables();
    },
    mounted() {
        this.initializeDragAndDrop();

        if (this.dashboard) {
            this.selectedDashboard = this.dashboard;
        } else if (this.all_dashboards && this.all_dashboards.length > 0) {
            this.selectedDashboard = this.all_dashboards[0];
        }
    },

    watch: {
        pinned_items: {
            handler() {
                this.destroySortables();
                this.initializeDragAndDrop();
            },
            deep: true,
        },

        current_dashboard: {
            handler(newVal) {
                if (newVal) {
                    this.selectedDashboard = newVal;
                }
            },
            immediate: true,
        },
    },
};
</script>

<style scoped>
.sortable-ghost {
    opacity: 0.5;
    background: #f3f4f6;
}

.sortable-chosen {
    cursor: grabbing;
}

.sortable-drag {
    transform: rotate(5deg);
}

.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-graph,
.sortable-table {
    transition: transform 0.2s ease;
}
</style>
