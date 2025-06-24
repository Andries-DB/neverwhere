<template>
    <Head title="Bericht detail" />
    <AuthenticatedLayout>
        <div
            class="overflow-y-scroll flex-1 flex flex-col gap-4 relative h-full"
        >
            <div
                class="p-3 rounded-lg shadow-sm relative h"
                :class="{
                    'bg-gray-300 text-gray-900 self-end max-w-[75%]':
                        message.send_by === 'user',
                    'bg-gray-100 text-gray-800 self-start':
                        message.send_by === 'ai' &&
                        !message.displayAsTable &&
                        !message.displayAsChart,
                    'bg-gray-100 text-gray-800 self-start w-full':
                        message.send_by === 'ai' &&
                        (message.displayAsTable || message.displayAsChart),
                }"
            >
                <!-- Toggle tussen tekst, tabel en grafiek -->
                <div
                    v-if="message.send_by === 'ai'"
                    class="mb-2 text-xs flex space-x-2"
                >
                    <button
                        @click="setDisplayMode(message, 'text')"
                        :class="[
                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                            !message.displayAsTable &&
                            !message.displayAsChart &&
                            !message.displayAsQuery
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-white text-blue-600 hover:bg-gray-100',
                        ]"
                    >
                        Tekst
                    </button>
                    <button
                        v-if="hasTableData(message)"
                        @click="setDisplayMode(message, 'table')"
                        :class="[
                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                            message.displayAsTable
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-white text-blue-600 hover:bg-gray-100',
                        ]"
                    >
                        Tabel
                    </button>
                    <button
                        v-if="hasChartData(message)"
                        @click="setDisplayMode(message, 'chart')"
                        :class="[
                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                            message.displayAsChart
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-white text-blue-600 hover:bg-gray-100',
                        ]"
                    >
                        Grafiek
                    </button>
                    <button
                        v-if="message.sql_query"
                        @click="setDisplayMode(message, 'sql_query')"
                        :class="[
                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                            message.displayAsQuery
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-white text-blue-600 hover:bg-gray-100',
                        ]"
                    >
                        <i class="fas fa-database"></i>
                    </button>
                </div>

                <!-- Grafiek weergave met AG Charts -->
                <div v-if="message.displayAsChart" class="relative">
                    <template v-if="message.json">
                        <!-- Chart configuratie paneel -->
                        <div
                            class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Chart type selector -->
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-700 mb-1"
                                    >
                                        Grafiek Type
                                    </label>
                                    <select
                                        v-model="message.selectedChartType"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option
                                            v-for="(
                                                field, key
                                            ) in availableChartTypes"
                                            :key="key"
                                            :value="field.value"
                                        >
                                            {{ field.label }}
                                        </option>
                                    </select>
                                </div>

                                <div
                                    v-if="
                                        message.selectedYAxis &&
                                        message.selectedYAxis !== '__count'
                                    "
                                >
                                    <label
                                        class="block text-xs font-medium text-gray-700 mb-1"
                                    >
                                        Aggregatie
                                    </label>
                                    <select
                                        v-model="message.selectedAggregation"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option
                                            v-for="(
                                                field, key
                                            ) in aggregationTypes"
                                            :key="key"
                                            :value="field.value"
                                        >
                                            {{ field.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- X-axis selector -->
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-700 mb-1"
                                    >
                                        X-as (Categorie)
                                    </label>
                                    <select
                                        v-model="message.selectedXAxis"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Kies X-as veld</option>
                                        <option
                                            v-for="field in getAvailableFields(
                                                message
                                            )"
                                            :key="field.key"
                                            :value="field.key"
                                        >
                                            {{ field.display }}
                                        </option>
                                        <option value="__index">
                                            Rij Nummer
                                        </option>
                                    </select>
                                </div>

                                <!-- Y-axis selector -->
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-700 mb-1"
                                    >
                                        Y-as (Waarde)
                                    </label>
                                    <select
                                        v-model="message.selectedYAxis"
                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Kies Y-as veld</option>
                                        <option
                                            v-for="field in getNumericFields(
                                                message
                                            )"
                                            :key="field.key"
                                            :value="field.key"
                                        >
                                            {{ field.display }}
                                        </option>
                                        <option value="__count">
                                            Aantal (Tel Records)
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Chart preview -->
                        <div
                            v-if="
                                message.selectedXAxis && message.selectedYAxis
                            "
                            class="w-full h-96 bg-white rounded-lg border border-gray-200"
                        >
                            <ag-charts
                                class="w-full h-full"
                                :options="getCustomChartOptions(message)"
                            />
                        </div>

                        <!-- Placeholder when no axes selected -->
                        <div
                            v-else
                            class="w-full h-96 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center"
                        >
                            <div class="text-center text-gray-500">
                                <i class="fas fa-chart-bar text-3xl mb-2"></i>
                                <p class="text-sm">
                                    Selecteer X-as en Y-as om de grafiek te
                                    bekijken
                                </p>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div
                            class="text-red-600 text-sm bg-red-50 p-3 rounded-lg border border-red-200"
                        >
                            Geen data beschikbaar voor grafiek
                        </div>
                    </template>
                </div>

                <!-- Tabel weergave met AG Grid -->
                <div v-else-if="message.displayAsTable">
                    <template v-if="message.json">
                        <!-- AG Grid component met chart ondersteuning -->
                        <div
                            class="w-full bg-white rounded-lg border border-gray-200 overflow-hidden"
                        >
                            <!-- AG Grid -->
                            <div class="h-[75vh]">
                                <ag-grid-vue
                                    ref="agGrid"
                                    class="ag-theme-alpine w-full h-full"
                                    :rowData="getTableRowData(message)"
                                    :columnDefs="getTableColumnDefs(message)"
                                    :defaultColDef="defaultColDef"
                                    :gridOptions="gridOptions"
                                    rowSelection="multiple"
                                    @grid-ready="onGridReady"
                                    @range-selection-changed="
                                        onRangeSelectionChanged
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </div>

                <div
                    v-else-if="message.displayAsQuery"
                    class="bg-transparent text-slate-900 text-xs font-mono rounded-md p-3 overflow-auto mb-3"
                >
                    <p
                        class="text-[10px] text-slate-600 mb-1 uppercase tracking-wide"
                    >
                        SQL Query
                    </p>
                    <pre class="whitespace-pre-wrap break-words">{{
                        message.sql_query
                    }}</pre>
                </div>
                <!-- Normale tekst weergave -->
                <p v-else class="whitespace-pre-wrap">
                    {{ message.message }}
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise"; // Dit activeert alle enterprise features
import { ModuleRegistry } from "ag-grid-community";
import {
    AllEnterpriseModule,
    LicenseManager,
    IntegratedChartsModule,
} from "ag-grid-enterprise";
import { AgCharts } from "ag-charts-vue3";
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";

