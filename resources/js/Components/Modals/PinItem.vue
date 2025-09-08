<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">Geef je instellingen door</h2>

            <TextInput
                v-model="form.title"
                label="Titel"
                placeholder="Titel"
                :class="{ 'border-red-500': form.errors.title }"
            />

            <!-- Dashboard Selector -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700"
                    >Dashboard</label
                >
                <div class="relative" v-click-outside="closeDashboardDropdown">
                    <button
                        type="button"
                        @click="toggleDashboardDropdown"
                        class="w-full inline-flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 rounded-md border border-gray-300 transition-colors"
                        :class="{ 'border-red-500': form.errors.dashboard_id }"
                    >
                        <span class="truncate">{{
                            selectedDashboard
                                ? selectedDashboard.name
                                : "Selecteer dashboard"
                        }}</span>
                        <div class="ml-2 flex flex-col">
                            <svg
                                class="h-3 w-3 -mb-1"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                class="h-3 w-3"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </button>

                    <!-- Dashboard Dropdown -->
                    <div
                        v-if="isDashboardDropdownOpen"
                        class="absolute z-50 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200"
                    >
                        <div class="py-1 max-h-60 overflow-auto">
                            <div
                                v-for="dashboard in dashboards"
                                :key="dashboard.guid"
                                @click="selectDashboard(dashboard)"
                                class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{
                                    'bg-gray-100 text-gray-900':
                                        selectedDashboard &&
                                        selectedDashboard.guid ===
                                            dashboard.guid,
                                }"
                            >
                                <div class="flex items-center">
                                    <span class="truncate">{{
                                        dashboard.name
                                    }}</span>
                                    <span
                                        v-if="dashboard.default === 1"
                                        class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"
                                    >
                                        Default
                                    </span>
                                </div>
                                <svg
                                    v-if="
                                        selectedDashboard &&
                                        selectedDashboard.guid ===
                                            dashboard.guid
                                    "
                                    class="h-4 w-4 text-gray-900"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="!dashboards || dashboards.length === 0"
                                class="px-3 py-2 text-sm text-gray-500"
                            >
                                Geen dashboards beschikbaar
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700"
                    >Breedte</label
                >
                <div
                    class="flex rounded-md overflow-hidden border border-gray-300 w-fit"
                >
                    <button
                        type="button"
                        @click="form.width = 'half'"
                        :class="[
                            'px-4 py-2 text-sm font-medium focus:outline-none',
                            form.width === 'half'
                                ? 'bg-blue-500 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-100',
                        ]"
                    >
                        Halve
                    </button>
                    <button
                        type="button"
                        @click="form.width = 'full'"
                        :class="[
                            'px-4 py-2 text-sm font-medium focus:outline-none',
                            form.width === 'full'
                                ? 'bg-blue-500 text-white'
                                : 'bg-white text-gray-700 hover:bg-gray-100',
                        ]"
                    >
                        Volle
                    </button>
                </div>
            </div>

            <div
                v-if="hasErrors"
                class="flex flex-col gap-2 text-sm text-red-600"
            >
                <InputError
                    v-for="(error, key) in form.errors"
                    :key="key"
                    :message="error"
                />
            </div>

            <div class="flex justify-end mt-4">
                <PrimaryButton
                    :disabled="form.processing"
                    type="submit"
                    :class="{ 'opacity-50': form.processing }"
                >
                    {{ form.processing ? "Aan het pinnen ..." : "Pin" }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>

<script>
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import { useForm } from "@inertiajs/vue3";
import { computed, watch, ref, onMounted } from "vue";

export default {
    name: "PinItemModal",
    components: {
        Modal,
        TextInput,
        PrimaryButton,
        InputError,
    },
    props: {
        show: Boolean,
        close: Function,
        message: Object,
        coldefs: Array, // Nieuwe coldefs prop
        sort: String,
        dashboards: Array,
        pinnedDef: Array,
    },
    setup(props, { emit }) {
        const form = useForm({
            message: "",
            width: "",
            title: "",
            col_def: "",
            dashboard_id: "", // Nieuwe dashboard_id field
        });

        const isDashboardDropdownOpen = ref(false);
        const selectedDashboard = ref(null);

        const setDefaultDashboard = (dashboards) => {
            if (dashboards && dashboards.length > 0) {
                // Zoek dashboard met default = 1
                const defaultDashboard = dashboards.find(
                    (dashboard) => dashboard.default === 1
                );

                if (defaultDashboard) {
                    selectedDashboard.value = defaultDashboard;
                    form.dashboard_id = defaultDashboard.id;
                } else {
                    // Als geen default gevonden, pak de eerste
                    selectedDashboard.value = dashboards[0];
                    form.dashboard_id = dashboards[0].id;
                }
            }
        };

        // Je bestaande watch voor chart title
        watch(
            () => [props.sort, props.message],
            ([newSort, newMessage]) => {
                if (
                    newSort === "chart" &&
                    newMessage?.selectedXAxis &&
                    newMessage?.selectedYAxis
                ) {
                    form.title = generateChartTitle(
                        newMessage.selectedXAxis,
                        newMessage.selectedYAxis
                    );
                }
            },
            { immediate: true }
        );

        // Watch voor dashboards - set default dashboard
        watch(
            () => props.dashboards,
            (newDashboards) => {
                setDefaultDashboard(newDashboards);
            },
            { immediate: true }
        );

        const toggleDashboardDropdown = () => {
            isDashboardDropdownOpen.value = !isDashboardDropdownOpen.value;
        };

        const closeDashboardDropdown = () => {
            isDashboardDropdownOpen.value = false;
        };

        const selectDashboard = (dashboard) => {
            selectedDashboard.value = dashboard;
            form.dashboard_id = dashboard.id;
            isDashboardDropdownOpen.value = false;
        };

        const sendFeedback = () => {
            // Optional: update message_id dynamically if it became available later
            if (props.message?.id) {
                form.message = props.message;
            }

            form.col_def = props.pinnedDef;

            const routeName =
                props.sort === "table"
                    ? "conversation.pinTable"
                    : "conversation.pinChart";

            form.post(route(routeName), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    setDefaultDashboard(props.dashboards);
                    props.close();

                    emit("item-pinned", {
                        message_id: props.message.id,
                        sort: props.sort,
                    });
                },
            });
        };

        const generateChartTitle = (categoryField, valueField) => {
            const categoryName = getFieldDisplayName(categoryField);
            const valueName = getFieldDisplayName(valueField);

            if (valueField === "__count") {
                return `Aantal per ${categoryName}`;
            } else if (categoryField === "__index") {
                return `${valueName} per Item`;
            } else {
                return `${valueName} per ${categoryName}`;
            }
        };

        const getFieldDisplayName = (fieldName) => {
            // Je bestaande field name mapping
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
        };

        const hasErrors = computed(() => Object.keys(form.errors).length > 0);

        // Initialize dashboard on mount
        onMounted(() => {
            setDefaultDashboard(props.dashboards);
        });

        return {
            form,
            sendFeedback,
            hasErrors,
            generateChartTitle,
            getFieldDisplayName,
            isDashboardDropdownOpen,
            selectedDashboard,
            toggleDashboardDropdown,
            closeDashboardDropdown,
            selectDashboard,
        };
    },
};
</script>
