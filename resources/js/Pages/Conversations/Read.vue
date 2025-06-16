<template>
    <Head :title="conversation.title ?? 'Nieuwe conversatie'" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between">
            <h1 class="text-black font-bold text-4xl">
                {{ conversation.title ?? "Nieuwe conversatie" }}
            </h1>
        </div>

        <div class="mt-10 items-stretch gap-5 flex flex-col md:flex-row">
            <div
                class="main-nav w-full bg-slate-100 text-black flex flex-col items-center rounded-md"
            >
                <div class="h-[475px] w-full pb-5 bg-dotted flex flex-col">
                    <div
                        ref="messagecontainer"
                        class="overflow-y-scroll flex-1 p-3 max-h-[500px] flex flex-col gap-2"
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
                                    'bg-gray-100 text-gray-800 self-start max-w-[95%]':
                                        message.send_by === 'ai' &&
                                        (message.displayAsTable ||
                                            message.displayAsChart),
                                }"
                            >
                                <p
                                    v-if="message.send_by === 'user'"
                                    class="text-xs text-gray-600 mb-1 font-semibold"
                                >
                                    You:
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
                                <div
                                    v-if="
                                        message.displayAsChart &&
                                        hasChartData(message)
                                    "
                                >
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

                                    <template
                                        v-if="
                                            parseTableMessage(message.message)
                                                .rows.length > 0
                                        "
                                    >
                                        <!-- Chart type selector -->
                                        <div class="mb-3 flex gap-2">
                                            <button
                                                v-for="chartType in availableChartTypes"
                                                :key="chartType.value"
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
                                                {{ chartType.label }}
                                            </button>
                                        </div>

                                        <!-- AG Chart component -->
                                        <div
                                            class="w-full h-96 bg-white rounded-lg border border-gray-200"
                                        >
                                            <ag-charts
                                                class="w-full h-full"
                                                :options="
                                                    getChartOptions(message)
                                                "
                                            />
                                        </div>

                                        <p
                                            v-if="
                                                parseTableMessage(
                                                    message.message
                                                ).below
                                            "
                                            class="mt-3 text-sm leading-relaxed"
                                        >
                                            {{
                                                parseTableMessage(
                                                    message.message
                                                ).below
                                            }}
                                        </p>
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
                                <div
                                    v-else-if="
                                        message.displayAsTable &&
                                        hasTableData(message)
                                    "
                                >
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

                                    <template
                                        v-if="
                                            parseTableMessage(message.message)
                                                .rows.length > 0
                                        "
                                    >
                                        <!-- AG Grid component -->
                                        <div
                                            class="w-full h-96 bg-white rounded-lg border border-gray-200"
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
                                            />
                                        </div>

                                        <p
                                            v-if="
                                                parseTableMessage(
                                                    message.message
                                                ).below
                                            "
                                            class="mt-3 text-sm leading-relaxed"
                                        >
                                            {{
                                                parseTableMessage(
                                                    message.message
                                                ).below
                                            }}
                                        </p>
                                    </template>

                                    <template v-else>
                                        <div
                                            class="text-red-600 text-sm bg-red-50 p-3 rounded-lg border border-red-200"
                                            v-html="
                                                parseTableMessage(
                                                    message.message
                                                ).below
                                            "
                                        ></div>
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
                    class="flex mflex-col justify-center items-center w-full gap-4 px-2 py-3"
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
                pagination: true,
                paginationPageSize: 15,
                suppressRowClickSelection: true,
            },
            // Chart types
            availableChartTypes: [
                { value: "column", label: "Kolom" },
                { value: "bar", label: "Balk" },
                { value: "line", label: "Lijn" },
                { value: "pie", label: "Taart" },
                { value: "doughnut", label: "Donut" },
                { value: "area", label: "Vlak" },
            ],
        };
    },
    methods: {
        // Stel weergave modus in
        setDisplayMode(message, mode) {
            message.displayAsTable = mode === "table";
            message.displayAsChart = mode === "chart";
        },

        // Controleer of een message tabel data heeft
        hasTableData(message) {
            if (message.send_by !== "ai") return false;
            const parsed = this.parseTableMessage(message.message);
            return parsed.rows.length > 0;
        },

        // Controleer of een message geschikt is voor grafieken
        hasChartData(message) {
            if (message.send_by !== "ai") return false;
            const parsed = this.parseTableMessage(message.message);

            if (parsed.rows.length === 0) return false;

            // Zoek naar numerieke kolommen
            const numericColumns = this.getNumericColumns(parsed);
            return numericColumns.length > 0;
        },

        // Vind numerieke kolommen in de data
        getNumericColumns(parsed) {
            const numericColumns = [];

            parsed.headers.forEach((header, index) => {
                const values = parsed.rows.map((row) => row[index]);
                const numericValues = values.filter((val) => {
                    if (!val || val === "") return false;
                    const num = parseFloat(
                        val.toString().replace(/[^\d.-]/g, "")
                    );
                    return !isNaN(num);
                });

                // Als meer dan 50% van de waarden numeriek zijn
                if (numericValues.length > values.length * 0.5) {
                    numericColumns.push({
                        header,
                        index,
                        values: values.map((val) => {
                            if (!val || val === "") return 0;
                            const num = parseFloat(
                                val.toString().replace(/[^\d.-]/g, "")
                            );
                            return isNaN(num) ? 0 : num;
                        }),
                    });
                }
            });

            return numericColumns;
        },

        // Genereer chart opties voor AG Charts
        getChartOptions(message) {
            const parsed = this.parseTableMessage(message.message);
            const chartType = message.selectedChartType || "column";
            const numericColumns = this.getNumericColumns(parsed);

            if (numericColumns.length === 0) {
                return {};
            }

            // Maak data array voor de chart
            const chartData = parsed.rows.map((row, index) => {
                const dataPoint = {
                    category: row[0] || `Item ${index + 1}`, // Gebruik eerste kolom als categorie
                };

                numericColumns.forEach((col) => {
                    dataPoint[col.header] = col.values[index];
                });

                return dataPoint;
            });

            const baseOptions = {
                data: chartData,
                title: {
                    text: `${parsed.headers[0] || "Data"} Analyse`,
                    fontSize: 16,
                    fontWeight: "bold",
                },
                subtitle: {
                    text: `Gebaseerd op ${parsed.rows.length} items`,
                    fontSize: 12,
                },
                padding: {
                    top: 20,
                    right: 20,
                    bottom: 20,
                    left: 20,
                },
            };

            // Chart type specifieke configuratie
            switch (chartType) {
                case "pie":
                case "doughnut":
                    // Voor pie charts, gebruik de eerste numerieke kolom
                    const firstNumericCol = numericColumns[0];
                    return {
                        ...baseOptions,
                        series: [
                            {
                                type:
                                    chartType === "doughnut" ? "donut" : "pie",
                                angleKey: firstNumericCol.header,
                                categoryKey: "category",
                                label: {
                                    enabled: true,
                                },
                                tooltip: {
                                    renderer: ({
                                        datum,
                                        angleKey,
                                        categoryKey,
                                    }) => {
                                        return {
                                            content: `${datum[categoryKey]}: ${datum[angleKey]}`,
                                        };
                                    },
                                },
                            },
                        ],
                    };

                case "line":
                case "area":
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: parsed.headers[0] || "Categorie",
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: { text: "Waarde" },
                            },
                        ],
                        series: numericColumns.map((col) => ({
                            type: chartType,
                            xKey: "category",
                            yKey: col.header,
                            marker:
                                chartType === "line"
                                    ? { enabled: true }
                                    : undefined,
                            fill:
                                chartType === "area"
                                    ? "rgba(54, 162, 235, 0.2)"
                                    : undefined,
                        })),
                    };

                case "bar":
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "number",
                                position: "bottom",
                                title: { text: "Waarde" },
                            },
                            {
                                type: "category",
                                position: "left",
                                title: {
                                    text: parsed.headers[0] || "Categorie",
                                },
                            },
                        ],
                        series: numericColumns.map((col) => ({
                            type: "bar",
                            xKey: col.header,
                            yKey: "category",
                        })),
                    };

                default: // column
                    return {
                        ...baseOptions,
                        axes: [
                            {
                                type: "category",
                                position: "bottom",
                                title: {
                                    text: parsed.headers[0] || "Categorie",
                                },
                            },
                            {
                                type: "number",
                                position: "left",
                                title: { text: "Waarde" },
                            },
                        ],
                        series: numericColumns.map((col) => ({
                            type: "column",
                            xKey: "category",
                            yKey: col.header,
                        })),
                    };
            }
        },

        // Genereer row data voor AG Grid
        getTableRowData(message) {
            const parsed = this.parseTableMessage(message.message);
            return parsed.rows.map((row) => {
                const obj = {};
                parsed.headers.forEach((header, index) => {
                    obj[header] = row[index] || "";
                });
                return obj;
            });
        },

        // Genereer column definitions voor AG Grid
        getTableColumnDefs(message) {
            const parsed = this.parseTableMessage(message.message);
            return parsed.headers.map((header) => ({
                field: header,
                headerName: header,
                sortable: true,
                filter: true,
                resizable: true,
                // Aangepaste cell renderer voor specifieke kolommen indien nodig
                cellRenderer: (params) => {
                    // Bijvoorbeeld voor URL's of speciale formatting
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
                        selectedChartType: "column",
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
                            selectedChartType: "column",
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

            for (let i = 0; i < lines.length; i++) {
                const rawLine = lines[i];
                const line = rawLine.trim();

                const projectMatch = line.match(projectRegex);

                if (projectMatch) {
                    if (!insideProjects) {
                        insideProjects = true;
                    }
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
            selectedChartType: "column",
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
@import "ag-grid-community/styles/ag-theme-alpine.css";
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
