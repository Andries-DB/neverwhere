<template>
    <div v-if="message.json">
        <!-- Configuratiepaneel -->
        <div
            class="p-3 bg-gray-50 rounded-t-lg border border-gray-200"
            v-if="this.sort !== 'pinned'"
        >
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- <div v-if="!message.config">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.type") }}
                    </label>
                    <select
                        v-model="message.selectedChartType"
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="(field, key) in chartTypes"
                            :key="key"
                            :value="field.value"
                        >
                            {{ field.label }}
                        </option>
                    </select>
                </div>

                <div v-if="!message.config">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.agg") }}
                    </label>
                    <select
                        v-model="message.selectedAggregation"
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="(field, key) in aggregationTypes"
                            :key="key"
                            :value="field.value"
                        >
                            {{ field.label }}
                        </option>
                    </select>
                </div>

                <div v-if="!message.config">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.xAxis") }}
                    </label>
                    <select
                        v-model="message.selectedXAxis"
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">{{ $t("chart.choose_x") }}</option>
                        <option
                            v-for="field in getAllFields(message)"
                            :key="field.key"
                            :value="field.key"
                        >
                            {{ field.display }}
                        </option>
                    </select>
                </div>

                <div v-if="!message.config">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.yAxis") }}
                    </label>
                    <select
                        v-model="message.selectedYAxis"
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">{{ $t("chart.choose_y") }}</option>
                        <option
                            v-for="field in getAllFields(message)"
                            :key="field.key"
                            :value="field.key"
                        >
                            {{ field.display }}
                        </option>
                    </select>
                </div> -->

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.sort") }}
                    </label>
                    <select
                        v-model="message.selectedSortField"
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option
                            v-for="field in getAllFields(message)"
                            :key="field.key"
                            :value="field.key"
                        >
                            {{ field.display }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ $t("chart.order") }}
                    </label>
                    <button
                        type="button"
                        @click="
                            message.selectedSortDirection =
                                message.selectedSortDirection === 'asc'
                                    ? 'desc'
                                    : 'asc'
                        "
                        :disabled="!message.selectedSortField"
                        class="w-fit px-3 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    >
                        {{
                            message.selectedSortDirection === "asc"
                                ? "A → Z"
                                : "Z → A"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Chart Preview -->
        <div class="w-full h-96 bg-white rounded-lg border-0">
            <ag-charts
                class="w-full h-full"
                :options="getCustomChartOptions(message)"
            />
        </div>
    </div>
</template>

<script>
import { AgCharts } from "ag-charts-vue3";
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";
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
    name: "ChartBuilder",
    props: {
        message: {
            type: Object,
            required: true,
        },
        sort: {
            type: String,
            default: "normal",
        },
        config: {
            required: false,
        },
        item: {
            required: false,
        },
    },
    components: {
        AgCharts,
    },
    data() {
        return {
            chartTypes: [
                { value: "bar", label: this.$t("chart.charttypes.bar") },
                { value: "line", label: this.$t("chart.charttypes.line") },
                { value: "area", label: this.$t("chart.charttypes.area") },
                { value: "pie", label: this.$t("chart.charttypes.pie") },
            ],
            aggregationTypes: [
                { value: "sum", label: this.$t("chart.aggtypes.sum") },
                { value: "avg", label: this.$t("chart.aggtypes.avg") },
                { value: "count", label: this.$t("chart.aggtypes.count") },
                { value: "min", label: this.$t("chart.aggtypes.min") },
                { value: "max", label: this.$t("chart.aggtypes.max") },
            ],
        };
    },
    methods: {
        getCustomChartOptions(message) {
            let chartType = "";
            let xAxis = "";
            let yAxis = "";
            let aggregation = "";
            let sortField = "";
            let sortDirection = "";

            if (this.sort === "pinned") {
                chartType = this.item.sort_chart;
                xAxis = this.item._x;
                yAxis = this.item._y;
                aggregation = this.item._agg;
                sortField = this.item._order;
                sortDirection = this.item._order_dir;
            } else {
                chartType = message.selectedChartType || "column";
                xAxis = message.selectedXAxis;
                yAxis = message.selectedYAxis;
                aggregation = message.selectedAggregation || "sum";
                sortField = message.selectedSortField || "";
                sortDirection = message.selectedSortDirection || "desc";
            }

            // Parse existing config if available
            let existingConfig = null;

            const parseConfig = (configString) => {
                try {
                    const parsed = JSON.parse(configString);

                    if (
                        parsed.Graph &&
                        Array.isArray(parsed.Graph) &&
                        parsed.Graph[0]
                    ) {
                        if (parsed.Graph[0]["0"]) {
                            return parsed.Graph[0]["0"];
                        }
                        return parsed.Graph[0];
                    }

                    if (parsed.data || parsed.series) {
                        return parsed;
                    }
                    return null;
                } catch (e) {
                    console.error("Kon config niet parsen:", e);
                    return null;
                }
            };
            if (this.config) {
                existingConfig = parseConfig(this.config);
            } else if (message.config) {
                existingConfig = parseConfig(message.config);
            }

            // Extract axes from existing config
            if (existingConfig) {
                // Voor pie charts
                if (existingConfig.series?.[0]?.type === "pie") {
                    xAxis =
                        existingConfig.series[0].calloutLabelKey ||
                        existingConfig.series[0].labelKey;
                    yAxis = existingConfig.series[0].angleKey;
                }
                // Voor normale charts
                else if (existingConfig.series?.[0]) {
                    const firstSeries = existingConfig.series[0];

                    if (Array.isArray(firstSeries.xKey)) {
                        xAxis = firstSeries.xKey;
                    } else {
                        xAxis = firstSeries.xKey;
                    }

                    if (existingConfig.series.length > 1) {
                        yAxis = existingConfig.series.map((s) => s.yKey);
                    } else {
                        yAxis = firstSeries.yKey;
                    }
                }
            }

            // Prepareer data
            const chartData = this.prepareCustomChartData(
                message.json,
                xAxis,
                yAxis,
                aggregation,
                existingConfig
            );

            if (!chartData || chartData.length === 0) {
                return {};
            }

            // Sorteer data
            let sortedData = this.sortChartData(
                chartData.data,
                sortField,
                sortDirection,
                xAxis,
                yAxis
            );

            sortedData = this.transformDataToOutputKeys(
                sortedData,
                chartData.keys
            );

            if (existingConfig) {
                if (existingConfig.series) {
                    const series = existingConfig.series[0];

                    // label formatter
                    if (
                        series.label?.formatter &&
                        typeof series.label.formatter === "string"
                    ) {
                        const formatterString = series.label.formatter;
                        series.label.formatter = new Function(
                            "params",
                            `return (${formatterString})(params);`
                        );
                    }

                    // groupedKeys dataFilter
                    if (series.groupedKeys) {
                        series.groupedKeys.forEach((key) => {
                            if (typeof key.dataFilter === "string") {
                                key.dataFilter = new Function(
                                    "params",
                                    `return (${key.dataFilter})(params);`
                                );
                            }
                        });
                    }
                }

                return {
                    ...existingConfig,
                    data: sortedData,
                };
            }

            // Genereer nieuwe config
            return this.generateNewChartConfig(
                sortedData,
                chartType,
                xAxis,
                yAxis,
                aggregation
            );
        },
        async SaveGraphState() {
            try {
                const data = {
                    message_guid: this.message.guid,
                    data: {
                        _sort: this.message.selectedChartType,
                        _agg: this.message.selectedAggregation,
                        _x: this.message.selectedXAxis,
                        _y: this.message.selectedYAxis,
                        _order: this.message.selectedSortField,
                        _order_dir: this.message.selectedSortDirection,
                    },
                };

                const response = await fetch("/conversation/savechartdef", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    console.log(response);
                }

                this.updateCsrfToken(response);

                const result = await response.json();

                return {
                    success: true,
                    data: result.summary,
                };
            } catch (e) {
                return {
                    success: false,
                };
            }
        },
        getCsrfToken() {
            return document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
        },
        updateCsrfToken(response) {
            const newToken = response.headers.get("X-CSRF-TOKEN");
            if (newToken) {
                document
                    .querySelector('meta[name="csrf-token"]')
                    .setAttribute("content", newToken);
            }
        },
        parseDate(value) {
            if (!value) return null;

            const dateStr = String(value).trim();
            if (!dateStr || dateStr === "null" || dateStr === "undefined")
                return null;

            // Probeer verschillende datum formaten
            let date = null;

            // ISO datum met timezone (2023-12-05T00:00:00.000Z)
            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(dateStr)) {
                date = new Date(dateStr);
            }
            // Eenvoudige datum (2025-07-03)
            else if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                // Parse als lokale datum (voorkom timezone issues)
                const parts = dateStr.split("-");
                date = new Date(
                    parseInt(parts[0]),
                    parseInt(parts[1]) - 1,
                    parseInt(parts[2])
                );
            }
            // Andere formaten
            else if (/^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}/.test(dateStr)) {
                date = new Date(dateStr);
            }
            // Timestamp formaten
            else if (/^\d{13}$/.test(dateStr)) {
                date = new Date(parseInt(dateStr));
            } else if (/^\d{10}$/.test(dateStr)) {
                date = new Date(parseInt(dateStr) * 1000);
            }

            if (!date || isNaN(date.getTime())) return null;

            const year = date.getFullYear();
            if (year < 1900 || year > 2100) return null;

            return date;
        },
        formatDate(date) {
            if (!date || !(date instanceof Date) || isNaN(date.getTime()))
                return null;

            // Nederlandse datum formatting: DD-MM-YYYY (gebruik - in plaats van / voor consistentie)
            const day = date.getDate().toString().padStart(2, "0");
            const month = (date.getMonth() + 1).toString().padStart(2, "0");
            const year = date.getFullYear();

            return `${day}-${month}-${year}`;
        },
        formatDisplayValue(value) {
            // Als het een Date object is
            if (value instanceof Date && !isNaN(value)) {
                return this.formatDate(value);
            }

            // Als het een string is die eruitziet als een datum
            if (typeof value === "string") {
                const dateValue = this.parseDate(value);
                if (dateValue) {
                    return this.formatDate(dateValue);
                }
            }

            // Voor getallen, gebruik Nederlandse formatting
            if (typeof value === "number") {
                return value.toLocaleString("nl-NL");
            }

            // Anders gewoon als string returnen
            return String(value);
        },
        isDateField(fieldName, data) {
            if (!data || data.length === 0) return false;

            // If fieldName is an array, it's not a date field
            if (Array.isArray(fieldName)) return false;

            const sampleSize = Math.min(10, data.length);
            let dateCount = 0;

            for (let i = 0; i < sampleSize; i++) {
                const value = data[i][fieldName];
                if (this.parseDate(value)) {
                    dateCount++;
                }
            }

            return dateCount / sampleSize > 0.7;
        },

        sortChartData(data, sortField, sortDirection, xAxis, yAxis) {
            if (!sortField) {
                return data;
            }

            const getFieldValue = (item, field) => {
                const value = item[field];
                return value;
            };

            return [...data].sort((a, b) => {
                const aVal = getFieldValue(a, sortField);
                const bVal = getFieldValue(b, sortField);

                if (typeof aVal === "number" && typeof bVal === "number") {
                    const result =
                        sortDirection === "asc" ? aVal - bVal : bVal - aVal;

                    // Tiebreaker: gebruik originele index
                    return result !== 0
                        ? result
                        : a._originalIndex - b._originalIndex;
                }

                if (aVal instanceof Date && bVal instanceof Date) {
                    return sortDirection === "asc"
                        ? aVal.getTime() - bVal.getTime()
                        : bVal.getTime() - aVal.getTime();
                }

                // String comparison
                const aStr = String(aVal).toLowerCase();
                const bStr = String(bVal).toLowerCase();

                if (sortDirection === "asc") {
                    return aStr.localeCompare(bStr);
                } else {
                    return bStr.localeCompare(aStr);
                }
            });
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
        sortChartDataByField(
            chartData,
            originalData,
            xAxis,
            yAxis,
            sortField,
            sortDirection
        ) {
            const fieldValueMap = new Map();

            if (originalData && originalData.length > 0) {
                originalData.forEach((item, index) => {
                    let category = this.getCategoryValue(
                        item,
                        xAxis,
                        index,
                        this.isDateField(xAxis, originalData)
                    );

                    if (category !== null) {
                        const categoryKey =
                            category instanceof Date
                                ? category.toISOString()
                                : String(category);

                        const fieldValue = item[sortField];

                        // Als we nog geen waarde hebben voor deze categorie, of deze waarde is "beter"
                        if (!fieldValueMap.has(categoryKey)) {
                            fieldValueMap.set(categoryKey, fieldValue);
                        }
                    }
                });
            }

            // Helper functie om jaar-maand strings te sorteren
            const isYearMonthFormat = (str) => {
                return typeof str === "string" && /^\d{4}-\d{1,2}$/.test(str);
            };

            const compareYearMonth = (a, b) => {
                const parseYearMonth = (str) => {
                    const [year, month] = str.split("-").map(Number);
                    return year * 100 + month; // 2025-8 wordt 202508, 2024-12 wordt 202412
                };

                const valueA = parseYearMonth(a);
                const valueB = parseYearMonth(b);

                return valueA - valueB; // Altijd ascending van laag naar hoog
            };

            // Nu sorteren we chartData gebaseerd op de veldwaarden
            return chartData.sort((a, b) => {
                const categoryKeyA =
                    a.category instanceof Date
                        ? a.category.toISOString()
                        : String(a.category);
                const categoryKeyB =
                    b.category instanceof Date
                        ? b.category.toISOString()
                        : String(b.category);

                const valueA = fieldValueMap.get(categoryKeyA);
                const valueB = fieldValueMap.get(categoryKeyB);

                // Check of het jaar-maand formaten zijn
                if (
                    isYearMonthFormat(String(valueA)) &&
                    isYearMonthFormat(String(valueB))
                ) {
                    const comparison = compareYearMonth(
                        String(valueA),
                        String(valueB)
                    );
                    return sortDirection === "asc" ? comparison : -comparison;
                }

                // Check of het datums zijn
                const dateA = this.parseDate(valueA);
                const dateB = this.parseDate(valueB);

                if (dateA && dateB) {
                    return sortDirection === "asc"
                        ? dateA - dateB
                        : dateB - dateA;
                }

                // Check of het getallen zijn
                const numA = parseFloat(valueA);
                const numB = parseFloat(valueB);

                if (!isNaN(numA) && !isNaN(numB)) {
                    return sortDirection === "asc" ? numA - numB : numB - numA;
                }

                // Anders als tekst behandelen
                const strA = String(valueA || "");
                const strB = String(valueB || "");

                return sortDirection === "asc"
                    ? strA.localeCompare(strB, "nl")
                    : strB.localeCompare(strA, "nl");
            });
        },
        generateNewChartConfig(data, chartType, xAxis, yAxis, aggregation) {
            const title = this.generateCustomChartTitle(
                xAxis,
                yAxis,
                aggregation
            );

            // Bepaal de keys uit de data
            const sampleItem = data[0] || {};
            const keys = Object.keys(sampleItem);
            const xKey = keys[0] || "category";
            const yKey = keys[1] || "value";

            // Create baseOptions equivalent
            const baseOptions = {
                data: data.map((item) => ({
                    category: item[xKey],
                    value: item[yKey],
                })),
                title: {
                    text: title,
                    fontSize: 16,
                    fontWeight: "bold",
                },
                padding: {
                    top: 20,
                    right: 20,
                    bottom: 20,
                    left: 20,
                },
                navigator: {
                    enabled: chartType !== "pie",
                    mask: {
                        fill: "rgba(59, 130, 246, 0.1)",
                        stroke: "#3B82F6",
                        strokeWidth: 1,
                    },
                },
            };
            const xAxisTitle = this.getFieldDisplayName
                ? this.getFieldDisplayName(xAxis)
                : xAxis;
            const yAxisTitle = this.getFieldDisplayName
                ? this.getFieldDisplayName(
                      yAxis === "__count" ? "Count" : yAxis
                  )
                : yAxis === "__count"
                ? "Count"
                : yAxis;

            // Check of x-as datum bevat
            const isXAxisDate =
                baseOptions.data.length > 0 &&
                baseOptions.data[0].category instanceof Date;

            // Pie chart heeft andere configuratie
            if (chartType === "pie") {
                return this.generatePieChartConfig(
                    baseOptions,
                    xAxisTitle,
                    yAxisTitle
                );
            }

            const maxLabelChars = 15;

            const xAxisConfig = {
                type: isXAxisDate ? "time" : "category",
                position: "bottom",
                title: { text: xAxisTitle },
                label: {
                    rotation:
                        !isXAxisDate && baseOptions.data.length > 10 ? -45 : 0,
                    fontSize: 9,
                    formatter: (params) => {
                        let val = params.value;

                        // Voor datum formatting
                        if (isXAxisDate) {
                            return this.formatDate ? this.formatDate(val) : val;
                        }

                        // Anders gewoon tekst afkappen
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

            // Verbeterde tooltip renderer
            const tooltipRenderer = ({ datum }) => {
                let xValue;

                // Zorg ervoor dat datum correct wordt getoond in tooltip
                if (isXAxisDate && datum.category instanceof Date) {
                    xValue = this.formatDate
                        ? this.formatDate(datum.category)
                        : datum.category;
                } else {
                    xValue = this.formatDisplayValue
                        ? this.formatDisplayValue(datum.category)
                        : datum.category;
                }

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
                case "column":
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "bar",
                                fill: this.message?._color || "#3B82F6",
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
                                stroke: this.message?._color || "#3B82F6",
                                strokeWidth: 2,
                                marker: {
                                    enabled: true,
                                    fill: this.message?._color || "#3B82F6",
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
                                stroke: this.message?._color || "#3B82F6",
                                strokeWidth: 2,
                            },
                        ],
                    };

                default:
                    return {
                        ...baseOptions,
                        axes: commonAxes,
                        series: [
                            {
                                ...serieConfig,
                                type: "bar",
                                fill: this.message?._color || "#3B82F6",
                            },
                        ],
                    };
            }
        },
        prepareCustomChartData(data, xAxis, yAxis, aggregation, config = null) {
            if (!data || data.length === 0) {
                console.warn("Geen data beschikbaar voor chart");
                return [];
            }

            // Check if this is a pie chart from config
            const isPieChart =
                config?.type === "pie" || config?.series?.[0]?.type === "pie";

            if (isPieChart) {
                return this.preparePieChartData(
                    data,
                    xAxis,
                    yAxis,
                    aggregation,
                    config
                );
            }

            // Bepaal de gewenste output keys op basis van config of gebruik defaults
            const outputKeys = this.getOutputKeysFromConfig(
                config,
                xAxis,
                yAxis
            );

            // BELANGRIJKE FIX: Transformeer de data direct naar het juiste formaat
            // zodat transformDataToOutputKeys de juiste properties kan vinden
            const processedData = data.map((item, index) => ({
                ...item,
                categoryValue: item[xAxis], // Voor de x-as waarde
                aggregatedValue: item[yAxis], // Voor de y-as waarde
                _originalIndex: index, // Voor sorting tiebreaker
            }));

            return {
                keys: outputKeys,
                data: processedData,
            };
        },
        preparePieChartData(data, xAxis, yAxis, aggregation, config) {
            const isXAxisDate = this.isDateField(xAxis, data);

            // Voor pie charts aggregeren we altijd
            let processedData;
            if (yAxis === "__count") {
                processedData = this.prepareCountDataNew(
                    data,
                    xAxis,
                    isXAxisDate
                );
            } else {
                processedData = this.prepareAggregatedDataNew(
                    data,
                    xAxis,
                    yAxis,
                    aggregation,
                    isXAxisDate
                );
            }

            const angleKey = config?.series?.[0]?.angleKey || "value";
            const labelKey = config?.series?.[0]?.labelKey || "category";

            return processedData.map((item) => ({
                [labelKey]: item.categoryValue,
                [angleKey]: item.aggregatedValue,
            }));
        },
        getOutputKeysFromConfig(config, xAxis, yAxis) {
            // Check for pie chart
            const isPieChart =
                config?.type === "pie" || config?.series?.[0]?.type === "pie";

            if (isPieChart && config?.series?.[0]) {
                return {
                    labelKey: config.series[0].labelKey,
                    angleKey: config.series[0].angleKey,
                };
            }

            if (config?.series && config.series.length > 0) {
                return {
                    xKey: config.series[0].xKey,
                    yKey: config.series.map((s) => s.yKey),
                };
            }

            return {
                xKey: Array.isArray(xAxis)
                    ? xAxis
                          .map((field) => this.sanitizeKeyName(field))
                          .filter(Boolean)
                    : this.sanitizeKeyName(xAxis) || "category",
                yKey: Array.isArray(yAxis)
                    ? yAxis
                          .map((field) => this.sanitizeKeyName(field))
                          .filter(Boolean)
                    : this.sanitizeKeyName(yAxis) || "value",
            };
        },
        sanitizeKeyName(fieldName) {
            if (!fieldName || fieldName === "__count") return null;

            return fieldName
                .toLowerCase()
                .replace(/[^a-z0-9]/g, "_")
                .replace(/^_+|_+$/g, "")
                .replace(/_+/g, "_");
        },
        prepareCountDataNew(data, xAxis, isXAxisDate) {
            const counts = new Map();

            data.forEach((item, index) => {
                const category = this.getCategoryValue(
                    item,
                    xAxis,
                    index,
                    isXAxisDate
                );

                if (category !== null) {
                    const categoryKey = this.getCategoryKey(category);
                    counts.set(categoryKey, (counts.get(categoryKey) || 0) + 1);
                }
            });

            return Array.from(counts.entries()).map(([categoryKey, count]) => ({
                categoryValue: this.parseCategoryKey(categoryKey, isXAxisDate),
                aggregatedValue: count,
            }));
        },
        prepareAggregatedDataNew(data, xAxis, yAxis, aggregation, isXAxisDate) {
            const groups = new Map();

            data.forEach((item, index) => {
                const category = this.getCategoryValue(
                    item,
                    xAxis,
                    index,
                    isXAxisDate
                );
                const numericValue = this.parseNumericValue(item[yAxis]);

                if (category !== null && numericValue !== null) {
                    const categoryKey = this.getCategoryKey(category);

                    if (!groups.has(categoryKey)) {
                        groups.set(categoryKey, []);
                    }
                    groups.get(categoryKey).push(numericValue);
                }
            });

            return Array.from(groups.entries())
                .map(([categoryKey, values]) => ({
                    categoryValue: this.parseCategoryKey(
                        categoryKey,
                        isXAxisDate
                    ),
                    aggregatedValue: this.aggregateValues(values, aggregation),
                }))
                .filter(
                    (item) =>
                        item.aggregatedValue !== null &&
                        !isNaN(item.aggregatedValue) &&
                        isFinite(item.aggregatedValue)
                );
        },
        getCategoryValue(item, xAxis, index, isXAxisDate) {
            if (xAxis === "__index") {
                return `Item ${index + 1}`;
            }

            // Handle array of fields for multi-field categories
            if (Array.isArray(xAxis)) {
                const values = xAxis.map((field) => {
                    const rawValue = item[field];
                    if (
                        rawValue === null ||
                        rawValue === undefined ||
                        rawValue === ""
                    ) {
                        return "Onbekend";
                    }
                    return String(rawValue);
                });
                return values.join(", ");
            }

            const rawValue = item[xAxis];

            // Rest of existing logic...
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
                    return null;
                }
            } else {
                return String(rawValue);
            }
        },
        parseCategoryKey(categoryKey, isXAxisDate) {
            return isXAxisDate ? new Date(categoryKey) : categoryKey;
        },
        transformDataToOutputKeys(processedData, outputKeys) {
            return processedData.map((item) => {
                const result = {};

                // Handle xKey - check of de property bestaat in het item
                if (Array.isArray(outputKeys.xKey)) {
                    outputKeys.xKey.forEach((field) => {
                        result[field] =
                            item[field] !== undefined
                                ? item[field]
                                : item.categoryValue
                                ? item.categoryValue.split(", ")[
                                      outputKeys.xKey.indexOf(field)
                                  ]
                                : null;
                    });
                } else {
                    result[outputKeys.xKey] =
                        item[outputKeys.xKey] !== undefined
                            ? item[outputKeys.xKey]
                            : item.categoryValue;
                }

                // Handle yKey - check of de property bestaat in het item
                if (Array.isArray(outputKeys.yKey)) {
                    outputKeys.yKey.forEach((field) => {
                        result[field] =
                            item[field] !== undefined
                                ? item[field]
                                : item.aggregatedValues
                                ? item.aggregatedValues[field]
                                : 0;
                    });
                } else {
                    result[outputKeys.yKey] =
                        item[outputKeys.yKey] !== undefined
                            ? item[outputKeys.yKey]
                            : item.aggregatedValue !== undefined
                            ? item.aggregatedValue
                            : 0;
                }

                return result;
            });
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

            // Converteer naar array (sorting wordt later gedaan)
            return Object.entries(counts).map(([categoryKey, count]) => ({
                category: isXAxisDate ? new Date(categoryKey) : categoryKey,
                value: count,
            }));
        },
        getCategoryKey(category) {
            return category instanceof Date
                ? category.toISOString()
                : String(category);
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

            // Converteer naar geaggregeerde data (sorting wordt later gedaan)
            return Object.entries(groups)
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
                    return dateValue; // Return het Date object zelf
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
        generatePieChartConfig(baseOptions, xAxisTitle, yAxisTitle) {
            // Colorschema voor pie chart
            const colors = [
                "#3B82F6",
                "#EF4444",
                "#10B981",
                "#F59E0B",
                "#8B5CF6",
                "#EC4899",
                "#06B6D4",
                "#84CC16",
                "#F97316",
                "#6366F1",
            ];

            const tooltipRenderer = ({ datum }) => {
                const percentage = (
                    (datum.value /
                        baseOptions.data.reduce(
                            (sum, item) => sum + item.value,
                            0
                        )) *
                    100
                ).toFixed(1);
                let categoryValue;
                if (datum.category instanceof Date) {
                    categoryValue = this.formatDate(datum.category);
                } else {
                    const parsedDate = this.parseDate(datum.category);

                    if (parsedDate) {
                        categoryValue = this.formatDate(parsedDate);
                    } else {
                        categoryValue = String(datum.category);
                    }
                }

                const value =
                    typeof datum.value === "number"
                        ? datum.value.toLocaleString("nl-NL")
                        : datum.value;

                return {
                    content: `${categoryValue}<br/>${yAxisTitle}: ${value} (${percentage}%)`,
                };
            };

            return {
                ...baseOptions,
                series: [
                    {
                        type: "pie",
                        angleKey: "value",
                        calloutLabelKey: "category",
                        sectorLabelKey: "value",
                        fills: colors,
                        tooltip: { renderer: tooltipRenderer },
                        calloutLabel: {
                            enabled: true,
                            fontSize: 11,
                            formatter: (params) => {
                                const maxLength = 20;
                                let formattedValue;

                                // Zorg voor consistente datum formatting in pie chart labels
                                if (params.value instanceof Date) {
                                    formattedValue = this.formatDate(
                                        params.value
                                    );
                                } else {
                                    // Probeer eerst of het een datum string is
                                    const parsedDate = this.parseDate(
                                        params.value
                                    );
                                    if (parsedDate) {
                                        formattedValue =
                                            this.formatDate(parsedDate);
                                    } else {
                                        formattedValue = String(params.value);
                                    }
                                }

                                return formattedValue.length > maxLength
                                    ? formattedValue.slice(0, maxLength) + "..."
                                    : formattedValue;
                            },
                        },
                        sectorLabel: {
                            enabled: true,
                            fontSize: 10,
                            formatter: (params) => {
                                const total = baseOptions.data.reduce(
                                    (sum, item) => sum + item.value,
                                    0
                                );
                                const percentage = (
                                    (params.datum.value / total) *
                                    100
                                ).toFixed(1);
                                return percentage > 5 ? `${percentage}%` : "";
                            },
                        },
                    },
                ],
            };
        },
        getFieldDisplayName(fieldName) {
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
    },
    mounted() {
        if (!this.message) {
            this.message = {};
        }
        if (!this.message.selectedSortField) {
            this.message.selectedSortField = "";
        }
        if (!this.message.selectedSortDirection) {
            this.message.selectedSortDirection = "desc";
        }
    },
};
</script>
