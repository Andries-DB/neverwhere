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
            />
        </div>
    </div>
</template>

<script>
import { ModuleRegistry } from "ag-grid-community";
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise"; // Dit activeert alle enterprise features
import { AgChartsEnterpriseModule } from "ag-charts-enterprise";

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
        sort: {
            type: String,
            default: "normal",
        },
        col_def: {
            type: Object,
            required: false,
        },
    },
    data() {
        return {
            gridApi: null,
            customHeaders: {},
            savedGridState: {
                columnState: null,
                filterModel: null,
                customHeaders: null,
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
                    const defaultItems = [
                        "copy",
                        "copyWithHeaders",
                        "separator",
                        "chartRange",
                        "separator",
                        "export",
                    ];

                    return defaultItems;
                },
                getMainMenuItems: (params) => {
                    const defaultItems = params.defaultItems;

                    defaultItems.push({
                        name: "Change column name",
                        action: () => {
                            const currentName =
                                params.column.getColDef().headerName ||
                                params.column.getColDef().field;
                            const newName = prompt(
                                "Nieuwe kolomtitel:",
                                currentName
                            );

                            if (newName && newName.trim() !== "") {
                                const colDef = params.column.getColDef();
                                colDef.headerName = newName;

                                if (
                                    !this.savedGridState ||
                                    !this.savedGridState.columnState
                                ) {
                                    this.savedGridState = {
                                        columnState:
                                            this.gridApi.getColumnState(),
                                        filterModel:
                                            this.gridApi.getFilterModel(),
                                    };
                                }

                                let colState =
                                    this.savedGridState.columnState.find(
                                        (c) => c.colId === colDef.field
                                    );

                                if (!colState) {
                                    // als hij nog niet bestaat, maak een nieuwe entry
                                    colState = { colId: colDef.field };
                                    this.savedGridState.columnState.push(
                                        colState
                                    );
                                }
                                // voeg de naam eraan toe
                                colState.headerName = newName;

                                params.api.refreshHeader();
                            }
                        },
                    });

                    return defaultItems;
                },
            },
        };
    },
    created() {
        this.loadGridState();
    },
    methods: {
        SaveGridState() {
            if (!this.gridApi || !this.message.guid) {
                // this.saveLoading = false;

                return;
            }

            const columnState = this.gridApi.getColumnState().map((c) => {
                const colDef = this.gridApi.getColumnDef(c.colId);
                return { ...c, headerName: colDef.headerName };
            });

            this.savedGridState = {
                columnState,
                filterModel: this.gridApi.getFilterModel(),
            };

            try {
                const data = {
                    message_guid: this.message.guid,
                    data: this.savedGridState,
                };

                const response = fetch("/conversation/savecoldef", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json", // <-- voeg deze toe
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    console.log(response);
                }

                this.updateCsrfToken(response);
            } catch (e) {}
        },
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
        getTableColumnDefs(message) {
            if (
                message.json &&
                Array.isArray(message.json) &&
                message.json.length > 0
            ) {
                const firstItem = message.json[0];
                return Object.keys(firstItem).map((key) => {
                    let isNumeric = this.isNumericField(key, message.json);
                    let isDate = this.isDateField(key, message.json);

                    if (isDate) {
                        isNumeric = false; // Als het een numeriek veld is, beschouwen we het niet als datum
                    }

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
                        enableValue: true,

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
                            if (isNumeric && !isDate) {
                                return this.formatNumber(params.value);
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
        formatNumber(value) {
            if (value === null || value === undefined) return "";

            let val = parseFloat(value);
            if (isNaN(val)) return "";

            let hasDecimals = val % 1 !== 0;
            let formatted = hasDecimals ? val.toFixed(2) : val.toString();

            const decimalSep =
                this.$page.props.auth.user.decimal_seperator === "comma"
                    ? ","
                    : ".";

            const thousandSep = (() => {
                switch (this.$page.props.auth.user.number_format) {
                    case "comma":
                        return ",";
                    case "point":
                        return ".";
                    case "space":
                        return " ";
                    default:
                        return ""; // 'none'
                }
            })();

            let [intPart, decPart] = formatted.split(".");

            if (thousandSep) {
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSep);
            }

            return decPart !== undefined
                ? `${intPart}${decimalSep}${decPart}`
                : intPart;
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
        loadGridState() {
            if (!this.message.guid) {
                return;
            }

            try {
                let storedState = null;
                if (this.col_def) {
                    storedState = this.col_def;
                } else {
                    storedState = this.message.col_def;
                }

                if (storedState) {
                    this.savedGridState = {
                        columnState: JSON.parse(storedState).columnState,
                        filterModel: JSON.parse(storedState).filterModel,
                    };
                } else {
                    this.savedGridState = {
                        columnState: null,
                        filterModel: null,
                    };
                }
            } catch (e) {
                console.log(e);
                this.savedGridState = { columnState: null, filterModel: null };
            }
        },

        restoreGridState() {
            if (!this.gridApi) {
                return;
            }

            if (!this.savedGridState || !this.savedGridState.columnState) {
                return;
            }

            try {
                // standaard state restoren (breedte, sort, etc.)
                this.gridApi.applyColumnState({
                    state: this.savedGridState.columnState,
                    applyOrder: true,
                });

                // custom headerName restoren
                this.savedGridState.columnState.forEach((col) => {
                    if (col.headerName) {
                        const colDef = this.gridApi.getColumnDef(col.colId);
                        if (colDef) {
                            colDef.headerName = col.headerName;
                        }
                    }
                });

                this.gridApi.refreshHeader();

                if (this.savedGridState.filterModel) {
                    this.gridApi.setFilterModel(
                        this.savedGridState.filterModel
                    );
                }
            } catch (e) {}
        },

        onGridReady(params) {
            this.gridApi = params.api;

            setTimeout(() => {
                this.restoreGridState();
            }, 1);
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
                            // this.restoreGridState();
                        });
                    }
                }
                this.columnDefs = this.getTableColumnDefs(newMessage);
            },
        },
    },
    computed: {},
};
</script>
