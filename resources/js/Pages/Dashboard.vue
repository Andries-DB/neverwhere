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
                    Je recente gesprekken verschijnen hier.
                </p>
            </section>

            <section v-if="pinned_graphs?.length" class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    Vastgepinde Grafieken
                </h2>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div
                        v-for="graph in pinned_graphs"
                        :key="graph.id"
                        class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{
                                            graph.title || getGraphTitle(graph)
                                        }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ getGraphSubtitle(graph) }}
                                    </p>
                                </div>
                                <button
                                    @click="unpinGraph(graph.id)"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
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
                        <div class="p-6">
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
                                class="w-full h-64 bg-white rounded border border-gray-100"
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
                            class="px-6 py-3 bg-gray-50 border-t border-gray-100 rounded-b-lg"
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
        </main>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { format, isToday, parseISO } from "date-fns";
import { AgCharts } from "ag-charts-vue3";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        AgCharts,
    },
    props: {
        pinned_graphs: Array,
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

        formatDate(dateString) {
            const date = parseISO(dateString + "Z");

            if (isToday(date)) {
                return format(date, "HH:mm");
            } else {
                return format(date, "dd MMM");
            }
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

            // Prepareer data gebaseerd op de pinned graph configuratie
            const chartData = this.preparePinnedChartData(
                graph.json,
                xAxis,
                yAxis,
                aggregation
            );

            if (!chartData || chartData.length === 0) {
                console.log("No chart data available");
                return {};
            }

            // Sorteer en limiteer data
            const sortedData = chartData
                .sort((a, b) => b.value - a.value)
                .slice(0, 50);

            const title = this.generatePinnedChartTitle(
                xAxis,
                yAxis,
                aggregation
            );

            const baseOptions = {
                data: sortedData,
                title: {
                    text: title,
                    fontSize: 14,
                    fontWeight: "bold",
                },
                padding: {
                    top: 20,
                    right: 20,
                    bottom: 60,
                    left: 20,
                },
            };

            const finalConfig = this.generatePinnedChartConfig(
                baseOptions,
                chartType,
                xAxis,
                yAxis
            );

            return finalConfig;
        },

        // Voeg deze nieuwe helper methode toe aan je methods object:

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

        // Update je preparePinnedChartData methode:
        preparePinnedChartData(data, xAxis, yAxis, aggregation) {
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

        generatePinnedChartTitle(xAxis, yAxis, aggregation) {
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

        generatePinnedChartConfig(baseOptions, chartType, xAxis, yAxis) {
            const xAxisTitle = this.getFieldDisplayName(xAxis);
            const yAxisTitle = this.getFieldDisplayName(yAxis);

            const commonAxes = [
                {
                    type: "category",
                    position: "bottom",
                    title: { text: xAxisTitle },
                    label: {
                        rotation: baseOptions.data.length > 10 ? -45 : 0,
                        fontSize: 11,
                    },
                },
                {
                    type: "number",
                    position: "left",
                    title: { text: yAxisTitle },
                    label: {
                        fontSize: 11,
                    },
                },
            ];

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
                                tooltip: {
                                    renderer: ({ datum }) => ({
                                        content: `${xAxisTitle}: ${
                                            datum.category
                                        }<br/>${yAxisTitle}: ${datum.value.toLocaleString()}`,
                                    }),
                                },
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
                                tooltip: {
                                    renderer: ({ datum }) => ({
                                        content: `${xAxisTitle}: ${
                                            datum.category
                                        }<br/>${yAxisTitle}: ${datum.value.toLocaleString()}`,
                                    }),
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
                                type: "area",
                                xKey: "category",
                                yKey: "value",
                                fill: "rgba(59, 130, 246, 0.3)",
                                stroke: "#3B82F6",
                                strokeWidth: 2,
                                tooltip: {
                                    renderer: ({ datum }) => ({
                                        content: `${xAxisTitle}: ${
                                            datum.category
                                        }<br/>${yAxisTitle}: ${datum.value.toLocaleString()}`,
                                    }),
                                },
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
                                tooltip: {
                                    renderer: ({ datum }) => ({
                                        content: `${xAxisTitle}: ${
                                            datum.category
                                        }<br/>${yAxisTitle}: ${datum.value.toLocaleString()}`,
                                    }),
                                },
                            },
                        ],
                    };
            }
        },

        getFieldDisplayName(fieldName) {
            if (fieldName === "__index") return "Rij Nummer";
            if (fieldName === "__count") return "Aantal";

            // Capitalize first letter and replace underscores
            return fieldName
                .replace(/_/g, " ")
                .replace(/\b\w/g, (l) => l.toUpperCase());
        },
        generatePinnedChartConfig(baseOptions, chartType, xAxis, yAxis) {
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

        unpinGraph(graphId) {
            // Handle unpinning logic - you'll need to implement this based on your backend
            this.form.delete(route("conversation.unpinChart", graphId), {
                onSuccess: () => {
                    this.pinned_graphs = this.pinned_graphs.filter(
                        (g) => g.id !== graphId
                    );
                },
                onError: (errors) => {
                    console.log("Error unpinning graph:", errors);
                },
            });
        },
    },
    computed: {
        numberOfSkeletons() {
            return Math.max(0, 3 - (this.conversations?.length || 0));
        },
    },
};
</script>
