<template>
    <!-- AG Grid component met chart ondersteuning -->
    <div
        v-if="message.json"
        class="w-full bg-white rounded-lg border border-gray-200 overflow-hidden"
    >
        <!-- AG Grid -->
        <div :class="this.height === 'full' ? 'h-[75vh]' : 'h-96'">
            <ag-grid-vue
                ref="agGrid"
                class="ag-theme-alpine w-full h-full"
                :rowData="getTableRowData(message)"
                :columnDefs="getTableColumnDefs(message)"
                :defaultColDef="defaultColDef"
                :gridOptions="gridOptions"
                rowSelection="multiple"
                groupDisplayType="multipleColumns"
                @grid-ready="onGridReady"
                @columnMoved="saveGridState"
                @columnResized="saveGridState"
                @columnVisible="saveGridState"
                @filterChanged="saveGridState"
                @sortChanged="saveGridState"
            />
        </div>
    </div>
</template>

<script>
import { ModuleRegistry } from "ag-grid-community";
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise"; // Dit activeert alle enterprise features
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";
import { ref } from "vue";

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

// let gridApi = ref(null);
// let columnApi = ref(null);
export default {
    name: "",
    components: {
        AgGridVue,
    },
    props: {
        message: Object,
        height: {
            type: String,
            default: "normal",
        },
    },
    data() {
        return {
            gridApi: null,

            savedGridState: {
                columnState: null,
                filterModel: null,
            },
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
        };
    },
    created() {
        this.loadGridState();
    },
    methods: {
        getTableRowData(message) {
            if (
                message.json &&
                Array.isArray(message.json) &&
                message.json.length > 0
            ) {
                return message.json;
            }

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
                        enableRowGroup: true,
                        enablePivot: true,
                        enableValue: true, // Alleen numerieke velden als waarden

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
                            valueFormatter: (params) => {
                                const value = parseFloat(params.value);
                                if (isNaN(value)) return "";

                                return value
                                    .toFixed(2) // twee decimalen
                                    .replace(".", ",") // decimaalteken vervangen
                                    .replace(/\B(?=(\d{3})+(?!\d))/g, "."); // duizendtallen
                            },
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
        getLocalStorageKey() {
            return `agGridState_${this.message.guid || "default"}`;
        },

        // Laad de opgeslagen staat uit localStorage
        loadGridState() {
            if (!this.message.guid) {
                return;
            }
            try {
                const storedState = localStorage.getItem(
                    this.getLocalStorageKey()
                );
                if (storedState) {
                    this.savedGridState = JSON.parse(storedState);
                } else {
                    this.savedGridState = {
                        columnState: null,
                        filterModel: null,
                    };
                }
            } catch (e) {
                this.savedGridState = { columnState: null, filterModel: null };
            }
        },
        saveGridState() {
            if (this.gridApi) {
                this.savedGridState.columnState = this.gridApi.getColumnState();
                this.savedGridState.filterModel = this.gridApi.getFilterModel();

                if (this.message.guid) {
                    try {
                        localStorage.setItem(
                            this.getLocalStorageKey(),
                            JSON.stringify(this.savedGridState)
                        );
                        console.log(
                            "Grid state saved to localStorage for key:",
                            this.getLocalStorageKey()
                        );
                    } catch (e) {
                        console.error(
                            "Error saving grid state to localStorage:",
                            e
                        );
                    }
                } else {
                }
            }
        },
        restoreGridState() {
            if (
                this.gridApi &&
                this.savedGridState &&
                this.savedGridState.columnState
            ) {
                this.gridApi.applyColumnState({
                    state: this.savedGridState.columnState,
                    applyOrder: true, // Belangrijk om de volgorde te behouden
                });

                if (this.savedGridState.filterModel) {
                    this.gridApi.setFilterModel(
                        this.savedGridState.filterModel
                    );
                }
            } else {
            }
        },

        onGridReady(params) {
            this.gridApi = params.api;

            setTimeout(() => {
                this.restoreGridState();
            }, 50);
        },
    },
    watch: {
        "message.guid": {
            immediate: false,
            handler(newGuid, oldGuid) {
                if (newGuid && newGuid !== oldGuid) {
                    this.loadGridState();
                    if (this.gridApi) {
                        this.$nextTick(() => {
                            this.restoreGridState();
                        });
                    }
                }
            },
        },

        "message.displayAsTable": {
            handler(newVal) {
                if (newVal && this.gridApi) {
                    this.$nextTick(() => {
                        this.gridApi.sizeColumnsToFit();
                    });
                }
            },
        },
    },
    computed: {},
};
</script>
