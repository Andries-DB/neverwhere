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
                :grandTotalRow="grandTotalRow"
            />
        </div>
    </div>
</template>

<script>
import {
    iconSetQuartzLight,
    ModuleRegistry,
    themeBalham,
    themeQuartz,
} from "ag-grid-community";
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-enterprise";
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
    name: "TableBuilder",
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
            recordCountInterval: null,
            grandTotalRow: "",
            gridApi: null,
            customHeaders: {},
            savedGridState: {
                columnState: null,
                filterModel: null,
                customHeaders: null,
            },
            // Maak defaultColDef dynamisch gebaseerd op features
            defaultColDef: {
                sortable: this.getFeature("sorting", true), // default true
                filter: this.getFeature("filtering", true), // default true
                resizable: true,
                flex: 1,
                minWidth: 100,
                enableValue: this.getFeature("grouping", true),
                enableRowGroup: this.getFeature("grouping", true),
                enablePivot: this.getFeature("grouping", true),
                chartDataType: "category",
                autoHeight: true,
                floatingFilter: false,
                wrapText: this.getFeature("multiline_text", false),
            },
            // Maak gridOptions ook dynamisch
            gridOptions: {
                rowHeight: 45,
                headerHeight: 45,
                animateRows: true,
                pagination: this.getFeature("pagination", false),
                paginationPageSize: 20,
                enableCharts: true,
                enableRangeSelection: true,
                suppressRowClickSelection: true,
                enableRowGroup: this.getFeature("grouping", true),
                enablePivot: this.getFeature("grouping", true),
                enableValue: this.getFeature("grouping", true),

                chartThemes: ["ag-default", "ag-material", "ag-pastel"],
                getContextMenuItems: (params) => {
                    const defaultItems = [
                        "copy",
                        "copyWithHeaders",
                        "separator",
                    ];

                    // Voeg chart opties alleen toe als grouping enabled is
                    if (this.getFeature("grouping", true)) {
                        defaultItems.push("chartRange", "separator");
                    }

                    defaultItems.push("export");
                    return defaultItems;
                },
                getMainMenuItems: (params) => {
                    const defaultItems = params.defaultItems;

                    // Voeg "Change column name" als eerste item toe
                    defaultItems.unshift({
                        name: "Change column name",
                        icon: '<i class="fas fa-edit"></i>', // Of gebruik een andere icon
                        cssClasses: ["custom-menu-item-with-border"],
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

                    // Voeg een separator toe na het custom item
                    defaultItems.splice(1, 0, "separator");

                    return defaultItems;
                },
            },
        };
    },
    created() {
        this.loadGridState();

        if (this.message._total_row && this.sort !== "pinned") {
            this.grandTotalRow = "bottom";
        }
    },
    methods: {
        getFilteredRecords() {
            const count = this.gridApi
                ? this.gridApi.getDisplayedRowCount()
                : 0;
            // Update de parent component
            if (this.message) {
                this.message.filteredRecordCount = count;
            }
            return count;
        },
        getColdef() {
            const columnState = this.gridApi.getColumnState().map((c) => {
                const colDef = this.gridApi.getColumnDef(c.colId);
                return { ...c, headerName: colDef.headerName };
            });

            return {
                columnState,
                filterModel: this.gridApi.getFilterModel(),
            };
        },
        exportTable() {
            this.gridApi.exportDataAsCsv({
                fileName: `export_${this.message.guid || "table"}.csv`,
            });
        },
        getFeature(featureName, defaultValue = true) {
            if (!this.message.features) {
                return defaultValue;
            }
            return this.message.features[featureName] !== undefined
                ? this.message.features[featureName]
                : defaultValue;
        },
        async SaveGridState() {
            if (!this.gridApi || !this.message.guid) {
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
                    total_row: this.grandTotalRow,
                };

                const response = await fetch("/conversation/savecoldef", {
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

                const result = await response.json();

                this.updateCsrfToken(response);

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
                        isNumeric = false;
                    }

                    const columnDef = {
                        field: key,
                        headerName: this.getFieldDisplayName(key),
                        sortable: this.getFeature("sorting", true),
                        filter: this.getFeature("filtering", true)
                            ? isNumeric
                                ? "agNumberColumnFilter"
                                : "agTextColumnFilter"
                            : false,
                        resizable: true,
                        enableRowGroup: this.getFeature("grouping", true),
                        enablePivot: this.getFeature("grouping", true),
                        enableValue: this.getFeature("grouping", true),
                        chartDataType: isNumeric
                            ? "series"
                            : isDate
                            ? "time"
                            : "category",
                        menuTabs: this.getMenuTabs(),
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

                    // Type-specifieke configuratie alleen toevoegen als grouping enabled is
                    if (this.getFeature("grouping", true)) {
                        if (isNumeric) {
                            Object.assign(columnDef, {
                                type: "numericColumn",
                                cellClass: "number-cell",
                                aggFunc: "sum",
                            });
                        }
                        if (isDate) {
                            Object.assign(columnDef, {
                                type: "dateColumn",
                                cellClass: "date-cell",
                            });
                        }
                    }

                    return columnDef;
                });
            }

            // Fallback voor oude methode
            const parsed = this.parseTableMessage(message.message);
            return parsed.headers.map((header) => ({
                field: header,
                headerName: header,
                sortable: this.getFeature("sorting", true),
                filter: this.getFeature("filtering", true),
                resizable: true,
                enableRowGroup: this.getFeature("grouping", true),
                enablePivot: this.getFeature("grouping", true),
                enableValue: this.getFeature("grouping", true),
                chartDataType: "category",
                menuTabs: this.getMenuTabs(),
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
        getMenuTabs() {
            const tabs = ["generalMenuTab"];

            if (this.getFeature("filtering", true)) {
                tabs.unshift("filterMenuTab"); // Voeg toe aan het begin
            }

            tabs.push("columnsMenuTab");

            return tabs;
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
                .slice(0, Math.min(20, data.length)) // Beperk tot eerste 20 records voor performance
                .map((item) => item[fieldName])
                .filter((val) => val != null && val !== "");

            if (sampleValues.length === 0) return false;

            // Minimaal 70% van de waarden moet een datum zijn
            const dateCount = sampleValues.filter((value) => {
                if (!value) return false;

                const str = value.toString().trim();

                // Skip obvious non-dates
                if (str === "") return false;

                // Check if it's a number (including decimals with comma or dot)
                if (/^-?\d+([,.]\d+)*$/.test(str)) return false;

                // Check if it contains only digits and common separators
                if (!/[^\d\s\-\/\.\:T]/.test(str)) {
                    // ISO datum format (YYYY-MM-DD of YYYY-MM-DDTHH:mm:ss)
                    if (/^\d{4}-\d{2}-\d{2}(\T\d{2}:\d{2}:\d{2})?/.test(str))
                        return true;

                    // DD/MM/YYYY of MM/DD/YYYY (maar niet 1.234,56 achtige nummers)
                    if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(str)) return true;

                    // DD-MM-YYYY of MM-DD-YYYY
                    if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(str)) return true;

                    if (/^\d{1,2}\.\d{1,2}\.\d{4}$/.test(str)) return true;

                    if (
                        /^\d{1,2}[\-\/\.]\d{1,2}[\-\/\.]\d{4}\s+\d{1,2}:\d{2}/.test(
                            str
                        )
                    )
                        return true;

                    if (/^\d{1,2}[\-\/\.]\d{4}$/.test(str)) return true;

                    // Jaar/Maand formaten (YYYY/MM, YYYY-MM, YYYY.MM)
                    if (/^\d{4}[\-\/\.]\d{1,2}$/.test(str)) return true;

                    // Andere datum formaten met tijd
                    if (
                        /^\d{1,2}[\-\/\.]\d{1,2}[\-\/\.]\d{4}\s+\d{1,2}:\d{2}/.test(
                            str
                        )
                    )
                        return true;
                }

                if (str.length >= 8 && str.length <= 30) {
                    const parsed = new Date(str);
                    if (!isNaN(parsed.getTime())) {
                        const year = parsed.getFullYear();
                        if (year >= 1900 && year <= 2100) {
                            return (
                                /[\-\/\.\s:]/.test(str) &&
                                !/^[\d,.]+$/.test(str)
                            );
                        }
                    }
                }

                return false;
            }).length;

            // Minimaal 80% van de waarden moet een geldige datum zijn
            return dateCount / sampleValues.length >= 0.8;
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
                    storedState = JSON.stringify(this.col_def);
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

            this.$nextTick(() => {
                this.message.filteredRecordCount = this.getFilteredRecords();
            });

            // Listen for changes
            this.gridApi.addEventListener("filterChanged", () => {
                this.message.filteredRecordCount = this.getFilteredRecords();
            });

            // Also listen for sort changes
            this.gridApi.addEventListener("sortChanged", () => {
                this.message.filteredRecordCount = this.getFilteredRecords();
            });

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
                }
                this.columnDefs = this.getTableColumnDefs(newMessage);
            },
        },
        "message.features.pagination": function (newValue) {
            if (this.gridApi) {
                this.gridApi.setGridOption("pagination", newValue);
            }
        },
        "message.features.multiline_text": function (newValue) {
            if (this.gridApi) {
                this.defaultColDef.wrapText = newValue;
                this.defaultColDef.autoHeight = newValue;

                const allColumns = this.gridApi.getColumns();
                allColumns.forEach((column) => {
                    const colDef = column.getColDef();
                    colDef.wrapText = newValue;
                    colDef.autoHeight = newValue;
                });

                // Refresh de grid
                this.gridApi.refreshCells();
            }
        },
        "message.features.floating_filters": function (newValue) {
            if (this.gridApi && this.getFeature("filtering") === true) {
                this.defaultColDef.floatingFilter = newValue;

                const allColumns = this.gridApi.getColumns();
                allColumns.forEach((column) => {
                    const colDef = column.getColDef();
                    colDef.floatingFilter = newValue;
                });

                // Refresh de grid
                this.gridApi.refreshCells();
                // this.gridApi.resetRowHeights();
            }
        },
        "message.features.filtering": function (newValue) {
            if (this.gridApi && this.getFeature("floating_filters") === true) {
                this.defaultColDef.floatingFilter = newValue;

                const allColumns = this.gridApi.getColumns();
                allColumns.forEach((column) => {
                    const colDef = column.getColDef();
                    colDef.floatingFilter = newValue;
                });

                this.gridApi.refreshCells();
            }
        },
        "message.features.total_row": function (newValue) {
            this.grandTotalRow = newValue === true ? "bottom" : "";
        },
    },
};
</script>
