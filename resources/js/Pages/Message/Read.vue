<template>
    <Head title="Bericht detail" />
    <AuthenticatedLayout>
        <PrimaryButton @click="goBack" class="text-sm mb-4">
            <i class="fas fa-arrow-left mr-2"></i>
            Ga terug
        </PrimaryButton>
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
                    <ChartBuilder :message="message" ref="chartBuilder" />
                </div>

                <!-- Tabel weergave met AG Grid -->
                <div v-else-if="message.displayAsTable">
                    <TableBuilder
                        :message="message"
                        height="full"
                        ref="tableBuilder"
                    />

                    <div class="flex justify-start items-center gap-2 mt-2">
                        <span
                            :style="{
                                visibility:
                                    message.json && message.json.length > 0
                                        ? 'visible'
                                        : 'hidden',
                            }"
                            class="text-xs ml-2 text-gray-600"
                        >
                            {{ message.json?.length || 0 }}
                            records
                        </span>

                        <button
                            @click="manualSaveGridState(message)"
                            class="px-3 py-1.5 text-xs rounded-md focus:outline-none flex items-center text-gray-600 hover:bg-gray-200"
                        >
                            <template v-if="message.displayAsTable">
                                <span v-if="message.col_def">
                                    <i class="fas fa-bookmark mr-1"></i>
                                    Opgeslagen
                                </span>
                                <span v-else>
                                    <i class="far fa-bookmark mr-1"></i>
                                    Opslaan
                                </span>
                            </template>
                        </button>
                    </div>
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
                <div
                    v-else
                    v-html="formatMessage(message)"
                    class="whitespace-pre-wrap"
                ></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ChartBuilder from "@/Components/ChartBuilder.vue";
import TableBuilder from "@/Components/TableBuilder.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        ChartBuilder,
        TableBuilder,
    },
    props: {
        message: Object,
    },
    data() {
        return {};
    },
    methods: {
        goBack() {
            window.history.back();
        },
        hasTableData(message) {
            if (message.send_by !== "ai") return false;

            if (!message.json || Object.keys(message.json).length === 0) {
                return false;
            }

            if (Array.isArray(message.json) && message.json.length > 0) {
                return true;
            }

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
        formatMessage(message) {
            if (!message?.message) return "";

            const markdownLinkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;

            return message.message.replace(
                markdownLinkRegex,
                (match, linkText, url) => {
                    return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline">${linkText}</a>`;
                }
            );
        },
        setDisplayMode(message, mode) {
            message.displayAsTable = mode === "table";
            message.displayAsChart = mode === "chart";
            message.displayAsQuery = mode === "sql_query";

            // Set default axes if switching to chart mode and not already set
            if (
                mode === "chart" &&
                (!message.selectedXAxis || !message.selectedYAxis)
            ) {
                const fields = this.getAllFields(message);
                if (fields.length > 0) {
                    message.selectedXAxis = fields[0].key;
                    // Try to find a numeric field for Y-axis, otherwise default to count
                    const numericField = fields.find((f) =>
                        this.isNumericField(f.key, message.json)
                    );
                    message.selectedYAxis = numericField
                        ? numericField.key
                        : "__count";
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
        manualSaveGridState(message) {
            if (message.displayAsTable) {
                const refName = `tableBuilder`;
                const tableBuilderRef = this.$refs[refName];
                const tableBuilder = Array.isArray(tableBuilderRef)
                    ? tableBuilderRef[0]
                    : tableBuilderRef;

                if (
                    tableBuilder &&
                    typeof tableBuilder.SaveGridState === "function"
                ) {
                    tableBuilder
                        .SaveGridState()
                        .then((result) => {
                            if (result.success) {
                                message.col_def = result.data;

                                this.$refs.snackbar.show(
                                    "Tabel is goed opgeslagen",
                                    "success"
                                );
                            } else {
                                this.$refs.snackbar.show(
                                    "Er is een fout opgetreden bij het opslaan van de tabel",
                                    "error"
                                );
                            }
                        })
                        .catch((error) => {
                            this.$refs.snackbar.show(
                                "Er is een fout opgetreden bij het opslaan van de tabel",
                                "error"
                            );
                        });
                }
            } else if (message.displayAsChart) {
                const refName = `chartBuilder`;
                const chartBuilderRef = this.$refs[refName];
                const chartBuilder = Array.isArray(chartBuilderRef)
                    ? chartBuilderRef[0]
                    : chartBuilderRef;

                if (
                    chartBuilderRef &&
                    typeof chartBuilder.SaveGraphState === "function"
                ) {
                    chartBuilder
                        .SaveGraphState()
                        .then((result) => {
                            if (result.success) {
                                console.log(result.data._x);
                                message._x = result.data._x;
                                message._y = result.data._y;
                                message._sort = result.data._sort;
                                message._order = result.data._order;
                                message._agg = result.data._agg;

                                this.$refs.snackbar.show(
                                    "Grafiek is goed opgeslagen",
                                    "success"
                                );
                            } else {
                                this.$refs.snackbar.show(
                                    "Er is een fout opgetreden bij het opslaan van de grafiek",
                                    "error"
                                );
                            }
                        })
                        .catch((error) => {
                            this.$refs.snackbar.show(
                                "Er is een fout opgetreden bij het opslaan van de grafiek",
                                "error"
                            );
                        });
                }
            }
        },
    },
    computed: {},
    mounted() {
        // Alleen nodig als de message afkomstig is van de AI
        if (this.message.send_by === "ai") {
            this.message.displayAsTable = this.message.respond_type === "Table";
            this.message.displayAsChart = this.message.respond_type === "Chart";
            this.message.displayAsQuery = this.message.respond_type === "Query";
            this.message.selectedChartType = this.message._sort || "bar";
            this.message.selectedXAxis = this.message._x || null;
            this.message.selectedYAxis = this.message._y || null;
            this.message.selectedAggregation = this.message._agg || "sum";
            this.message.selectedSortField = this.message._order || null;
            this.message.selectedSortDirection =
                this.message._order_dir || null;
            this.message.col_def = this.message.col_def || null;
        }
    },
};
</script>
