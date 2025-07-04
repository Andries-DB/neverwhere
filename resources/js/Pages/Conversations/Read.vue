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
                        class="overflow-y-scroll flex-1 p-3 xl:max-h-[73vh] lg:max-h-[500px] md:max-h-[475px] max-h-[65vh] flex flex-col gap-4 relative"
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
                                class="p-3 rounded-lg shadow-sm relative"
                                :class="{
                                    'bg-gray-300 text-gray-900 self-end max-w-[75%]':
                                        message.send_by === 'user',
                                    'bg-gray-100 text-gray-800 self-start':
                                        message.send_by === 'ai' &&
                                        !message.displayAsTable &&
                                        !message.displayAsChart,
                                    'bg-gray-100 text-gray-800 self-start w-full':
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
                                    <button
                                        v-if="message.sql_query"
                                        @click="
                                            setDisplayMode(message, 'sql_query')
                                        "
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
                                <div
                                    v-if="message.displayAsChart"
                                    class="relative"
                                >
                                    <div class="mb-3 flex justify-end">
                                        <button
                                            v-if="!isChartPinned(message)"
                                            :class="[
                                                'absolute -top-10 right-3 z-10 transition-all duration-200 ease-in-out',
                                                'w-9 h-9 rounded-full shadow-md flex items-center justify-center border',
                                                isChartPinned(message)
                                                    ? 'bg-slate-200 text-slate-700 border-slate-300 hover:bg-slate-300'
                                                    : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200',
                                                clickedMessageId === message.id
                                                    ? 'animate-pop'
                                                    : '',
                                            ]"
                                            @click="togglePinChart(message)"
                                            :title="
                                                isChartPinned(message)
                                                    ? 'Loskoppelen van Dashboard'
                                                    : 'Vastzetten op Dashboard'
                                            "
                                            :disabled="
                                                !message.selectedXAxis ||
                                                !message.selectedYAxis
                                            "
                                        >
                                            <i
                                                :class="[
                                                    'text-base',
                                                    isChartPinned(message)
                                                        ? 'fas fa-times'
                                                        : 'fas fa-thumbtack',
                                                ]"
                                            ></i>
                                        </button>
                                    </div>

                                    <ChartBuilder :message="message" />
                                </div>

                                <!-- Tabel weergave met AG Grid -->
                                <div v-else-if="message.displayAsTable">
                                    <div class="mb-3 flex justify-end relative">
                                        <button
                                            v-if="!isTablePinned(message)"
                                            :class="[
                                                'absolute -top-10 right-3 z-10 transition-all duration-200 ease-in-out',
                                                'w-9 h-9 rounded-full shadow-md flex items-center justify-center border',
                                                isTablePinned(message)
                                                    ? 'bg-slate-200 text-slate-700 border-slate-300 hover:bg-slate-300'
                                                    : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200',
                                                clickedMessageId === message.id
                                                    ? 'animate-pop'
                                                    : '',
                                            ]"
                                            @click="togglePinTable(message)"
                                            :title="
                                                isTablePinned(message)
                                                    ? 'Loskoppelen van Dashboard'
                                                    : 'Vastzetten op Dashboard'
                                            "
                                        >
                                            <i
                                                :class="[
                                                    'text-base',
                                                    isTablePinned(message)
                                                        ? 'fas fa-times'
                                                        : 'fas fa-thumbtack',
                                                ]"
                                            ></i>
                                        </button>
                                    </div>
                                    <template v-if="message.json">
                                        <!-- AG Grid component met chart ondersteuning -->
                                        <div
                                            class="w-full bg-white rounded-lg border border-gray-200 overflow-hidden"
                                        >
                                            <!-- AG Grid -->
                                            <div class="h-96">
                                                <ag-grid-vue
                                                    ref="agGrid"
                                                    class="ag-theme-alpine w-full h-full"
                                                    :rowData="
                                                        getTableRowData(message)
                                                    "
                                                    :columnDefs="
                                                        getTableColumnDefs(
                                                            message
                                                        )
                                                    "
                                                    :defaultColDef="
                                                        defaultColDef
                                                    "
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
                                    <pre
                                        class="whitespace-pre-wrap break-words"
                                        >{{ message.sql_query }}</pre
                                    >
                                </div>

                                <!-- Normale tekst weergave -->
                                <p v-else class="whitespace-pre-wrap">
                                    {{ message.message }}
                                </p>

                                <div
                                    class="flex justify-between items-center mt-2"
                                >
                                    <div class="text-xs ml-2 text-gray-600">
                                        <span
                                            :style="{
                                                visibility:
                                                    message.json &&
                                                    message.json.length > 0
                                                        ? 'visible'
                                                        : 'hidden',
                                            }"
                                        >
                                            {{ message.json?.length || 0 }}
                                            records
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-end gap-2 text-[10px]"
                                    >
                                        <button
                                            class="p-1 rounded transition-colors"
                                            :class="{
                                                'text-green-600 bg-green-50':
                                                    message.thumbs_up === 1,
                                                'text-gray-400 hover:text-green-700 hover:bg-green-50':
                                                    message.thumbs_up !== 1,
                                            }"
                                            @click="onThumbsUp(message)"
                                            title="Vind ik goed"
                                            v-if="message.send_by === 'ai'"
                                        >
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>

                                        <button
                                            class="p-1 rounded transition-colors"
                                            :class="{
                                                'text-red-600 bg-red-50':
                                                    message.thumbs_down === 1,
                                                'text-gray-400 hover:text-red-600 hover:bg-gray-50':
                                                    message.thumbs_down !== 1,
                                            }"
                                            @click="onThumbsDown(message)"
                                            title="Vind ik niet goed"
                                            v-if="message.send_by === 'ai'"
                                        >
                                            <i class="fas fa-thumbs-down"></i>
                                        </button>

                                        <p
                                            :class="[
                                                message.user_id ===
                                                $page.props.auth.user.id
                                                    ? 'text-gray-500'
                                                    : 'text-gray-600',
                                            ]"
                                        >
                                            {{ formatTime(message.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    v-if="message.send_by === 'ai'"
                                    :href="route('message.read', message.guid)"
                                    target="_blank"
                                    class="absolute -top-2 -right-2 bg-slate-300 text-gray-600 hover:text-gray-800 hover:bg-slate-200 rounded-full px-2 py-1 transition-all"
                                    title="Bekijk in detail"
                                >
                                    <i class="fas fa-expand-alt"></i>
                                </a>
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
                    class="flex flex-col justify-center items-center w-full gap-4 px-2 pt-3"
                >
                    <!-- Source selection and message input row -->
                    <div class="flex flex-col-reverse gap-3 items-start w-full">
                        <!-- Source selector -->
                        <div class="w-full flex items-center gap-2">
                            <label class="block text-xs text-gray-600"
                                >Bron</label
                            >
                            <select
                                v-model="form.source"
                                class="w-full text-sm border-none rounded-md bg-transparent focus:ring-none focus:ring-blue-500 focus:border-none transition-colors"
                                @change="saveSourceSelection"
                            >
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
                        <div class="flex-1 w-full">
                            <div
                                class="flex items-stretch rounded-md w-full border border-gray-300 overflow-hidden focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all"
                            >
                                <!-- Textarea -->
                                <textarea
                                    rows="1"
                                    v-model="form.message"
                                    @input="autoResize"
                                    @keydown.enter.exact.prevent="sendMessage"
                                    ref="messageTextarea"
                                    class="flex-1 resize-none bg-transparent border-none focus:ring-0 focus:outline-none placeholder:text-gray-500 px-3 py-2 text-sm leading-snug max-h-40 overflow-y-auto"
                                    placeholder="Stuur een bericht..."
                                ></textarea>

                                <!-- Send button -->
                                <button
                                    class="px-4 py-2 flex justify-center items-center bg-blue-500 hover:bg-blue-600 text-white transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
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

        <Snackbar ref="snackbar" />
    </AuthenticatedLayout>

    <Create :close="toggleAddCompany" :show="addCompany" />
    <DislikeModal
        :show="dislikeModal"
        :close="toggleDislikeModal"
        :message="dislikedMessage"
    />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { AgCharts } from "ag-charts-vue3";
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";
import Snackbar from "@/Components/Snackbar.vue";
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise"; // Dit activeert alle enterprise features
import { ModuleRegistry } from "ag-grid-community";
import {
    AllEnterpriseModule,
    LicenseManager,
    IntegratedChartsModule,
} from "ag-grid-enterprise";
import DislikeModal from "@/Components/Modals/DislikeModal.vue";
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
        Snackbar,
        DislikeModal,
        ChartBuilder,
    },
    props: {
        conversation: Object,
        pinned_charts: Array,
        pinned_tables: Array,
    },
    data() {
        return {
            loading: false,
            form: useForm({
                message: "",
                conversation: this.conversation,
                source: null,
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
                autoHeight: true,

                wrapText: true,
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

            pinnedCharts: [],
            pinnedTables: [],
            clickedMessageId: null,
            dislikeModal: false,
            dislikedMessage: null,
        };
    },
    methods: {
        toggleDislikeModal(message) {
            this.dislikedMessage = message;
            this.dislikeModal = !this.dislikeModal;
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
        togglePinTable(message) {
            this.clickedMessageId = message.id;

            if (this.isTablePinned(message)) {
                this.unpinTable(message);
            } else {
                this.pinTable(message);
            }

            setTimeout(() => {
                this.clickedMessageId = null;
            }, 300); // reset na animatie
        },

        // Chart functions
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
        autoResize() {
            const textarea = this.$refs.messageTextarea;
            if (textarea) {
                textarea.style.height = "auto"; // Reset
                textarea.style.height = textarea.scrollHeight + "px"; // Groei
            }
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
        isChartPinned(message) {
            return this.pinnedCharts.some(
                (chart) => chart.messageId === message.id
            );
        },
        isTablePinned(message) {
            return this.pinnedTables.some(
                (table) => table.messageId === message.id
            );
        },
        saveSourceSelection() {
            // Sla de geselecteerde source op per conversatie GUID
            const storageKey = `selected_source_${this.conversation.guid}`;
            localStorage.setItem(storageKey, this.form.source.id);
        },
        loadSourceSelection() {
            // Laad de opgeslagen source voor deze conversatie
            const storageKey = `selected_source_${this.conversation.guid}`;
            const savedSource = localStorage.getItem(storageKey);
            if (savedSource) {
                try {
                    const sourceId = parseInt(savedSource);

                    const sourceExists = this.conversation.user.sources.find(
                        (source) => source.id === sourceId
                    );

                    if (sourceExists) {
                        this.form.source = sourceExists;
                    }
                } catch (error) {
                    localStorage.removeItem(storageKey);
                }
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

        // Requests
        async sendMessage() {
            if (!this.form.message.trim()) return;
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
                        displayAsQuery: false,
                        thumbs_up: 0,
                        thumbs_down: 0,
                        selectedChartType: "bar",
                        selectedXAxis: null,
                        selectedYAxis: null,
                        selectedAggregation: "sum",
                    });
                }

                this.form.message = "";
                this.$nextTick(() => {
                    this.autoResize(); // Reset hoogte correct na DOM-update
                });
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
                            displayAsTable:
                                bot_message.respond_type === "Table",
                            displayAsChart:
                                bot_message.respond_type === "Chart",
                            displayAsQuery:
                                bot_message.respond_type === "Query",
                            thumbs_up: 0,
                            thumbs_down: 0,
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
        async pinChart(message) {
            const title = this.generateChartTitle(
                message.selectedXAxis,
                message.selectedYAxis
            );

            try {
                const response = await fetch(route("conversation.pinChart"), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        title: title,
                        type: message.selectedChartType || "bar",
                        xAxis: message.selectedXAxis,
                        yAxis: message.selectedYAxis,
                        aggregation: message.selectedAggregation || "sum",
                        data: message.json,
                        messageId: message.id,
                        createdAt: new Date().toISOString(),
                    }),
                });

                if (!response.ok) {
                    throw new Error("Failed to pin chart");
                }

                this.pinnedCharts.push({
                    messageId: message.id,
                });

                // Toon success feedback
                this.$refs.snackbar.show(
                    "Grafiek is vastgezet op het dashboard!",
                    "success"
                );
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het vastzetten van de grafiek",
                    "error"
                );
            }
        },
        async unpinChart(message) {
            try {
                const response = await fetch(
                    route("conversation.unpinChartByMessage", message.id),
                    {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error("Failed to pin chart");
                }

                this.pinnedCharts = this.pinnedCharts.filter(
                    (chart) => chart.messageId !== message.id
                );

                this.$refs.snackbar.show(
                    "Grafiek is losgekoppeld van het dashboard!",
                    "success"
                );
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het loskoppelen van de grafiek",
                    "error"
                );
            }
        },
        async onThumbsUp(message) {
            try {
                const response = await fetch(
                    route("conversation.likeMessage"),
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                        body: JSON.stringify({
                            messageId: message.id,
                        }),
                    }
                );

                if (!response.ok) {
                    throw new Error("Failed to pin chart");
                }

                // Zet het bericht lokaal op liked
                const index = this.messages.findIndex(
                    (m) => m.id === message.id
                );

                console.log("Message liked successfully", this.messages[index]);

                if (index !== -1) {
                    this.messages[index].thumbs_up = 1;
                    this.messages[index].thumbs_down = 0;
                }

                console.log("Message liked successfully", this.messages[index]);
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het liken van het bericht",
                    "error"
                );
            }
        },
        async onThumbsDown(message) {
            this.toggleDislikeModal(message);

            try {
                const index = this.messages.findIndex(
                    (m) => m.id === message.id
                );

                if (index !== -1) {
                    this.messages[index].thumbs_up = 0;
                    this.messages[index].thumbs_down = 1;
                }

                console.log(
                    "Message disliked successfully",
                    this.messages[index]
                );
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het disliken van het bericht",
                    "error"
                );
            }
        },
        async pinTable(message) {
            try {
                const response = await fetch(route("conversation.pinTable"), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        data: message.json,
                        messageId: message.id,
                    }),
                });

                if (!response.ok) {
                    throw new Error("Failed to pin chart");
                }

                this.pinnedTables.push({
                    messageId: message.id,
                });

                // Toon success feedback
                this.$refs.snackbar.show(
                    "Tabel is vastgezet op het dashboard!",
                    "success"
                );
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het vastzetten van de tabel",
                    "error"
                );
            }
        },
        async unpinTable(message) {
            try {
                const response = await fetch(
                    route("conversation.unpinTable", message.id),
                    {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error("Failed to pin chart");
                }

                this.pinnedTables = this.pinnedTables.filter(
                    (table) => table.messageId !== message.id
                );

                this.$refs.snackbar.show(
                    "Tabel is losgekoppeld van het dashboard!",
                    "success"
                );
            } catch (error) {
                this.$refs.snackbar.show(
                    "Er is een fout opgetreden bij het loskoppelen van de tabel",
                    "error"
                );
            }
        },
    },
    mounted() {
        this.messages = this.conversation.messages.map((m) => ({
            ...m,
            displayAsTable: m.respond_type === "Table",
            displayAsChart: m.respond_type === "Chart",
            displayAsQuery: m.respond_type === "Query",
            thumbs_up: m.thumbs_up || 0,
            thumbs_down: m.thumbs_down || 0,
            selectedChartType: "bar",
            selectedXAxis: null,
            selectedYAxis: null,
            selectedAggregation: "sum",
        }));

        this.form.source ??= this.conversation.user.sources?.[0] ?? null;

        this.pinnedCharts = this.pinned_charts.map((chart) => ({
            messageId: chart.message_id,
        }));

        this.pinnedTables = this.pinned_tables.map((table) => ({
            messageId: table.message_id,
        }));

        this.scrollToBottom();

        this.loadSourceSelection();
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
