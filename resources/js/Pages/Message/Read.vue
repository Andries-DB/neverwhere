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
                        v-if="message.respond_type !== 'Table'"
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
                    <ChartBuilder :message="message" />
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
                            <div
                                class="pt-3 text-xs ml-2 text-gray-600 border-t border-gray-200"
                            >
                                {{ message.json.length }}
                                records
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
import ChartBuilder from "@/Components/ChartBuilder.vue";

ModuleRegistry.registerModules([
    AllEnterpriseModule,
    IntegratedChartsModule.with(AgChartsEnterpriseModule),
]);
LicenseManager.setLicenseKey(import.meta.env.VITE_AG_KEY);

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        AgGridVue,
        AgCharts,
        ChartBuilder,
    },
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

            // Controleer of het eerste object minstens 1 veld heeft (we kunnen altijd count gebruiken)
            const firstItem = message.json[0];
            const keys = Object.keys(firstItem);

            return keys.length >= 1;
        },
        togglePinChart(message) {
            this.clickedMessageId = message.id;

            if (this.isChartPinned(message)) {
                this.unpinChart(message);
            } else {
                this.pinChart(message);
            }

            setTimeout(() => {
                this.clickedMessageId = null;
            }, 300);
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

                /^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}/, // 12/01/2023

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
            const sortedData = this.sortChartData(chartData, xAxis).slice(
                0,
                50
            );

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
                navigator: {
                    enabled: true,
                    mask: {
                        fill: "rgba(59, 130, 246, 0.1)",
                        stroke: "#3B82F6",
                        strokeWidth: 1,
                    },
                },
            };

            return this.generateCustomChartConfig(
                baseOptions,
                chartType,
                xAxis,
                yAxis
            );
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
        prepareCustomChartData(data, xAxis, yAxis, aggregation) {
            if (!data || data.length === 0) {
                console.warn("Geen data beschikbaar voor chart");
                return [];
            }

            const isXAxisDate = this.isDateField(xAxis, data);

            console.log("Chart data preparatie:", {
                xAxis,
                yAxis,
                aggregation,
                isXAxisDate,
                dataLength: data.length,
            });

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

            const maxLabelChars = 15;

            const xAxisConfig = {
                type: isXAxisDate ? "time" : "category",
                position: "bottom",
                title: { text: xAxisTitle },
                label: {
                    rotation:
                        !isXAxisDate && baseOptions.data.length > 10 ? -45 : 0,
                    fontSize: 9,
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
        generateChartTitle(categoryField, valueField) {
            const categoryName = this.getFieldDisplayName(categoryField);
            const valueName = this.getFieldDisplayName(valueField);

            if (valueField === "__count") {
                return `Aantal per ${categoryName}`;
            } else if (categoryField === "__index") {
                return `${valueName} per Item`;
            } else {
                return `${valueName} per ${categoryName}`;
            }
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
                const fields = this.getAllFields(message);
                const numericFields = this.getAllFields(message);

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
        getAllFields(message) {
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
