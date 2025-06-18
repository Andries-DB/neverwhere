<template>
    <Head :title="conversation.title ?? 'Nieuwe conversatie'" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="items-stretch gap-5 flex flex-col md:flex-row">
            <div
                class="main-nav w-full text-black flex flex-col items-center rounded-md"
            >
                <div
                    class="xl:h-[75vh] lg:h-[550px] md:h-[500px] h-[68vh] w-full pb-5 flex flex-col"
                >
                    <div
                        ref="messagecontainer"
                        class="overflow-y-scroll flex-1 p-3 xl:max-h-[73vh] lg:max-h-[500px] md:max-h-[475px] max-h-[65vh] flex flex-col gap-4"
                    >
                        <template
                            v-for="(message, index) in messages"
                            :key="message.id"
                        >
                            <div
                                v-if="shouldDisplayDate(index, messages)"
                                class="w-full text-center text-xs text-gray-500 mb-2"
                            >
                                {{ formatDate(message.created_at) }}
                            </div>

                            <div
                                class="p-3 rounded-lg shadow-sm"
                                :class="{
                                    'bg-gray-300 text-gray-900 self-end max-w-[75%]':
                                        message.send_by === 'user',
                                    'bg-gray-100 text-gray-800 self-start':
                                        message.send_by === 'ai' &&
                                        !message.displayAsTable &&
                                        !message.displayAsChart,
                                    'bg-gray-100 text-gray-800 self-start  w-full':
                                        message.send_by === 'ai' &&
                                        (message.displayAsTable ||
                                            message.displayAsChart),
                                }"
                            >
                                <p
                                    v-if="message.send_by === 'user'"
                                    class="text-xs text-gray-600 mb-1 font-semibold"
                                >
                                    Jij:
                                </p>
                                <p
                                    v-else
                                    class="text-xs text-gray-700 mb-1 font-semibold"
                                >
                                    Neverwhere bot
                                </p>

                                <!-- Toggle tussen tekst, tabel en grafiek -->
                                <div
                                    v-if="
                                        message.send_by === 'ai' &&
                                        (hasTableData(message) ||
                                            hasChartData(message))
                                    "
                                    class="mb-2 text-xs flex space-x-2"
                                >
                                    <button
                                        @click="setDisplayMode(message, 'text')"
                                        :class="[
                                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                                            !message.displayAsTable &&
                                            !message.displayAsChart
                                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                                : 'bg-white text-blue-600 hover:bg-gray-100',
                                        ]"
                                    >
                                        Tekst
                                    </button>
                                    <button
                                        v-if="hasTableData(message)"
                                        @click="
                                            setDisplayMode(message, 'table')
                                        "
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
                                        @click="
                                            setDisplayMode(message, 'chart')
                                        "
                                        :class="[
                                            'px-3 py-1 rounded-md shadow-sm transition-colors duration-200',
                                            message.displayAsChart
                                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                                : 'bg-white text-blue-600 hover:bg-gray-100',
                                        ]"
                                    >
                                        Grafiek
                                    </button>
                                </div>

                                <!-- Grafiek weergave met AG Charts -->
                                <div v-if="message.displayAsChart">
                                    <p
                                        v-if="
                                            parseTableMessage(message.message)
                                                .above
                                        "
                                        class="mb-3 text-sm leading-relaxed"
                                    >
                                        {{
                                            parseTableMessage(message.message)
                                                .above
                                        }}
                                    </p>

                                    <template v-if="message.json">
                                        <!-- Chart configuratie paneel -->
                                        <div
                                            class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200"
                                        >
                                            <div
                                                class="grid grid-cols-1 md:grid-cols-3 gap-4"
                                            >
                                                <!-- Chart type selector -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1"
                                                    >
                                                        Grafiek Type
                                                    </label>
                                                    <div
                                                        class="flex flex-wrap gap-1"
                                                    >
                                                        <button
                                                            v-for="chartType in availableChartTypes"
                                                            :key="
                                                                chartType.value
                                                            "
                                                            @click="
                                                                message.selectedChartType =
                                                                    chartType.value
                                                            "
                                                            :class="[
                                                                'px-2 py-1 text-xs rounded border transition-colors',
                                                                (message.selectedChartType ||
                                                                    'column') ===
                                                                chartType.value
                                                                    ? 'bg-blue-100 border-blue-300 text-blue-700'
                                                                    : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50',
                                                            ]"
                                                        >
                                                            {{
                                                                chartType.label
                                                            }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- X-axis selector -->
                                                <div>
                                                    <label
                                                        class="block text-xs font-medium text-gray-700 mb-1"
                                                    >
                                                        X-as (Categorie)
                                                    </label>
                                                    <select
                                                        v-model="
                                                            message.selectedXAxis
                                                        "
                                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    >
                                                        <option value="">
                                                            Kies X-as veld
                                                        </option>
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
                                                        v-model="
                                                            message.selectedYAxis
                                                        "
                                                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                    >
                                                        <option value="">
                                                            Kies Y-as veld
                                                        </option>
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

                                            <!-- Aggregatie opties voor numerieke Y-as -->
                                            <div
                                                v-if="
                                                    message.selectedYAxis &&
                                                    message.selectedYAxis !==
                                                        '__count'
                                                "
                                                class="mt-3"
                                            >
                                                <label
                                                    class="block text-xs font-medium text-gray-700 mb-1"
                                                >
                                                    Aggregatie
                                                </label>
                                                <div class="flex gap-2">
                                                    <button
                                                        v-for="aggType in aggregationTypes"
                                                        :key="aggType.value"
                                                        @click="
                                                            message.selectedAggregation =
                                                                aggType.value
                                                        "
                                                        :class="[
                                                            'px-2 py-1 text-xs rounded border transition-colors',
                                                            (message.selectedAggregation ||
                                                                'sum') ===
                                                            aggType.value
                                                                ? 'bg-green-100 border-green-300 text-green-700'
                                                                : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50',
                                                        ]"
                                                    >
                                                        {{ aggType.label }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chart preview -->
                                        <div
                                            v-if="
                                                message.selectedXAxis &&
                                                message.selectedYAxis
                                            "
                                            class="w-full h-96 bg-white rounded-lg border border-gray-200"
                                        >
                                            <ag-charts
                                                class="w-full h-full"
                                                :options="
                                                    getCustomChartOptions(
                                                        message
                                                    )
                                                "
                                            />
                                        </div>

                                        <!-- Placeholder when no axes selected -->
                                        <div
                                            v-else
                                            class="w-full h-96 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center"
                                        >
                                            <div
                                                class="text-center text-gray-500"
                                            >
                                                <i
                                                    class="fas fa-chart-bar text-3xl mb-2"
                                                ></i>
                                                <p class="text-sm">
                                                    Selecteer X-as en Y-as om de
                                                    grafiek te bekijken
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
                                        <!-- AG Grid component -->
                                        <div
                                            class="w-[100%] h-96 bg-white rounded-lg border border-gray-200"
                                        >
                                            <ag-grid-vue
                                                class="ag-theme-alpine w-full h-full"
                                                :rowData="
                                                    getTableRowData(message)
                                                "
                                                :columnDefs="
                                                    getTableColumnDefs(message)
                                                "
                                                :defaultColDef="defaultColDef"
                                                :gridOptions="gridOptions"
                                                :enableCharts="true"
                                            />
                                        </div>
                                    </template>
                                </div>

                                <!-- Normale tekst weergave -->
                                <p v-else class="whitespace-pre-wrap">
                                    {{ message.message }}
                                </p>

                                <p
                                    :class="[
                                        'text-[10px] mt-1',
                                        message.user_id ===
                                        $page.props.auth.user.id
                                            ? 'text-gray-500 text-right'
                                            : 'text-gray-600',
                                    ]"
                                >
                                    {{ formatTime(message.created_at) }}
                                </p>
                            </div>
                        </template>

                        <div
                            class="p-3 rounded-lg max-w-[60%] bg-gray-100 text-gray-600 self-start flex flex-col shadow-sm"
                            v-if="this.loading"
                        >
                            <p class="text-xs font-semibold mb-3 select-none">
                                Neverwhere bot
                            </p>
                            <div class="flex space-x-1">
                                <span
                                    class="w-2 h-2 bg-gray-400 rounded-full typing-dot"
                                ></span>
                                <span
                                    class="w-2 h-2 bg-gray-400 rounded-full typing-dot delay-200"
                                ></span>
                                <span
                                    class="w-2 h-2 bg-gray-400 rounded-full typing-dot delay-400"
                                ></span>
                            </div>
                        </div>

                        <div
                            v-if="this.error"
                            class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded text-sm w-fit max-w-full mx-auto"
                        >
                            {{ this.error }}
                        </div>
                    </div>
                </div>

                <div
                    class="flex mflex-col justify-center items-center w-full gap-4 px-2 pt-3"
                >
                    <!-- Source selection and message input row -->
                    <div class="flex flex-col md:flex-row gap-3 w-full">
                        <!-- Source selector -->
                        <div class="flex-shrink-0 md:w-48">
                            <label class="block text-xs text-gray-600 mb-1"
                                >Bron</label
                            >
                            <select
                                v-model="form.source"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-transparent focus:ring-none focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option disabled value="x">
                                    Selecteer een bron
                                </option>
                                <option
                                    v-for="(source, index) in conversation.user
                                        .sources"
                                    :key="index"
                                    :value="source"
                                >
                                    {{ source.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Message input with send button -->
                        <div class="flex-1">
                            <label class="block text-xs text-gray-600 mb-1"
                                >Bericht</label
                            >
                            <div
                                class="flex h-10 rounded-md w-full border border-gray-300 overflow-hidden focus-within:ring-none focus-within:ring-blue-500 focus-within:border-blue-500 transition-all"
                            >
                                <input
                                    type="text"
                                    class="flex-1 bg-transparent border-none focus:ring-0 focus:outline-none placeholder:text-gray-500 px-3 text-sm"
                                    placeholder="Stuur een bericht..."
                                    v-model="form.message"
                                    @keyup.enter="sendMessage"
                                />
                                <button
                                    class="px-4 flex justify-center items-center bg-blue-500 hover:bg-blue-600 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="sendMessage"
                                    :disabled="
                                        !form.message.trim() ||
                                        form.source === 'x'
                                    "
                                    aria-label="Send message"
                                >
                                    <i class="fas fa-paper-plane text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Validation message -->
                    <transition name="fade">
                        <div v-if="no_message" class="w-full">
                            <div
                                class="bg-red-50 border border-red-200 rounded-md p-3 flex items-center gap-2"
                            >
                                <i
                                    class="fas fa-exclamation-circle text-red-500 text-sm"
                                ></i>
                                <span class="text-red-700 text-sm"
                                    >Please fill in a message</span
                                >
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { AgGridVue } from "ag-grid-vue3";
import { AgCharts } from "ag-charts-vue3";
import { ref } from "vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        AgGridVue,
        AgCharts,
    },
    props: {
        conversation: Object,
    },
    data() {
        return {
            loading: false,
            form: useForm({
                message: "",
                conversation: this.conversation,
                source: "x",
            }),
            error: "",
            no_message: false,
            messages: [],
            breadcrumbs: [
                { title: "Dashboard", href: "/dashboard" },
                { title: "Conversaties", href: "#" },
                {
                    title: this.conversation.title ?? "Nieuw gesprek",
                    href: "/conversations/" + this.conversation.guid,
                },
            ],
            // AG Grid configuratie
            defaultColDef: {
                sortable: true,
                filter: true,
                resizable: true,
                flex: 1,
                minWidth: 100,
            },
            gridOptions: {
                rowHeight: 45,
                headerHeight: 45,
                animateRows: true,
                pagination: false,
                paginationPageSize: 1000,
                suppressRowClickSelection: true,
            },
            // Chart types
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
        hasChartData(message) {
            if (message.send_by !== "ai") return false;

            // Controleer eerst of message.json bestaat en niet leeg is
            if (!message.json || Object.keys(message.json).length === 0) {
                return false;
            }

            // Als json bestaat en het is een array, controleer voor visualiseerbare data
            if (Array.isArray(message.json) && message.json.length > 0) {
                return this.hasVisualizableData(message.json);
            }

            return false;
        },
        setDisplayMode(message, mode) {
            message.displayAsTable = mode === "table";
            message.displayAsChart = mode === "chart";

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
            if (yAxis === "__count") {
                // Tel items per categorie
                const counts = {};
                data.forEach((item) => {
                    const category =
                        xAxis === "__index"
                            ? `Item ${data.indexOf(item) + 1}`
                            : (item[xAxis] || "Onbekend").toString();
                    counts[category] = (counts[category] || 0) + 1;
                });

                return Object.entries(counts).map(([category, count]) => ({
                    category: category,
                    value: count,
                }));
            } else {
                // Groepeer data op X-as en aggregeer Y-as waarden
                const groups = {};

                data.forEach((item) => {
                    const category =
                        xAxis === "__index"
                            ? `Item ${data.indexOf(item) + 1}`
                            : (item[xAxis] || "Onbekend").toString();

                    const value = parseFloat(item[yAxis]) || 0;

                    if (!groups[category]) {
                        groups[category] = [];
                    }
                    groups[category].push(value);
                });

                // Aggregeer de waarden per groep
                return Object.entries(groups)
                    .map(([category, values]) => ({
                        category: category,
                        value: this.aggregateValues(values, aggregation),
                    }))
                    .filter(
                        (item) => item.value !== null && !isNaN(item.value)
                    );
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

        // Genereer custom chart titel
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

        // Genereer custom chart configuratie
        generateCustomChartConfig(baseOptions, chartType, xAxis, yAxis) {
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
        // Hoofdmethode om zinvolle chart opties te genereren
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
        // Helper methodes
        getFieldDisplayName(fieldName) {
            const displayNames = {
                amount: "Bedrag (€)",
                quantity: "Uren",
                date: "Datum",
                employee: "Medewerker",
                customer: "Klant",
                task: "Taak",
                projectdescription: "Project",
                count: "Aantal",
            };

            return displayNames[fieldName] || fieldName;
        },
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

        getChartOptions(message) {
            const chartType = message.selectedChartType || "bar";

            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return {};
            }

            const data = message.json;
            const firstItem = data[0];
            const keys = Object.keys(firstItem);

            // Zoek de beste combinatie van velden voor de chart
            const { categoryField, valueField } = this.getBestFieldCombination(
                data,
                keys
            );

            if (!categoryField || !valueField) {
                return {};
            }

            // Prepareer de data voor de chart
            const chartData = this.prepareChartData(
                data,
                categoryField,
                valueField
            );

            // Genereer chart opties gebaseerd op het type
            return this.generateChartOptions(
                chartData,
                categoryField,
                valueField,
                chartType
            );
        },

        getBestFieldCombination(data, keys) {
            let categoryField = null;
            let valueField = null;

            // Zoek numerieke velden (voor Y-as)
            const numericFields = keys.filter((key) => {
                return data.some((item) => {
                    const value = item[key];
                    return (
                        value !== null &&
                        value !== undefined &&
                        !isNaN(parseFloat(value)) &&
                        isFinite(value) &&
                        parseFloat(value) !== 0
                    );
                });
            });

            // Zoek categorische velden (voor X-as)
            const categoricalFields = keys.filter((key) => {
                return (
                    !numericFields.includes(key) &&
                    data.some(
                        (item) =>
                            item[key] && item[key].toString().trim() !== ""
                    )
                );
            });

            // Kies de beste velden
            if (numericFields.length > 0) {
                valueField = numericFields[0]; // Eerste numerieke veld

                if (categoricalFields.length > 0) {
                    categoryField = categoricalFields[0]; // Eerste categorische veld
                } else {
                    // Als er geen categorisch veld is, gebruik index
                    categoryField = "__index";
                }
            } else {
                // Als er geen numerieke velden zijn, tel de items per categorie
                if (categoricalFields.length > 0) {
                    categoryField = categoricalFields[0];
                    valueField = "__count";
                }
            }

            return { categoryField, valueField };
        },

        prepareChartData(data, categoryField, valueField) {
            if (valueField === "__count") {
                // Tel items per categorie
                const counts = {};
                data.forEach((item) => {
                    const category = item[categoryField] || "Onbekend";
                    counts[category] = (counts[category] || 0) + 1;
                });

                return Object.entries(counts).map(([category, count]) => ({
                    category: category,
                    value: count,
                }));
            } else if (categoryField === "__index") {
                // Gebruik index als categorie
                return data
                    .map((item, index) => ({
                        category: `Item ${index + 1}`,
                        value: parseFloat(item[valueField]) || 0,
                    }))
                    .filter((item) => item.value > 0);
            } else {
                // Normale data mapping
                return data
                    .map((item) => {
                        const category = item[categoryField] || "Onbekend";
                        const value = parseFloat(item[valueField]) || 0;
                        return { category: category.toString(), value: value };
                    })
                    .filter((item) => item.value > 0);
            }
        },

        generateChartOptions(chartData, categoryField, valueField, chartType) {
            // Sorteer data op waarde (hoogste eerst) en limiteer tot top 20
            const sortedData = chartData
                .sort((a, b) => b.value - a.value)
                .slice(0, 20);

            const title = this.generateChartTitle(categoryField, valueField);

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
                    bottom: 40,
                    left: 60,
                },
            };

            switch (chartType) {
                case "bar":
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: this.getFieldDisplayName(
                                        categoryField
                                    ),
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: {
                                    text: this.getFieldDisplayName(valueField),
                                },
                            },
                        ],
                        series: [
                            {
                                type: "bar",
                                xKey: "category",
                                yKey: "number",
                                fill: "#3B82F6",
                            },
                        ],
                    };

                case "line":
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: this.getFieldDisplayName(
                                        categoryField
                                    ),
                                },
                                label: {
                                    rotation: -45,
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: {
                                    text: this.getFieldDisplayName(valueField),
                                },
                            },
                        ],
                        series: [
                            {
                                type: "line",
                                xKey: "category",
                                yKey: "value",
                                stroke: "#3B82F6",
                                marker: {
                                    enabled: true,
                                    fill: "#3B82F6",
                                },
                            },
                        ],
                    };

                case "area":
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: this.getFieldDisplayName(
                                        categoryField
                                    ),
                                },
                                label: {
                                    rotation: -45,
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: {
                                    text: this.getFieldDisplayName(valueField),
                                },
                            },
                        ],
                        series: [
                            {
                                type: "area",
                                xKey: "category",
                                yKey: "value",
                                fill: "rgba(59, 130, 246, 0.3)",
                                stroke: "#3B82F6",
                            },
                        ],
                    };

                default: // column
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: this.getFieldDisplayName(
                                        categoryField
                                    ),
                                },
                                label: {
                                    rotation: sortedData.length > 5 ? -45 : 0,
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: {
                                    text: this.getFieldDisplayName(valueField),
                                },
                            },
                        ],
                        series: [
                            {
                                type: "bar",
                                xKey: "category",
                                yKey: "value",
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
        getTableRowData(message) {
            // Probeer eerst JSON data te gebruiken
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
            // Probeer eerst JSON data te gebruiken
            if (
                message.json &&
                Array.isArray(message.json) &&
                message.json.length > 0
            ) {
                const firstItem = message.json[0];
                return Object.keys(firstItem).map((key) => ({
                    field: key,
                    headerName: key,
                    sortable: true,
                    filter: true,
                    resizable: true,
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
            }

            // Fallback naar oude methode
            const parsed = this.parseTableMessage(message.message);
            return parsed.headers.map((header) => ({
                field: header,
                headerName: header,
                sortable: true,
                filter: true,
                resizable: true,
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
        async sendMessage() {
            this.no_message = false;
            if (this.form.message === "" || this.form.source === "x") {
                this.no_message = true;
                return;
            }

            const params = new URLSearchParams({
                message: this.form.message,
                conversation: this.form.conversation.guid,
                source: this.form.source.id || "",
            });

            try {
                const userresponse = await fetch(
                    `${route(
                        "conversation.postUserMessage",
                        this.conversation.guid
                    )}?${params.toString()}`,
                    {
                        headers: { Accept: "application/json" },
                    }
                );

                if (!userresponse.ok)
                    throw new Error("Network response was not ok");

                const { message = [] } = await userresponse.json();
                if (message && message.created_at) {
                    this.messages.push({
                        ...message,
                        displayAsTable: false,
                        displayAsChart: false,
                        selectedChartType: "bar",
                        selectedXAxis: null,
                        selectedYAxis: null,
                        selectedAggregation: "sum",
                    });
                }

                this.form.message = "";
                this.loading = true;

                try {
                    const botresponse = await fetch(
                        `${route(
                            "conversation.getBotResponse",
                            this.conversation.guid
                        )}?${params.toString()}`,
                        {
                            headers: { Accept: "application/json" },
                        }
                    );

                    if (!botresponse.ok)
                        throw new Error("Network response was not ok");

                    const { bot_message = [] } = await botresponse.json();

                    this.loading = false;
                    if (bot_message && bot_message.created_at) {
                        this.messages.push({
                            ...bot_message,
                            displayAsTable: false,
                            displayAsChart: false,
                            selectedChartType: "bar",
                            selectedXAxis: null,
                            selectedYAxis: null,
                            selectedAggregation: "sum",
                        });
                    }
                } catch (error) {
                    setTimeout(() => {
                        this.loading = false;
                        this.error =
                            "There was an error fetching the information. Please try again.";
                    }, 1000);
                }
            } catch (error) {
                this.error =
                    "There was an error fetching the information. Please try again.";
            }
        },
        formatDate(date) {
            const options = {
                year: "numeric",
                month: "numeric",
                day: "numeric",
            };
            return new Date(date).toLocaleDateString(undefined, options);
        },

        formatTime(date) {
            const options = {
                hour: "2-digit",
                minute: "2-digit",
            };
            return new Date(date).toLocaleTimeString(undefined, options);
        },

        shouldDisplayDate(index, messages) {
            if (!messages[index] || !messages[index - 1]) return true;
            const currentDate = new Date(messages[index].created_at);
            const previousDate = new Date(messages[index - 1].created_at);
            return currentDate.toDateString() !== previousDate.toDateString();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagecontainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
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
    },

    mounted() {
        this.messages = this.conversation.messages.map((m) => ({
            ...m,
            displayAsTable: false,
            displayAsChart: false,
            selectedChartType: "bar",
            // Nieuwe chart configuratie properties
            selectedXAxis: null,
            selectedYAxis: null,
            selectedAggregation: "sum",
        }));
        this.scrollToBottom();
    },

    watch: {
        messages() {
            this.scrollToBottom();
        },
        loading() {
            this.scrollToBottom();
        },
    },
};
</script>

<style>
/* @import "ag-grid-community/styles/ag-grid.css"; */
/* @import     "ag-grid-community/styles/ag-theme-alpine.css"; */
.typing-dot {
    animation: pulse 1.4s infinite ease-in-out;
    opacity: 0.4;
    display: inline-block;
}

.delay-200 {
    animation-delay: 0.2s;
}

.delay-400 {
    animation-delay: 0.4s;
}

@keyframes pulse {
    0%,
    80%,
    100% {
        opacity: 0.4;
    }
    40% {
        opacity: 1;
    }
}

/* AG Grid customization */
.ag-theme-alpine {
    --ag-font-size: 13px;
    --ag-header-height: 45px;
    --ag-row-height: 45px;
    --ag-border-color: #e5e7eb;
    --ag-header-background-color: #f9fafb;
}

.ag-theme-alpine .ag-header-cell-text {
    font-weight: 600;
    color: #374151;
}

.ag-theme-alpine .ag-row {
    border-bottom: 1px solid #f3f4f6;
}

.ag-theme-alpine .ag-row:hover {
    background-color: #f9fafb;
}

.ag-aria-description-container {
    display: none !important;
    height: 0 !important;
    overflow: hidden !important;
}
</style>
