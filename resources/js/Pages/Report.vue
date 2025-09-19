<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-xl font-bold text-slate-900 mb-2">
                {{ $t("reports.title") }}
            </h1>
            <p class="text-slate-600 text-sm">
                {{ $t("reports.description") }}
            </p>
        </div>

        <!-- Success Message -->
        <div
            v-if="showSuccess"
            class="mb-2 p-4 bg-green-50 border border-green-200 rounded-md"
        >
            <div class="flex items-center">
                <svg
                    class="w-5 h-5 text-green-600 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                <span class="text-green-800 font-medium">
                    {{ $t("reports.thanks") }}
                </span>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitReport" class="space-y-6">
            <!-- Type Selection -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-3">
                    {{ $t("reports.type") }}
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="form.type = 'feature'"
                        :class="[
                            'px-2 py-4 border rounded-md text-left transition-all duration-200',
                            form.type === 'feature'
                                ? 'border-blue-500 bg-blue-50 text-blue-900'
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <div class="flex items-center mb-2">
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                />
                            </svg>
                            <span class="font-medium">
                                {{ $t("reports.feature") }}</span
                            >
                        </div>
                        <p class="text-xs text-slate-600">
                            {{ $t("reports.feature_description") }}
                        </p>
                    </button>

                    <button
                        type="button"
                        @click="form.type = 'bug'"
                        :class="[
                            'px-2 py-4 border rounded-md text-left transition-all duration-200',
                            form.type === 'bug'
                                ? 'border-red-500 bg-red-50 text-red-900'
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <div class="flex items-center mb-2">
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                            <span class="font-medium">
                                {{ $t("reports.bug") }}</span
                            >
                        </div>
                        <p class="text-xs text-slate-600">
                            {{ $t("reports.bug_description") }}
                        </p>
                    </button>
                </div>
                <div v-if="errors.type" class="mt-1 text-sm text-red-600">
                    {{ errors.type }}
                </div>
            </div>

            <!-- Priority (only for bugs) -->
            <div v-if="form.type === 'bug'">
                <label class="block text-sm font-medium text-slate-700 mb-3">
                    {{ $t("reports.prio") }}
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <button
                        type="button"
                        @click="form.priority = 'low'"
                        :class="[
                            'p-2 border rounded-md text-center transition-all duration-200',
                            form.priority === 'low'
                                ? 'border-green-500 bg-green-50 text-green-900'
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <div class="text-sm font-medium">
                            {{ $t("reports.low.title") }}
                        </div>
                        <div class="text-xs text-slate-600">
                            {{ $t("reports.low.description") }}
                        </div>
                    </button>

                    <button
                        type="button"
                        @click="form.priority = 'medium'"
                        :class="[
                            'p-2 border rounded-md text-center transition-all duration-200',
                            form.priority === 'medium'
                                ? 'border-yellow-500 bg-yellow-50 text-yellow-900'
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <div class="text-sm font-medium">
                            {{ $t("reports.medium.title") }}
                        </div>
                        <div class="text-xs text-slate-600">
                            {{ $t("reports.medium.description") }}
                        </div>
                    </button>

                    <button
                        type="button"
                        @click="form.priority = 'high'"
                        :class="[
                            'p-2 border rounded-md text-center transition-all duration-200',
                            form.priority === 'high'
                                ? 'border-red-500 bg-red-50 text-red-900'
                                : 'border-slate-200 hover:border-slate-300',
                        ]"
                    >
                        <div class="text-sm font-medium">
                            {{ $t("reports.high.title") }}
                        </div>
                        <div class="text-xs text-slate-600">
                            {{ $t("reports.high.description") }}
                        </div>
                    </button>
                </div>
            </div>

            <!-- Title -->
            <div>
                <label
                    for="title"
                    class="block text-sm font-medium text-slate-700 mb-2"
                >
                    {{
                        form.type === "feature"
                            ? $t("reports.feature_title")
                            : $t("reports.detail_description")
                    }}
                </label>
                <TextInput
                    v-model="form.title"
                    :label="$t('reports.title')"
                    :placeholder="$t('reports.title')"
                    class="w-full"
                />

                <div v-if="errors.title" class="mt-1 text-sm text-red-600">
                    {{ errors.title }}
                </div>
            </div>

            <!-- Description -->
            <div>
                <label
                    for="description"
                    class="block text-sm font-medium text-slate-700 mb-2"
                >
                    {{ $t("reports.detail_description") }}
                </label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="6"
                    :placeholder="
                        form.type === 'feature'
                            ? $t('reports.feature_detail_description')
                            : $t('reports.bug_detail_description')
                    "
                    class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent resize-none"
                />
                <div
                    v-if="errors.description"
                    class="mt-1 text-sm text-red-600"
                >
                    {{ errors.description }}
                </div>
            </div>

            <!-- Steps to reproduce (only for bugs) -->
            <div v-if="form.type === 'bug'">
                <label
                    for="steps"
                    class="block text-sm font-medium text-slate-700 mb-2"
                >
                    {{ $t("reports.steps") }}
                </label>
                <textarea
                    id="steps"
                    v-model="form.steps"
                    rows="4"
                    :placeholder="$t('reports.stepsplaceholder')"
                    class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent resize-none"
                />
            </div>

            <!-- Browser/Device info -->
            <div>
                <label
                    for="environment"
                    class="block text-sm font-medium text-slate-700 mb-2"
                >
                    {{ $t("reports.environment") }}
                </label>
                <TextInput
                    v-model="form.environment"
                    :placeholder="browserInfo"
                    class="w-full"
                />

                <p class="mt-1 text-xs text-slate-500">
                    {{ $t("reports.environment_sublabel") }}
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4">
                <button
                    type="button"
                    @click="resetForm"
                    class="px-4 py-2 text-sm bg-slate-100 rounded-md text-slate-600 hover:text-slate-800 hover:bg-slate-200 transition-colors duration-200"
                >
                    {{ $t("reports.delete") }}
                </button>

                <button
                    type="submit"
                    :disabled="isSubmitting"
                    :class="[
                        'px-4 py-2 rounded-md transition-all duration-200 text-sm',
                        form.type === 'feature'
                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                            : 'bg-red-600 hover:bg-red-700 text-white',
                        isSubmitting
                            ? 'opacity-50 cursor-not-allowed'
                            : 'shadow-sm hover:shadow-md',
                    ]"
                >
                    <span v-if="isSubmitting" class="flex items-center">
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        {{ $t("reports.submitting") }}...
                    </span>
                    <span v-else>
                        {{
                            form.type === "feature"
                                ? $t("reports.feature_submit")
                                : $t("reports.bug_submit")
                        }}
                    </span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script>
