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
                                        <!-- Dr. Itchy Summary Button -->
                                        <button
                                            :class="[
                                                'absolute -top-10 right-14 z-10 transition-all duration-200 ease-in-out',
                                                'w-9 h-9 rounded-full shadow-md flex items-center justify-center border',
                                                'bg-yellow-400 text-yellow-800 border-yellow-500 hover:bg-yellow-500',
                                                loadingSummary &&
                                                loadingSummary.id === message.id
                                                    ? 'animate-pulse'
                                                    : '',
                                            ]"
                                            @click="
                                                generateSummary(
                                                    message,
                                                    'chart'
                                                )
                                            "
                                            title="Dr. Itchy Samenvatting"
                                            :disabled="
                                                loadingSummary &&
                                                loadingSummary.id === message.id
                                            "
                                        >
                                            <i
                                                :class="[
                                                    'text-base',
                                                    loadingSummary &&
                                                    loadingSummary.id ===
                                                        message.id
                                                        ? 'fas fa-spinner fa-spin'
                                                        : 'fas fa-user-md',
                                                ]"
                                            ></i>
                                        </button>

                                        <!-- Pin button-->
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
                                            @click="
                                                togglePinModal(message, 'chart')
                                            "
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
                                <div v-show="message.displayAsTable">
                                    <div class="mb-3 flex justify-end relative">
                                        <button
                                            :class="[
                                                'absolute -top-10 right-14 z-10 transition-all duration-200 ease-in-out',
                                                'w-9 h-9 rounded-full shadow-md flex items-center justify-center border',
                                                'bg-yellow-400 text-yellow-800 border-yellow-500 hover:bg-yellow-500',
                                                loadingSummary &&
                                                loadingSummary.id === message.id
                                                    ? 'animate-pulse'
                                                    : '',
                                            ]"
                                            @click="
                                                generateSummary(
                                                    message,
                                                    'table'
                                                )
                                            "
                                            title="Dr. Itchy Samenvatting"
                                            :disabled="
                                                loadingSummary &&
                                                loadingSummary.id === message.id
                                            "
                                        >
                                            <i
                                                :class="[
                                                    'text-base',
                                                    loadingSummary &&
                                                    loadingSummary.id ===
                                                        message.id
                                                        ? 'fas fa-spinner fa-spin'
                                                        : 'fas fa-user-md',
                                                ]"
                                            ></i>
                                        </button>

                                        <!-- Pin button-->
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
                                            @click="
                                                togglePinModal(message, 'table')
                                            "
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

                                    <TableBuilder :message="message" />
                                </div>

                                <div
                                    v-if="message.displayAsQuery"
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
                                <div
                                    v-if="
                                        !message.displayAsChart &&
                                        !message.displayAsTable &&
                                        !message.displayAsQuery
                                    "
                                    v-html="formatMessage(message)"
                                    class="whitespace-pre-wrap"
                                ></div>

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

        <div
            v-if="showSummaryModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeSummaryModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4 flex items-center justify-between"
                >
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center"
                        >
                            <i class="fas fa-user-md text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">
                                Dr. Itchy zegt:
                            </h3>
                            <p class="text-sm opacity-90">
                                AI-gegenereerde samenvatting
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeSummaryModal"
                        class="text-white hover:text-gray-200 transition-colors"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <div
                        v-if="summaryLoading"
                        class="flex items-center justify-center py-8"
                    >
                        <div
                            class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"
                        ></div>
                        <span class="ml-3 text-gray-600"
                            >Dr. Itchy analyseert de data...</span
                        >
                    </div>

                    <div
                        v-else-if="summaryContent"
                        class="prose prose-sm max-w-none"
                    >
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Analyse van
                                {{
                                    summaryType === "chart"
                                        ? "grafiek"
                                        : "tabel"
                                }}
                            </p>
                        </div>

                        <div
                            class="text-gray-800 leading-relaxed whitespace-pre-wrap"
                        >
                            {{ summaryContent }}
                        </div>
                    </div>

                    <div v-else-if="summaryError" class="text-center py-8">
                        <i
                            class="fas fa-exclamation-triangle text-red-500 text-3xl mb-3"
                        ></i>
                        <p class="text-red-600 mb-4">
                            Er ging iets mis bij het genereren van de
                            samenvatting
                        </p>
                        <p class="text-sm text-gray-600">{{ summaryError }}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button
                        v-if="summaryContent"
                        @click="copySummary"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center space-x-2"
                    >
                        <i class="fas fa-copy"></i>
                        <span>Kopiëren</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <Create :close="toggleAddCompany" :show="addCompany" />
    <DislikeModal
        :show="dislikeModal"
        :close="toggleDislikeModal"
        :message="dislikedMessage"
        :sort="dislikedSort"
    />
    <PinItem
        :show="pinModal"
        :close="togglePinModal"
        :message="pinnedMessage"
        :sort="pinnedSort"
        :dashboards="dashboards"
        @item-pinned="handleItemPinned"
    />
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Snackbar from "@/Components/Snackbar.vue";
import DislikeModal from "@/Components/Modals/DislikeModal.vue";
import ChartBuilder from "@/Components/ChartBuilder.vue";
import PinItem from "@/Components/Modals/PinItem.vue";
import TableBuilder from "@/Components/TableBuilder.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        Snackbar,
        DislikeModal,
        ChartBuilder,
        PinItem,
        TableBuilder,
    },
    props: {
        conversation: Object,
        pinned_charts: Array,
        pinned_tables: Array,
        dashboards: Array,
    },
    data() {
        return {
            loading: false,
            showSummaryModal: false,
            summaryContent: null,
            summaryError: null,
            summaryLoading: false,
            summaryType: null,
            loadingSummary: null,
            form: useForm({
                message: "",
                conversation: this.conversation,
                source: null,
            }),
            error: "",
            no_message: false,
            messages: [],
            pinnedCharts: [],
            pinnedTables: [],
            clickedMessageId: null,
            dislikeModal: false,
            dislikedMessage: null,
            dislikedSort: "",
            pinnedInformation: [],
            pinnedMessage: null,
            pinnedSort: "",
            pinModal: false,
        };
    },
    methods: {
        toggleDislikeModal(message, sort = "dislike") {
            this.dislikedMessage = message;
            this.dislikedSort = sort;
            this.dislikeModal = !this.dislikeModal;
        },

        async generateSummary(message, type) {
            this.loadingSummary = { id: message.id, type };
            this.summaryType = type;
            this.showSummaryModal = true;
            this.summaryLoading = true;
            this.summaryContent = null;
            this.summaryError = null;

            try {
                // Prepare data for the API call
                const data = {
                    type: type,
                    message_id: message.id,
                    data: message.json,
                    // Add any additional context needed
                    context: {
                        xAxis: message.selectedXAxis,
                        yAxis: message.selectedYAxis,
                        chartType: message.chartType,
                    },
                };

                // Replace with your actual API endpoint
                const response = await fetch("/dr-itchy/summary", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json", // <-- voeg deze toe
                        "X-CSRF-TOKEN": this.getCsrfToken(),
                    },
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                this.updateCsrfToken(response);

                const result = await response.json();
                this.summaryContent = result.summary;
            } catch (error) {
                console.error("Error generating summary:", error);
                this.summaryError = error.message;
            } finally {
                this.summaryLoading = false;
                this.loadingSummary = null;
            }
        },

        closeSummaryModal() {
            this.showSummaryModal = false;
            this.summaryContent = null;
            this.summaryError = null;
            this.summaryLoading = false;
            this.summaryType = null;
        },

        async copySummary() {
            try {
                await navigator.clipboard.writeText(this.summaryContent);
                // You might want to show a toast notification here
                console.log("Summary copied to clipboard");
            } catch (error) {
                console.error("Failed to copy summary:", error);
            }
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
        togglePinModal(message, sort) {
            this.pinnedMessage = message;
            this.pinnedSort = sort;

            this.pinModal = !this.pinModal;
        },
        handleItemPinned({ message_id, sort }) {
            if (sort === "chart") {
                this.pinnedCharts.push({ messageId: message_id });

                this.$refs.snackbar.show(
                    "Grafiek is vastgezet op het dashboard!",
                    "success"
                );
            } else {
                this.pinnedTables.push({ messageId: message_id });

                this.$refs.snackbar.show(
                    "Tabel is vastgezet op het dashboard!",
                    "success"
                );
            }
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

        // Helpers
        autoResize() {
            const textarea = this.$refs.messageTextarea;
            if (textarea) {
                textarea.style.height = "auto"; // Reset
                textarea.style.height = textarea.scrollHeight + "px"; // Groei
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

        // CSRF Token functions
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
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": this.getCsrfToken(),
                        },
                    }
                );

                if (!userresponse.ok)
                    throw new Error("Network response was not ok");

                this.updateCsrfToken(userresponse);

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
                            headers: {
                                Accept: "application/json",
                                "X-CSRF-TOKEN": this.getCsrfToken(),
                            },
                        }
                    );

                    if (!botresponse.ok)
                        throw new Error("Network response was not ok");

                    this.updateCsrfToken(botresponse);

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
        async onThumbsUp(message) {
            this.toggleDislikeModal(message, "like");

            try {
                const index = this.messages.findIndex(
                    (m) => m.id === message.id
                );

                if (index !== -1) {
                    this.messages[index].thumbs_up = 1;
                    this.messages[index].thumbs_down = 0;
                }
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
    },
    commputed: {},
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
