<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <main class="space-y-6 sm:space-y-8 px-4 sm:px-0">
            <!-- Nieuwe conversatie knop -->
            <div class="flex justify-between items-center">
                <h1 class="text-black font-bold text-3xl sm:text-4xl">
                    Dashboard
                </h1>

                <button
                    @click="createConversation()"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium hover:bg-blue-700 transition"
                >
                    + Nieuwe conversatie
                </button>
            </div>

            <!-- Welkomstbericht -->
            <section
                class="text-center text-gray-600 max-w-md mx-auto px-4 sm:px-0"
                v-if="!pinned_graphs?.length"
            >
                <p>
                    Start een nieuwe conversatie door op de knop hierboven te
                    klikken.<br />
                    Je gepinde informatie verschijnt hier.
                </p>
            </section>

            <section v-if="pinned_graphs?.length" class="space-y-4">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div
                        v-for="graph in pinned_graphs"
                        :key="graph.id"
                        class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <!-- Header -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="flex justify-between items-center">
                                <div class="flex-1 mr-3">
                                    <!-- Editable Title -->
                                    <div
                                        v-if="editingTitleId !== graph.id"
                                        @click="startEditingTitle(graph)"
                                        class="group cursor-pointer"
                                        :title="'Klik om titel te bewerken'"
                                    >
                                        <h3
                                            class="text-base font-medium text-gray-900 group-hover:text-blue-600 transition-colors duration-200 flex items-center"
                                        >
                                            {{
                                                graph.title ||
                                                getGraphTitle(graph)
                                            }}
                                            <svg
                                                class="w-4 h-4 ml-2 opacity-0 group-hover:opacity-50 transition-opacity duration-200"
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
                                            @keydown.enter="saveTitle(graph.id)"
                                            @keydown.escape="cancelEditingTitle"
                                            @blur="saveTitle(graph.id)"
                                            class="flex-1 text-lg font-medium text-gray-900 bg-transparent border-blue-500 focus:outline-none focus:border-blue-600 transition-colors"
                                            :placeholder="getGraphTitle(graph)"
                                        />
                                        <div class="flex space-x-1">
                                            <button
                                                @click="saveTitle(graph.id)"
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
                                <button
                                    @click="unpinGraph(graph.id)"
                                    class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
                                    title="Losmaken"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M10 2L3 9h4v7h6V9h4l-7-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Chart -->
                        <div>
                            <div
                                v-if="!getPinnedChartOptions(graph).data"
                                class="text-red-500 text-sm mb-2"
                            >
                                Debug: Geen chart data beschikbaar<br />
                                X-as: {{ graph._x }}<br />
                                Y-as: {{ graph._y }}<br />
                                Chart type: {{ graph.sort_chart }}<br />
                                Data records: {{ graph.json?.length || 0 }}
                            </div>

                            <div
                                class="w-full h-96 bg-white rounded border border-gray-100"
                            >
                                <ag-charts
                                    v-if="
                                        getPinnedChartOptions(graph).data
                                            ?.length
                                    "
                                    class="w-full h-full"
                                    :options="getPinnedChartOptions(graph)"
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
                        </div>

                        <!-- Footer -->
                        <div
                            class="px-4 py-2 bg-gray-50 border-t border-gray-100 rounded-b-lg"
                        >
                            <div
                                class="flex justify-between items-center text-xs text-gray-500"
                            >
                                <span
                                    >{{ graph.json?.length || 0 }} records</span
                                >
                                <span>{{
                                    formatChartType(graph.sort_chart)
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="pinned_tables?.length" class="space-y-4">
                <div class="grid grid-cols-1 xl:grid-cols-1 gap-6">
                    <div
                        v-for="table in pinned_tables"
                        :key="table.id"
                        class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <!-- Header -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <button
                                    @click="unpinTable(table.id)"
                                    class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
                                    title="Losmaken"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M10 2L3 9h4v7h6V9h4l-7-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Chart -->

                        <div class="w-full h-96 bg-white">
                            <ag-grid-vue
                                ref="agGrid"
                                class="ag-theme-alpine w-full h-full"
                                :rowData="getTableRowData(table)"
                                :columnDefs="getTableColumnDefs(table)"
                                :defaultColDef="defaultColDef"
                                :gridOptions="gridOptions"
                                rowSelection="multiple"
                                @grid-ready="onGridReady"
                                @range-selection-changed="
                                    onRangeSelectionChanged
                                "
                            />
                        </div>

                        <!-- Footer -->
                        <div
                            class="px-6 py-3 bg-gray-50 border-t border-gray-100 rounded-b-lg"
                        >
                            <div
                                class="flex justify-between items-center text-xs text-gray-500"
                            >
                                <span
                                    >{{ table.json?.length || 0 }} records</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { format, isToday, parseISO } from "date-fns";
import { AgCharts } from "ag-charts-vue3";
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";

import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise"; // Dit activeert alle enterprise features
import { ModuleRegistry } from "ag-grid-community";
import {
    AllEnterpriseModule,
    LicenseManager,
    IntegratedChartsModule,
} from "ag-grid-enterprise";

ModuleRegistry.registerModules([
    AllEnterpriseModule,
    IntegratedChartsModule.with(AgChartsEnterpriseModule),
]);
LicenseManager.setLicenseKey(import.meta.env.VITE_AG_KEY);

export default {
    components: {
        AuthenticatedLayout,
        Head,
        AgCharts,
        AgGridVue,
    },
    props: {
        pinned_graphs: Array,
        pinned_tables: Array,
    },
    data() {
        return {
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
            localPinnedGraphs: [...this.pinned_graphs],
            editingTitleId: null,
            editingTitle: "",
            savingTitleId: null,
            defaultColDef: {
                sortable: true,
                filter: true,
                resizable: true,
                flex: 1,
                minWidth: 100,
                enableValue: true,
                enableRowGroup: true,
                enablePivot: true,
                chartDataType: "category", // of 'series', 'time', 'excluded'
            },
            gridOptions: {
                rowHeight: 45,
                headerHeight: 45,
                animateRows: true,
                pagination: false,
                paginationPageSize: 1000,
                enableCharts: true,
                enableRangeSelection: true, // Nodig voor charts
                suppressRowClickSelection: true,
                enableRowGroup: true,
                enablePivot: true,
                enableValue: true,
                chartThemes: ["ag-default", "ag-material", "ag-pastel"],
                getContextMenuItems: (params) => {
                    return [
                        "copy",
                        "copyWithHeaders",
                        "separator",
                        "chartRange",
                        "separator",
                        "export",
                    ];
                },
            },
        };
    },
    methods: {
        createConversation() {
            this.form.post(route("conversation.create"), {
                onSuccess: () => {
                    location.reload();
                },
                onError: (errors) => {},
            });
        },

        // Nieuwe methodes voor titel bewerking
        startEditingTitle(graph) {
            this.editingTitleId = graph.id;
            this.editingTitle = graph.title || this.getGraphTitle(graph);

            // Focus de input na een korte delay zodat het element bestaat
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
            if (this.savingTitleId) return; // Voorkom dubbele saves

            const graph = this.pinned_graphs.find((g) => g.id === graphId);
            if (!graph) return;

            // Als de titel niet is veranderd, gewoon annuleren
            const originalTitle = graph.title || this.getGraphTitle(graph);
            if (this.editingTitle.trim() === originalTitle) {
                this.cancelEditingTitle();
                return;
            }

            this.savingTitleId = graphId;

            try {
                // Maak een nieuwe form instance voor de title update
                const titleForm = useForm({
                    title: this.editingTitle.trim(),
                });

                await new Promise((resolve, reject) => {
                    titleForm.patch(
                        route("conversation.updateChartTitle", graphId),
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

        formatDate(dateString) {
            const date = parseISO(dateString + "Z");

            if (isToday(date)) {
                return format(date, "HH:mm");
            } else {
                return format(date, "dd MMM");
            }
        },

        // VERBETERDE DATE PARSING (zoals in de tweede code)
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

        // VERBETERDE DATE FIELD DETECTION (zoals in de tweede code)
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

            if (!xAxis || !yAxis || !graph.json) {
                return {};
            }

            // GEBRUIK VERBETERDE DATA PREPARATIE
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
            const sortedData = this.sortChartData(chartData, xAxis).slice(
                0,
                chartData.length
            );

            const title = this.generateChartTitle(xAxis, yAxis, aggregation);

            const baseOptions = {
                data: sortedData,
                // title: {
                //     text: title,
                //     fontSize: 14,
                //     fontWeight: "bold",
                // },
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

            console.log("Final chart options:", finalConfig);
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

        sortChartData(chartData, xAxis) {
            // Bepaal of het datums zijn door te kijken naar het eerste item
            const isDateData =
                chartData.length > 0 && chartData[0].category instanceof Date;

            if (isDateData) {
                // Sorteer datums chronologisch
                return chartData.sort((a, b) => a.category - b.category);
            } else {
                // Sorteer andere waarden op waarde (hoogste eerst)
                return chartData.sort((a, b) => b.value - a.value);
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
            // Handle unpinning logic - you'll need to implement this based on your backend
            this.form.delete(route("conversation.unpinChart", graphId), {
                onSuccess: () => {
                    const index = this.pinned_graphs.findIndex(
                        (g) => g.id === graphId
                    );
                    if (index !== -1) {
                        this.pinned_graphs.splice(index, 1); // werkt reactief
                    }
                },
                onError: (errors) => {
                    console.log("Error unpinning graph:", errors);
                },
            });
        },

        unpinTable(tableId) {
            this.form.delete(route("conversation.unpinTable", tableId), {
                onSuccess: () => {
                    const index = this.pinned_tables.findIndex(
                        (t) => t.id === tableId
                    );
                    if (index !== -1) {
                        this.pinned_tables.splice(index, 1);
                    }
                },
                onError: (errors) => {
                    console.log("Error unpinning graph:", errors);
                },
            });
        },

        // Table functions
        hasTableData(message) {
            if (message.send_by !== "ai") return false;

            // Controleer eerst of message.json bestaat en niet leeg is
            if (!message.json || Object.keys(message.json).length === 0) {
                return false;
            }

            // Als json bestaat, controleer of het een array is met data
            if (Array.isArray(message.json) && message.json.length > 0) {
                return true;
            }

            // Fallback naar de oude parseTableMessage methode
            const parsed = this.parseTableMessage(message.message);
            return parsed.rows.length > 0;
        },

        getTableRowData(message) {
            if (
                message.json &&
                Array.isArray(message.json) &&
                message.json.length > 0
            ) {
                return message.json;
            }

            // Fallback naar oude methode
            const parsed = this.parseTableMessage(message.message);

            return parsed.rows.map((row) => {
                const obj = {};
                parsed.headers.forEach((header, index) => {
                    obj[header] = row[index] || "";
                });
                return obj;
            });
        },

        getTableColumnDefs(message) {
            if (
                message.json &&
                Array.isArray(message.json) &&
                message.json.length > 0
            ) {
                const firstItem = message.json[0];
                return Object.keys(firstItem).map((key) => {
                    const isNumeric = this.isNumericField(key, message.json);
                    const isDate = this.isDateField(key, message.json);

                    return {
                        field: key,
                        headerName: this.getFieldDisplayName(key),
                        sortable: true,
                        filter: isNumeric
                            ? "agNumberColumnFilter"
                            : "agTextColumnFilter",
                        resizable: true,
                        enableRowGroup: !isNumeric, // Numerieke velden niet grouperen
                        enablePivot: true,
                        enableValue: isNumeric, // Alleen numerieke velden als waarden

                        // Chart configuratie
                        chartDataType: isNumeric
                            ? "series"
                            : isDate
                            ? "time"
                            : "category",

                        // Type-specifieke configuratie
                        ...(isNumeric && {
                            type: "numericColumn",
                            cellClass: "number-cell",
                            aggFunc: "sum",
                        }),

                        ...(isDate && {
                            type: "dateColumn",
                            cellClass: "date-cell",
                        }),

                        menuTabs: [
                            "filterMenuTab",
                            "generalMenuTab",
                            "columnsMenuTab",
                        ],
                        cellRenderer: (params) => {
                            if (
                                typeof params.value === "string" &&
                                params.value.includes("http")
                            ) {
                                return `<a href="${params.value}" target="_blank" class="text-blue-600 hover:underline">${params.value}</a>`;
                            }
                            return params.value;
                        },
                    };
                });
            }

            // Fallback naar oude methode...
            const parsed = this.parseTableMessage(message.message);
            return parsed.headers.map((header) => ({
                field: header,
                headerName: header,
                sortable: true,
                filter: true,
                resizable: true,
                enableRowGroup: true,
                enablePivot: true,
                enableValue: true,
                chartDataType: "category",
                menuTabs: ["filterMenuTab", "generalMenuTab", "columnsMenuTab"],
                cellRenderer: (params) => {
                    if (
                        typeof params.value === "string" &&
                        params.value.includes("http")
                    ) {
                        return `<a href="${params.value}" target="_blank" class="text-blue-600 hover:underline">${params.value}</a>`;
                    }
                    return params.value;
                },
            }));
        },
    },
    mounted() {
        this.localPinnedGraphs = [...this.pinned_graphs];
    },
};
</script>