ModuleRegistry.registerModules([
    AllEnterpriseModule,
    IntegratedChartsModule.with(AgChartsEnterpriseModule),
]);
LicenseManager.setLicenseKey(import.meta.env.VITE_AG_KEY);

export default {
    name: "",
    components: { AuthenticatedLayout, Head, AgGridVue, AgCharts },
    props: { message: Object },
    data() {
        return {
            defaultColDef: {
                sortable: true,
                filter: true,
                resizable: true,
                flex: 1,
                minWidth: 100,
                enableValue: true,
                enableRowGroup: true,
                enablePivot: true,
                chartDataType: "category",
            },
            gridOptions: {
                rowHeight: 45,
                headerHeight: 45,
                animateRows: true,
                pagination: false,
                paginationPageSize: 1000,
                enableCharts: true,
                enableRangeSelection: true,
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
            availableChartTypes: [
                // { value: "column", label: "Kolom" },
                { value: "bar", label: "Balk" },
                { value: "line", label: "Lijn" },
                { value: "area", label: "Vlak" },
            ],
            aggregationTypes: [
                { value: "sum", label: "Som" },
                { value: "avg", label: "Gemiddelde" },
                { value: "count", label: "Aantal" },
                { value: "min", label: "Minimum" },
                { value: "max", label: "Maximum" },
            ],
        };
    },
    methods: {
        // Table methods
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
        parseTableMessage(message) {
            const projectRegex = /^\d+\.\s\*\*(.+?)\*\*/;
            const fieldRegex = /^(.+?):\s(.+)$/;
            const lines = message.split("\n");
            const projects = [];

            let current = null;
            let insideProjects = false;
            let aboveLines = [];
            let belowLines = [];

            const trimmedMessage = message.trim();

            // Fallback 1: Probeer te parsen als JSON-array van objecten
            try {
                const json = JSON.parse(trimmedMessage);
                if (Array.isArray(json) && typeof json[0] === "object") {
                    const headers = Array.from(
                        new Set(json.flatMap((obj) => Object.keys(obj)))
                    );
                    const rows = json.map((obj) =>
                        headers.map((key) => obj[key] ?? "")
                    );
                    return {
                        above: "",
                        headers,
                        rows,
                        below: "",
                    };
                }
            } catch (e) {
                // Geen geldig JSON? Ga verder met reguliere parsing
            }

            // Fallback 2: klassieke genummerde projectnotatie
            for (let i = 0; i < lines.length; i++) {
                const rawLine = lines[i];
                const line = rawLine.trim();

                const projectMatch = line.match(projectRegex);

                if (projectMatch) {
                    if (!insideProjects) insideProjects = true;
                    if (current) projects.push(current);
                    current = { Titel: projectMatch[1] };
                } else if (insideProjects && current) {
                    const fieldMatch = line.match(fieldRegex);
                    if (fieldMatch) {
                        const key = fieldMatch[1].trim();
                        const value = fieldMatch[2].trim();
                        current[key] = value;
                    } else if (
                        line === "" &&
                        i + 1 < lines.length &&
                        lines[i + 1].match(projectRegex)
                    ) {
                        continue;
                    } else if (i === lines.length - 1) {
                        belowLines.push(rawLine);
                    } else if (!lines[i + 1].match(projectRegex)) {
                        belowLines.push(rawLine);
                    }
                } else if (!insideProjects) {
                    aboveLines.push(rawLine);
                } else if (insideProjects && !current) {
                    belowLines.push(rawLine);
                }
            }

            if (current) projects.push(current);

            const headers = Array.from(
                new Set(projects.flatMap((p) => Object.keys(p)))
            );
            const rows = projects.map((p) => headers.map((h) => p[h] || ""));

            if (rows.length === 0) {
                return {
                    above: "",
                    headers: [],
                    rows: [],
                    below: `<span style="color: red;">Wij kunnen hier geen tabel van maken.</span>`,
                };
            }

            return {
                above: aboveLines.join("\n").trim(),
                headers,
                rows,
                below: belowLines.join("\n").trim(),
            };
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

        // Chart methods
        hasChartData(message) {
            if (message.send_by !== "ai") return false;

            // Controleer of er JSON data is
            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return false;
            }

            // Controleer of het eerste object minstens 2 velden heeft
            const firstItem = message.json[0];
            const keys = Object.keys(firstItem);

            // We hebben minstens 2 velden nodig voor een zinvolle chart
            return keys.length >= 2;
        },
        getCustomChartOptions(message) {
            const chartType = message.selectedChartType || "column";
            const xAxis = message.selectedXAxis;
            const yAxis = message.selectedYAxis;
            const aggregation = message.selectedAggregation || "sum";

            if (!xAxis || !yAxis || !message.json) {
                return {};
            }

            // Prepareer data gebaseerd op selecties
            const chartData = this.prepareCustomChartData(
                message.json,
                xAxis,
                yAxis,
                aggregation
            );

            if (!chartData || chartData.length === 0) {
                return {};
            }

            // Sorteer en limiteer data
            const sortedData = chartData
                .sort((a, b) => b.value - a.value)
                .slice(0, 50); // Verhoog limiet naar 50 voor meer detail

            const title = this.generateCustomChartTitle(
                xAxis,
                yAxis,
                aggregation
            );

            const baseOptions = {
                data: sortedData,
                title: {
                    text: title,
                    fontSize: 16,
                    fontWeight: "bold",
                },
                padding: {
                    top: 20,
                    right: 20,
                    bottom: 60,
                    left: 80,
                },
            };

            // Genereer specifieke chart configuratie
            return this.generateCustomChartConfig(
                baseOptions,
                chartType,
                xAxis,
                yAxis
            );
        },
        prepareCustomChartData(data, xAxis, yAxis, aggregation) {
            const isXAxisDate = this.isDateField(xAxis, data);

            if (yAxis === "__count") {
                // Tel items per categorie
                const counts = {};

                data.forEach((item) => {
                    let category;

                    if (xAxis === "__index") {
                        category = `Item ${data.indexOf(item) + 1}`;
                    } else if (isXAxisDate) {
                        const dateValue = this.parseDate(item[xAxis]);
                        category = dateValue
                            ? dateValue.toISOString().split("T")[0]
                            : "Onbekend";
                    } else {
                        category = (item[xAxis] || "Onbekend").toString();
                    }

                    counts[category] = (counts[category] || 0) + 1;
                });

                let entries = Object.entries(counts);

                // Sorteer datums chronologisch, andere waarden op count
                if (isXAxisDate) {
                    entries.sort(([a], [b]) => new Date(a) - new Date(b));
                } else {
                    entries.sort(([, a], [, b]) => b - a);
                }

                return entries.slice(0, 20).map(([category, count]) => ({
                    category: isXAxisDate ? new Date(category) : category,
                    value: count,
                }));
            } else {
                // Groepeer data op X-as en aggregeer Y-as waarden
                const groups = {};

                data.forEach((item) => {
                    let category;

                    if (xAxis === "__index") {
                        category = `Item ${data.indexOf(item) + 1}`;
                    } else if (isXAxisDate) {
                        const dateValue = this.parseDate(item[xAxis]);
                        category = dateValue
                            ? dateValue.toISOString().split("T")[0]
                            : "Onbekend";
                    } else {
                        category = (item[xAxis] || "Onbekend").toString();
                    }

                    const value = parseFloat(item[yAxis]) || 0;

                    if (!groups[category]) {
                        groups[category] = [];
                    }
                    groups[category].push(value);
                });

                let aggregatedData = Object.entries(groups)
                    .map(([category, values]) => ({
                        category: isXAxisDate ? new Date(category) : category,
                        value: this.aggregateValues(values, aggregation),
                    }))
                    .filter(
                        (item) => item.value !== null && !isNaN(item.value)
                    );

                // Sorteer datums chronologisch, andere waarden op waarde
                if (isXAxisDate) {
                    aggregatedData.sort((a, b) => a.category - b.category);
                } else {
                    aggregatedData.sort((a, b) => b.value - a.value);
                }

                return aggregatedData.slice(0, 20);
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
        generateCustomChartTitle(xAxis, yAxis, aggregation) {
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
        generateCustomChartConfig(baseOptions, chartType, xAxis, yAxis) {
            const xAxisTitle = this.getFieldDisplayName(xAxis);
            const yAxisTitle = this.getFieldDisplayName(yAxis);

            // Check of x-as datum bevat
            const isXAxisDate =
                baseOptions.data.length > 0 &&
                baseOptions.data[0].category instanceof Date;

            const xAxisConfig = {
                type: isXAxisDate ? "time" : "category",
                position: "bottom",
                title: { text: xAxisTitle },
                label: {
                    rotation:
                        !isXAxisDate && baseOptions.data.length > 10 ? -45 : 0,
                    fontSize: 11,
                    format: isXAxisDate ? "%d/%m/%Y" : undefined,
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

                return {
                    content: `${xAxisTitle}: ${xValue}<br/>${yAxisTitle}: ${datum.value.toLocaleString()}`,
                };
            };

            switch (chartType) {
                case "bar":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                type: "bar",
                                xKey: "category",
                                yKey: "value",
                                fill: "#3B82F6",
                                tooltip: { renderer: tooltipRenderer },
                            },
                        ],
                    };

                case "line":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                type: "line",
                                xKey: "category",
                                yKey: "value",
                                stroke: "#3B82F6",
                                strokeWidth: 2,
                                marker: {
                                    enabled: true,
                                    fill: "#3B82F6",
                                    size: 6,
                                },
                                tooltip: { renderer: tooltipRenderer },
                            },
                        ],
                    };

                case "area":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                type: "area",
                                xKey: "category",
                                yKey: "value",
                                fill: "rgba(59, 130, 246, 0.3)",
                                stroke: "#3B82F6",
                                strokeWidth: 2,
                                tooltip: { renderer: tooltipRenderer },
                            },
                        ],
                    };

                default: // column
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                type: "bar",
                                xKey: "category",
                                yKey: "value",
                                fill: "#3B82F6",
                                tooltip: { renderer: tooltipRenderer },
                            },
                        ],
                    };
            }
        },
        getChartOptions(message) {
            const chartType = message.selectedChartType || "bar";

            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return {};
            }

            // Analyseer en aggregeer de data voor zinvolle visualisaties
            const aggregatedOptions = this.createMeaningfulChartOptions(
                message.json,
                chartType
            );

            if (!aggregatedOptions) {
                return {};
            }

            return aggregatedOptions;
        },

        // Switch methods
        setDisplayMode(message, mode) {
            message.displayAsTable = mode === "table";
            message.displayAsChart = mode === "chart";
            message.displayAsQuery = mode === "sql_query";

            // Stel default axes in als ze nog niet zijn ingesteld
            if (
                mode === "chart" &&
                (!message.selectedXAxis || !message.selectedYAxis)
            ) {
                const fields = this.getAvailableFields(message);
                const numericFields = this.getNumericFields(message);

                // Probeer slimme defaults te kiezen
                if (!message.selectedXAxis && fields.length > 0) {
                    // Zoek een geschikt categorisch veld
                    const categoricalField = fields.find(
                        (f) => !numericFields.some((nf) => nf.key === f.key)
                    );
                    message.selectedXAxis = categoricalField
                        ? categoricalField.key
                        : fields[0].key;
                }

                if (!message.selectedYAxis && numericFields.length > 0) {
                    message.selectedYAxis = numericFields[0].key;
                } else if (!message.selectedYAxis) {
                    message.selectedYAxis = "__count";
                }
            }
        },
        getAvailableFields(message) {
            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return [];
            }

            const firstItem = message.json[0];
            return Object.keys(firstItem).map((key) => ({
                key: key,
                display: this.getFieldDisplayName(key),
            }));
        },
        getNumericFields(message) {
            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return [];
            }

            const data = message.json;
            const firstItem = data[0];
            const numericFields = [];

            Object.keys(firstItem).forEach((key) => {
                // Check of het veld numerieke waarden bevat
                const hasNumericValues = data.some((item) => {
                    const value = item[key];
                    return (
                        value !== null &&
                        value !== undefined &&
                        !isNaN(parseFloat(value)) &&
                        isFinite(value)
                    );
                });

                if (hasNumericValues) {
                    numericFields.push({
                        key: key,
                        display: this.getFieldDisplayName(key),
                    });
                }
            });

            return numericFields;
        },

        // Helpers
        getFieldDisplayName(fieldName) {
            // Verbeterde field name mapping
            const displayNames = {
                // Algemene velden
                amount: "Bedrag (€)",
                quantity: "Aantal",
                count: "Aantal",
                __count: "Aantal",
                __index: "Item",

                // Tijd gerelateerd
                date: "Datum",
                time: "Tijd",
                datum: "Datum",
                tijd: "Tijd",
                created_at: "Aangemaakt op",
                updated_at: "Bijgewerkt op",

                // Uren en tijd
                hours: "Uren",
                uren: "Uren",
                totaal_uren: "Totaal Uren",
                total_hours: "Totaal Uren",

                // Personen
                employee: "Medewerker",
                medewerker: "Medewerker",
                user: "Gebruiker",
                customer: "Klant",
                klant: "Klant",

                // Project gerelateerd
                project: "Project",
                task: "Taak",
                taak: "Taak",
                description: "Beschrijving",
                beschrijving: "Beschrijving",
                projectdescription: "Project Beschrijving",

                // Status en type
                status: "Status",
                type: "Type",
                category: "Categorie",
                categorie: "Categorie",

                // Financieel
                price: "Prijs (€)",
                cost: "Kosten (€)",
                revenue: "Omzet (€)",
                profit: "Winst (€)",
            };

            // Zoek eerst exacte match
            const lowerFieldName = fieldName.toLowerCase();
            if (displayNames[lowerFieldName]) {
                return displayNames[lowerFieldName];
            }

            // Zoek gedeeltelijke matches
            for (const [key, value] of Object.entries(displayNames)) {
                if (
                    lowerFieldName.includes(key) ||
                    key.includes(lowerFieldName)
                ) {
                    return value;
                }
            }

            // Fallback: maak fieldname mooier
            return fieldName
                .replace(/_/g, " ")
                .replace(/([A-Z])/g, " $1")
                .split(" ")
                .map(
                    (word) =>
                        word.charAt(0).toUpperCase() +
                        word.slice(1).toLowerCase()
                )
                .join(" ")
                .trim();
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
        isDateField(fieldName, data) {
            if (!data || data.length === 0) return false;

            // Check eerste paar records om te zien of het veld datum-achtige waarden bevat
            const sampleValues = data
                .slice(0, 5)
                .map((item) => item[fieldName])
                .filter((val) => val != null);

            if (sampleValues.length === 0) return false;

            return sampleValues.some((value) => {
                if (!value) return false;

                // Check verschillende datum formaten
                const str = value.toString();

                // ISO datum format (YYYY-MM-DD of YYYY-MM-DDTHH:mm:ss)
                if (/^\d{4}-\d{2}-\d{2}/.test(str)) return true;

                // DD/MM/YYYY of MM/DD/YYYY
                if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(str)) return true;

                // DD-MM-YYYY of MM-DD-YYYY
                if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(str)) return true;

                // Probeer Date parsing
                const parsed = new Date(str);
                return !isNaN(parsed.getTime()) && parsed.getFullYear() > 1900;
            });
        },
        parseDate(value) {
            if (!value) return null;

            const str = value.toString();

            // Probeer verschillende formaten
            let date = new Date(str);

            // Als standaard parsing faalt, probeer DD/MM/YYYY
            if (
                isNaN(date.getTime()) &&
                /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(str)
            ) {
                const parts = str.split("/");
                // Assumeer DD/MM/YYYY (Europees formaat)
                date = new Date(parts[2], parts[1] - 1, parts[0]);
            }

            // Als nog steeds niet geldig, probeer DD-MM-YYYY
            if (isNaN(date.getTime()) && /^\d{1,2}-\d{1,2}-\d{4}$/.test(str)) {
                const parts = str.split("-");
                if (parts[2].length === 4) {
                    // DD-MM-YYYY
                    date = new Date(parts[2], parts[1] - 1, parts[0]);
                }
            }

            return isNaN(date.getTime()) ? null : date;
        },
    },
    computed: {},
    mounted() {
        // Alleen nodig als de message afkomstig is van de AI
        if (this.message.send_by === "ai") {
            this.message.displayAsTable = this.message.respond_type === "Table";
            this.message.displayAsChart = this.message.respond_type === "Chart";
            this.message.displayAsQuery = this.message.respond_type === "Query";
            this.message.selectedChartType = "bar";
            this.message.selectedXAxis = null;
            this.message.selectedYAxis = null;
            this.message.selectedAggregation = "sum";
        }
    },
};
</script>
