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
                                : $t("dashboard.select")
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
                                {{ $t("labels.noresults") }}
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
                                {{ $t("dashboard.create") }}
                            </button>
                            <button
                                @click="toggleExportDashbpard()"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors duration-150 flex items-center"
                            >
                                <i class="far fa-file-pdf mr-[14px]"></i>

                                {{ $t("dashboard.export") }}
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
                                {{ $t("dashboard.default") }}
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
                                {{ $t("dashboard.delete") }}
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
                    {{ $t("dashboard.welcome") }}
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
                                <div
                                    class="relative"
                                    v-click-outside="
                                        () => {
                                            this.openDropdown = null;
                                        }
                                    "
                                >
                                    <button
                                        @click.stop="toggleDropdown(item.id)"
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
                                            {{ $t("dashboard.edit") }}
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
                                                    ? $t("dashboard.small")
                                                    : $t("dashboard.big")
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
                                            <span>
                                                {{
                                                    $t("dashboard.refresh")
                                                }}</span
                                            >
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
                                            {{ $t("dashboard.duplicate") }}
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
                                            {{ $t("dashboard.delete_pin") }}
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
                                <ChartBuilder
                                    :message="item.message"
                                    :config="item.config"
                                    sort="pinned"
                                    :item="item"
                                />
                            </div>

                            <div
                                class="w-full h-96 bg-white"
                                v-if="item.type === 'table'"
                            >
                                <TableBuilder
                                    :message="item.message"
                                    sort="pinned"
                                    :col_def="item.col_def"
                                    :total_row="item.total_row"
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
                                <div class="">
                                    <span
                                        >{{
                                            item.json?.length || 0
                                        }}
                                        records</span
                                    >

                                    <!-- <span v-if="item.type == 'graph'">{{
                                        formatChartType(item?.sort_chart)
                                    }}</span> -->
                                </div>

                                <div class="flex gap-2 items-end">
                                    <small class="text-gray-400 ml-2">
                                        Laatst geüpdatet:
                                        {{ formatDateTime(item?.last_updated) }}
                                    </small>
                                </div>
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
import ChartBuilder from "@/Components/ChartBuilder.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        AgCharts,
        RefreshGraph,
        AddDashboard,
        TableBuilder,
        ChartBuilder,
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
        formatDateTime(value) {
            if (!value) return "-";
            const date = new Date(value + " UTC"); // forceren dat het UTC is
            return date.toLocaleString("nl-BE", {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
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