import TextInput from "@/Components/TextInput.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

export default {
    name: "FeedbackForm",
    components: {
        AuthenticatedLayout,
        Head,
        TextInput,
    },
    data() {
        return {
            form: {
                type: "",
                title: "",
                description: "",
                steps: "",
                priority: "medium",
                environment: "",
            },
            errors: {},
            isSubmitting: false,
            showSuccess: false,
        };
    },
    computed: {
        browserInfo() {
            const ua = navigator.userAgent;
            const browser = ua.includes("Chrome")
                ? "Chrome"
                : ua.includes("Firefox")
                ? "Firefox"
                : ua.includes("Safari")
                ? "Safari"
                : "Unknown";
            const os = ua.includes("Windows")
                ? "Windows"
                : ua.includes("Mac")
                ? "macOS"
                : ua.includes("Linux")
                ? "Linux"
                : "Unknown";
            return `${browser} op ${os}`;
        },
    },
    methods: {
        async submitReport() {
            this.errors = {};

            // Validation
            if (!this.form.type) {
                this.errors.type = "Selecteer een type melding";
                return;
            }

            if (!this.form.title.trim()) {
                this.errors.title = "Titel is verplicht";
                return;
            }

            if (!this.form.description.trim()) {
                this.errors.description = "Beschrijving is verplicht";
                return;
            }

            this.isSubmitting = true;

            try {
                // Here you would make the actual API call:
                await this.$inertia.post("/feedback", this.form);

                this.showSuccess = true;
                this.resetForm();

                // Hide success message after 5 seconds
                setTimeout(() => {
                    this.showSuccess = false;
                }, 5000);
            } catch (error) {
                console.error("Error submitting feedback:", error);
                // Handle error appropriately
            } finally {
                this.isSubmitting = false;
            }
        },

        resetForm() {
            this.form = {
                type: "",
                title: "",
                description: "",
                steps: "",
                priority: "medium",
                environment: "",
            };
            this.errors = {};
        },
    },
};
</script>
