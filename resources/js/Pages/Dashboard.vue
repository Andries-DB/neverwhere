<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <main class="space-y-6 sm:space-y-8 px-4 sm:px-0">
            <!-- Nieuwe conversatie knop -->
            <div class="flex justify-between items-center">
                <h1 class="text-black font-bold text-3xl sm:text-4xl">
                    Dashboard
                </h1>

                <button
                    @click="createConversation()"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium hover:bg-blue-700 transition"
                >
                    + Nieuwe conversatie
                </button>
            </div>

            <!-- Overzicht conversaties -->
            <section class="bg-white rounded-md border border-gray-200">
                <header class="border-b border-gray-100 px-4 sm:px-6 py-4">
                    <h2
                        class="text-base sm:text-lg font-semibold text-gray-800"
                    >
                        Recente conversaties
                    </h2>
                </header>

                <div>
                    <!-- Echte conversaties -->
                    <a
                        v-for="conv in conversations"
                        :key="conv.id"
                        :href="route('conversation.read', conv.guid)"
                        class="cursor-pointer border-b last:border-none hover:bg-gray-50 transition px-4 sm:px-6 py-4 flex justify-between items-start sm:items-center flex-wrap sm:flex-nowrap gap-2"
                    >
                        <div class="w-full sm:w-auto flex-1">
                            <p class="font-medium text-gray-900 truncate">
                                {{ conv.title }}
                            </p>
                            <p class="text-gray-500 text-sm truncate max-w-lg">
                                {{
                                    conv.lastMessage ??
                                    "Stuur een bericht om de chat te starten"
                                }}
                            </p>
                        </div>
                        <time
                            class="text-gray-400 text-xs whitespace-nowrap"
                            v-if="conv.lastMessage"
                        >
                            {{ formatDate(conv.updatedAt) }}
                        </time>
                    </a>

                    <!-- Skeleton placeholders -->
                    <div
                        v-for="n in numberOfSkeletons"
                        :key="'skeleton-' + n"
                        class="animate-pulse border-b last:border-none px-4 sm:px-6 py-4 flex justify-between items-start sm:items-center"
                    >
                        <div class="space-y-2 w-full sm:w-auto flex-1">
                            <div class="h-4 w-1/4 bg-gray-200 rounded"></div>
                            <div class="h-3 w-2/4 bg-gray-100 rounded"></div>
                        </div>
                        <div class="h-3 w-24 bg-gray-100 rounded"></div>
                    </div>
                </div>
            </section>

            <!-- Welkomstbericht -->
            <section
                class="text-center text-gray-600 max-w-md mx-auto px-4 sm:px-0"
            >
                <p>
                    Start een nieuwe conversatie door op de knop hierboven te
                    klikken.<br />
                    Je recente gesprekken verschijnen hier.
                </p>
            </section>
        </main>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { format, isToday, parseISO } from "date-fns"; // date-fns library, handig voor datumformattering

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    props: {
        conversations: Array,
    },
    data() {
        return {
            form: useForm({}),
            breadcrumbs: [{ title: "Dashboard", href: "/dashboard" }],
        };
    },
    methods: {
        createConversation() {
            this.form.post(route("conversation.create"), {
                onSuccess: () => {
                    location.reload();
                },
                onError: (errors) => {
                    console.log(errors);
                },
            });
        },
        formatDate(dateString) {
            const date = parseISO(dateString); // parseert ISO string naar Date object

            if (isToday(date)) {
                // Als het vandaag is: toon enkel het uur, bv. 14:35
                return format(date, "HH:mm");
            } else {
                // Anders enkel dag, bv. 2025-06-16 of 16 jun
                return format(date, "dd MMM");
            }
        },
    },
    computed: {
        numberOfSkeletons() {
            return Math.max(0, 3 - this.conversations.length);
        },
    },
};
</script>
