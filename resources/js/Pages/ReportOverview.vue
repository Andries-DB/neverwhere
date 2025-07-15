<template>
    <Head title="Feedback" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-gray-900"
                    >
                        Feedback Beheer
                    </h1>
                    <p class="text-sm text-gray-600">
                        Beheer alle feature requests en bug reports
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex flex-wrap gap-4 items-center">
                    <!-- Type Filter -->
                    <div class="flex items-center gap-2 min-w-[200px]">
                        <label class="text-sm font-medium text-gray-700"
                            >Type</label
                        >
                        <select
                            v-model="filters.type"
                            class="h-9 px-3 py-2 text-sm border border-gray-200 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full"
                        >
                            <option value="">Alle</option>
                            <option value="feature">Features</option>
                            <option value="bug">Bugs</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div class="flex items-center gap-2 min-w-[200px]">
                        <label class="text-sm font-medium text-gray-700"
                            >Prioriteit</label
                        >
                        <select
                            v-model="filters.priority"
                            class="h-9 px-3 py-2 text-sm border border-gray-200 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full"
                        >
                            <option value="">Alle</option>
                            <option value="low">Laag</option>
                            <option value="medium">Normaal</option>
                            <option value="high">Hoog</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="flex items-center gap-2 flex-1 min-w-[100px]">
                        <label class="text-sm font-medium text-gray-700"
                            >Zoeken</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Zoek in titel of beschrijving..."
                            class="h-9 flex-1 px-3 py-2 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>

                    <!-- Clear Filters -->
                    <button
                        @click="clearFilters"
                        class="h-9 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors"
                    >
                        Wissen
                    </button>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="relative overflow-x-auto sm:rounded-lg">
                <div class="w-full text-sm text-left rtl:text-right">
                    <table class="w-full">
                        <thead
                            class="text-xs text-gray-700 uppercase bg-gray-50"
                        >
                            <tr>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Titel</th>
                                <th class="px-6 py-3">Prioriteit</th>
                                <th class="px-6 py-3">Datum</th>
                                <th class="px-6 py-3">Gebruiker</th>
                                <th class="px-6 py-3">Acties</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="request in filteredRequests"
                                :key="request.id"
                                class="bg-white border-b border-gray-200"
                            >
                                <!-- Type -->
                                <td class="px-6 py-4 text-start">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            request.type === 'feature'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        <div
                                            class="w-1.5 h-1.5 rounded-full mr-1.5"
                                            :class="
                                                request.type === 'feature'
                                                    ? 'bg-blue-500'
                                                    : 'bg-red-500'
                                            "
                                        ></div>
                                        {{
                                            request.type === "feature"
                                                ? "Feature"
                                                : "Bug"
                                        }}
                                    </span>
                                </td>

                                <!-- Title -->
                                <td class="px-6 py-4 text-start">
                                    <div class="max-w-xs">
                                        <div
                                            class="font-medium text-gray-900 truncate"
                                        >
                                            {{ request.title }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 truncate"
                                        >
                                            {{ request.description }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Priority -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="request.priority"
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            getPriorityClass(request.priority),
                                        ]"
                                    >
                                        {{ getPriorityLabel(request.priority) }}
                                    </span>
                                    <span v-else class="text-gray-400 text-xs"
                                        >-</span
                                    >
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(request.created_at) }}
                                </td>

                                <!-- User -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center"
                                        >
                                            <span
                                                class="text-xs font-medium text-gray-600"
                                            >
                                                {{
                                                    getUserInitials(
                                                        request.user?.name
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    request.user
                                                        ? `${
                                                              request.user
                                                                  .firstname ??
                                                              ""
                                                          } ${
                                                              request.user
                                                                  .name ?? ""
                                                          }`.trim() ||
                                                          "Onbekend"
                                                        : "Onbekend"
                                                }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ request.user?.email || "" }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right text-sm">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <button
                                            @click="viewDetails(request)"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded-md hover:bg-blue-50 transition-colors"
                                            title="Bekijk details"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </button>

                                        <button
                                            @click="deleteRequest(request.id)"
                                            class="p-1.5 text-gray-400 hover:text-red-600 rounded-md hover:bg-red-50 transition-colors"
                                            title="Verwijderen"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredRequests.length === 0"
                    class="text-center py-12 bg-white"
                >
                    <div
                        class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        Geen feedback gevonden
                    </h3>
                    <p class="text-gray-500 text-sm">
                        Er zijn geen feedback items die voldoen aan de huidige
                        filters.
                    </p>
                </div>
            </div>

            <!-- Detail Modal -->
            <div
                v-if="selectedRequest"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click="closeDetails"
            >
                <div
                    class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto"
                    @click.stop
                >
                    <div class="p-6">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-1 rounded-md text-sm font-medium',
                                        selectedRequest.type === 'feature'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-red-100 text-red-700',
                                    ]"
                                >
                                    <div
                                        class="w-1.5 h-1.5 rounded-full mr-1.5"
                                        :class="
                                            selectedRequest.type === 'feature'
                                                ? 'bg-blue-500'
                                                : 'bg-red-500'
                                        "
                                    ></div>
                                    {{
                                        selectedRequest.type === "feature"
                                            ? "Feature Request"
                                            : "Bug Report"
                                    }}
                                </span>
                                <span
                                    v-if="selectedRequest.priority"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 rounded-md text-sm font-medium',
                                        getPriorityClass(
                                            selectedRequest.priority
                                        ),
                                    ]"
                                >
                                    {{
                                        getPriorityLabel(
                                            selectedRequest.priority
                                        )
                                    }}
                                </span>
                            </div>
                            <button
                                @click="closeDetails"
                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100 transition-colors"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Title -->
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            {{ selectedRequest.title }}
                        </h2>

                        <!-- Description -->
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">
                                Beschrijving
                            </h3>
                            <p
                                class="text-gray-600 text-sm leading-relaxed whitespace-pre-wrap"
                            >
                                {{ selectedRequest.description }}
                            </p>
                        </div>

                        <!-- Steps (for bugs) -->
                        <div
                            v-if="
                                selectedRequest.type === 'bug' &&
                                selectedRequest.steps
                            "
                            class="mb-6"
                        >
                            <h3 class="text-sm font-medium text-gray-700 mb-2">
                                Stappen om te reproduceren
                            </h3>
                            <p
                                class="text-gray-600 text-sm leading-relaxed whitespace-pre-wrap"
                            >
                                {{ selectedRequest.steps }}
                            </p>
                        </div>

                        <!-- Environment -->
                        <div v-if="selectedRequest.environment" class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">
                                Omgeving
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ selectedRequest.environment }}
                            </p>
                        </div>

                        <!-- Meta Info -->
                        <div
                            class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200"
                        >
                            <div>
                                <h3
                                    class="text-sm font-medium text-gray-700 mb-1"
                                >
                                    Ingediend door
                                </h3>
                                <p class="text-gray-900 text-sm">
                                    {{
                                        selectedRequest.user
                                            ? `${
                                                  selectedRequest.user
                                                      .firstname ?? ""
                                              } ${
                                                  selectedRequest.user.name ??
                                                  ""
                                              }`.trim() || "Onbekend"
                                            : "Onbekend"
                                    }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ selectedRequest.user?.email || "" }}
                                </p>
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-medium text-gray-700 mb-1"
                                >
                                    Datum
                                </h3>
                                <p class="text-gray-900 text-sm">
                                    {{ formatDate(selectedRequest.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

export default {
    name: "AdminFeedbackDashboard",
    components: {
        AuthenticatedLayout,
        Head,
    },
    props: {
        requests: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            filters: {
                type: "",
                priority: "",
                search: "",
            },
            selectedRequest: null,
        };
    },
    computed: {
        filteredRequests() {
            return this.requests.filter((request) => {
                const matchesType =
                    !this.filters.type || request.type === this.filters.type;
                const matchesPriority =
                    !this.filters.priority ||
                    request.priority === this.filters.priority;
                const matchesSearch =
                    !this.filters.search ||
                    request.title
                        .toLowerCase()
                        .includes(this.filters.search.toLowerCase()) ||
                    request.description
                        .toLowerCase()
                        .includes(this.filters.search.toLowerCase());

                return matchesType && matchesPriority && matchesSearch;
            });
        },
    },
    methods: {
        clearFilters() {
            this.filters = {
                type: "",
                priority: "",
                search: "",
            };
        },

        getPriorityClass(priority) {
            const classes = {
                low: "bg-green-100 text-green-700",
                medium: "bg-yellow-100 text-yellow-700",
                high: "bg-red-100 text-red-700",
            };
            return classes[priority] || "bg-gray-100 text-gray-700";
        },

        getPriorityLabel(priority) {
            const labels = {
                low: "Laag",
                medium: "Normaal",
                high: "Hoog",
            };
            return labels[priority] || priority;
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("nl-NL", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        getUserInitials(name) {
            if (!name) return "?";
            return name
                .split(" ")
                .map((n) => n[0])
                .join("")
                .toUpperCase()
                .slice(0, 2);
        },

        viewDetails(request) {
            this.selectedRequest = request;
        },

        closeDetails() {
            this.selectedRequest = null;
        },

        async deleteRequest(requestId) {
            if (
                !confirm("Weet je zeker dat je deze feedback wilt verwijderen?")
            ) {
                return;
            }

            try {
                await this.$inertia.delete(`/feedback/admin/${requestId}`);
                const index = this.requests.findIndex(
                    (r) => r.id === requestId
                );
                if (index > -1) {
                    this.requests.splice(index, 1);
                }
            } catch (error) {
                console.error("Error deleting request:", error);
            }
        },
    },
};
</script>
