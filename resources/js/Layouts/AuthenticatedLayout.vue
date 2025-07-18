<script setup>
import { ref, onMounted, watch, computed, onUnmounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { defineProps } from "vue";

const showingNavigationDropdown = ref(false);
const sidebarCollapsed = ref(false);
const page = usePage();
const openDropdown = ref(null);
const editingConversation = ref(null);
const editingName = ref("");
let displayMode = ref("unknown");
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

let deferredPrompt = ref(null);
let showPWA = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const props = defineProps({
    breadcrumbs: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const filteredSidebarItems = computed(() => {
    if (page.props.auth.user.role === "admin") {
        return sidebarItems;
    } else if (page.props.auth.user.role === "user") {
        return sidebarItems.filter((item) => item.role === "user");
    }
    return [];
});

const toggleDropdown = (convId) => {
    openDropdown.value = openDropdown.value === convId ? null : convId;
};

const toggleSettingsDropdown = () => {
    if (!props.sidebarCollapsed) {
        dropdownOpen.value = !dropdownOpen.value;
    }
};

const closeDropdown = () => {
    openDropdown.value = null;
};

const closeSettingsDropdown = () => {
    dropdownOpen.value = false;
};

const form = useForm({
    title: "",
});

// Sidebar menu items
const sidebarItems = [
    // {
    //     name: "Dashboard",
    //     route: "dashboard",
    //     routes: ["dashboard"],
    //     icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
    //     role: "user",
    // },
    {
        name: "Bedrijven",
        route: "company.get",
        routes: ["company.get", "company.read", "company.user.read"],
        icon: "M3 21h18M5 21V7l8-4v18M9 9h1m0 4h1m4-4h1m0 4h1",
        role: "admin",
    },
    {
        name: "Gebruikers",
        route: "user.get",
        routes: ["user.get", "user.read"],
        icon: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z",
        role: "admin",
    },
    {
        name: "Logs",
        route: "logs.get",
        routes: ["logs.get"],
        icon: "M15 12h4.5M19.5 12l-2-2m2 2l-2 2M16 6a4 4 0 1 0-8 0 4 4 0 0 0 8 0zM4 18a6 6 0 0 1 12 0v1H4v-1z",

        role: "admin",
    },
    {
        name: "Feedback",
        route: "reports.get.admin",
        routes: ["reports.get.admin"],
        icon: "M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",

        role: "admin",
    },
    // {
    //     name: "Rapporten",
    //     route: "reports.get",
    //     routes: ["reports.get"],
    //     icon: "M6 2a1 1 0 00-1 1v18a1 1 0 001.447.894l5.553-2.776 5.553 2.776A1 1 0 0020 21V3a1 1 0 00-1-1H6zm1 2h10v15.382l-4.553-2.276a1 1 0 00-.894 0L7 19.382V4z",

    //     role: "user",
    // },
    // {
    //     name: "Tweestapsverificatie",
    //     route: "two-factor.setup",
    //     routes: ["two-factor.setup", "two-factor.manage"],
    //     icon: "M12 17a1.5 1.5 0 100-3 1.5 1.5 0 000 3z M16.5 10V7a4.5 4.5 0 10-9 0v3m-1.5 0a1.5 1.5 0 00-1.5 1.5v7A1.5 1.5 0 005.5 20h13a1.5 1.5 0 001.5-1.5v-7a1.5 1.5 0 00-1.5-1.5h-13z",

    //     role: "user",
    // },
];

const isActive = (routes) => {
    return routes.some((r) => route().current(r));
};

function createConversation() {
    form.post(route("conversation.create"), {
        onSuccess: () => {
            location.reload();
        },
        onError: (errors) => {
            // errors afhandelen
            console.log(errors);
        },
    });
}

const deleteConversation = (guid) => {
    form.delete(route("conversation.delete", guid), {
        onSuccess: () => {
            // Optioneel: success message
            console.log("Conversatie verwijderd");
        },
        onError: () => {
            // Optioneel: error message
            console.log("Kon conversatie niet verwijderen");
        },
    });

    openDropdown.value = null;
};

// Start editing conversation name
const updateConversation = async (guid) => {
    const conversation = page.props.conversations?.find(
        (conv) => conv.guid === guid
    );
    if (conversation) {
        editingConversation.value = guid;
        editingName.value = conversation.title || "";
        openDropdown.value = null; // Close dropdown

        // Focus on input field after render
        await nextTick();
        const input = document.querySelector(`[ref="edit-input-${guid}"]`);
        if (input) {
            input.focus();
            input.select();
        }
    }
};

const saveConversationName = async (guid) => {
    if (!editingName.value.trim()) {
        cancelEdit();
        return;
    }

    form.title = editingName.value.trim();

    try {
        form.patch(route("conversation.update", guid), {
            preserveScroll: true,
            onSuccess: () => location.reload(),
            onError: (errors) => console.error("Fout:", errors),
        });
    } catch (error) {
        console.error("Fout bij opslaan conversatienaam:", error);
        cancelEdit();
    }
};

// Cancel editing
const cancelEdit = () => {
    editingConversation.value = null;
    editingName.value = "";
};

// Handle keyboard shortcuts
const handleKeydown = (event, guid) => {
    if (event.key === "Enter") {
        event.preventDefault();
        saveConversationName(guid);
    } else if (event.key === "Escape") {
        event.preventDefault();
        cancelEdit();
    }
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        closeDropdown();
    }
};

function getPWADisplayMode() {
    if (document.referrer.startsWith("android-app://")) return "twa";
    if (window.matchMedia("(display-mode: browser)").matches) return "browser";
    if (window.matchMedia("(display-mode: standalone)").matches)
        return "standalone";
    if (window.matchMedia("(display-mode: minimal-ui)").matches)
        return "minimal-ui";
    if (window.matchMedia("(display-mode: fullscreen)").matches)
        return "fullscreen";
    if (window.matchMedia("(display-mode: window-controls-overlay)").matches)
        return "window-controls-overlay";
    return "unknown";
}

const isMobileBrowser = computed(() => {
    return /Mobi|Android/i.test(navigator.userAgent);
});

const isPWA = computed(() => {
    return (
        isMobileBrowser.value &&
        displayMode.value !== "browser" &&
        displayMode.value !== "unknown"
    );
});

onMounted(() => {
    document.addEventListener("click", handleClickOutside);

    const saved = localStorage.getItem("sidebarCollapsed");
    if (saved !== null) {
        sidebarCollapsed.value = JSON.parse(saved);
    }
});

onUnmounted(() => {
    displayMode.value = getPWADisplayMode();

    if (!localStorage.getItem("pwaPromptShown")) {
        showPWA.value = true;
    }

    window.addEventListener("beforeinstallprompt", (event) => {
        event.preventDefault();
        deferredPrompt.value = event;
    });

    document.removeEventListener("click", closeDropdown);
    document.removeEventListener("click", handleClickOutside);
});

// Watch for changes and save to localStorage
watch(sidebarCollapsed, (newValue) => {
    localStorage.setItem("sidebarCollapsed", JSON.stringify(newValue));
});
</script>

<template>
    <div class="flex h-screen bg-slate-50 overflow-hidden">
        <!-- Sidebar -->
        <div
            :class="[
                'bg-white border-r border-slate-200 transition-all duration-300 ease-in-out flex flex-col',
                // Mobile behavior - slide in from left when expanded
                sidebarCollapsed
                    ? '-translate-x-full md:translate-x-0 md:w-20'
                    : 'translate-x-0 w-80 md:w-64',
                'fixed inset-y-0 left-0 z-50 md:relative md:z-auto',
            ]"
        >
            <!-- Sidebar Header -->
            <div
                class="flex items-center px-4 h-16 justify-center w-full border-b border-slate-200"
            >
                <a
                    :class="[
                        'flex items-center  min-w-0 flex-1',
                        sidebarCollapsed ? 'justify-center' : 'justify-start',
                    ]"
                    :href="route('dashboard')"
                >
                    <img src="/nw_logo.png" alt="Logo" class="w-8" />

                    <span
                        v-if="!sidebarCollapsed"
                        class="ml-3 text-lg font-semibold text-slate-900 truncate"
                    >
                        Neverwhere
                    </span>
                </a>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <template
                    v-for="item in filteredSidebarItems"
                    :key="item.route"
                >
                    <div class="relative">
                        <Link
                            :href="route(item.routes[0])"
                            :class="[
                                'flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-all duration-200 group relative',
                                isActive(item.routes)
                                    ? 'bg-blue-50 text-blue-700 shadow-sm'
                                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900',
                            ]"
                        >
                            <svg
                                class="w-5 h-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    :d="item.icon"
                                />
                            </svg>
                            <span
                                v-if="!sidebarCollapsed"
                                class="ml-3 truncate"
                            >
                                {{ item.name }}
                            </span>

                            <!-- Active indicator -->
                            <div
                                v-if="isActive(item.routes)"
                                class="absolute right-0 top-1/2 -translate-y-1/2 w-0.5 h-6 bg-blue-600 rounded-l-full"
                            ></div>
                        </Link>
                    </div>
                </template>

                <div class="space-y-1">
                    <div
                        class="text-xs font-semibold text-slate-400 uppercase tracking-wide px-3 mt-4"
                        v-if="!sidebarCollapsed"
                    >
                        Mijn conversaties
                    </div>
                    <div
                        class="text-xs font-semibold text-slate-400 uppercase tracking-wide flex justify-center mt-4"
                        v-else
                    >
                        <i class="far fa-comments text-base"></i>
                    </div>

                    <div>
                        <button
                            @click="createConversation"
                            :disabled="form.processing"
                            class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-all duration-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900 w-full mb-2"
                        >
                            <svg
                                class="w-5 h-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            <span
                                v-if="!sidebarCollapsed"
                                class="ml-3 truncate"
                            >
                                Nieuwe conversatie
                            </span>
                        </button>

                        <div
                            v-if="$page.props.conversations?.length"
                            class="space-y-0.5"
                        >
                            <div
                                v-for="conv in $page.props.conversations"
                                :key="conv.id"
                                class="group relative flex items-center rounded-md transition-all duration-200 pr-1"
                                :class="{
                                    'bg-blue-50 shadow-sm': route().current(
                                        'conversation.read',
                                        conv.guid
                                    ),
                                    'hover:bg-slate-100':
                                        !$page.props.currentConversationId ||
                                        $page.props.conversations !== conv.id,
                                }"
                            >
                                <!-- Normal state - Link wrapper -->
                                <Link
                                    v-if="editingConversation !== conv.guid"
                                    :href="
                                        route('conversation.read', conv.guid)
                                    "
                                    class="flex-1 flex items-center px-3 py-2.5 text-sm font-medium truncate"
                                    :class="{
                                        'text-blue-700': route().current(
                                            'conversation.read',
                                            conv.guid
                                        ),
                                        'text-slate-700 hover:text-slate-900':
                                            !$page.props
                                                .currentConversationId ||
                                            $page.props.conversations !==
                                                conv.id,
                                    }"
                                >
                                    <span
                                        @dblclick.prevent="
                                            updateConversation(conv.guid)
                                        "
                                        class="truncate cursor-pointer rounded px-1 py-0.5 transition-colors"
                                        :class="{
                                            'text-center w-full':
                                                sidebarCollapsed,
                                        }"
                                        :title="
                                            sidebarCollapsed
                                                ? 'Dubbelklik om naam te bewerken'
                                                : ''
                                        "
                                    >
                                        {{ conv.title || "Nieuwe conversatie" }}
                                    </span>
                                </Link>

                                <!-- Edit state - Input field -->
                                <div
                                    v-else
                                    class="flex-1 flex items-center px-3 py-2.5 space-x-2"
                                >
                                    <input
                                        :ref="`edit-input-${conv.guid}`"
                                        v-model="editingName"
                                        @keydown="
                                            handleKeydown($event, conv.guid)
                                        "
                                        @blur="saveConversationName(conv.guid)"
                                        @click.stop
                                        class="flex-1 px-2 py-1 text-sm border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                                        placeholder="Conversatie naam..."
                                        maxlength="100"
                                    />

                                    <!-- Optional: Save/Cancel buttons -->
                                    <div class="flex space-x-1">
                                        <button
                                            @click.stop="
                                                saveConversationName(conv.guid)
                                            "
                                            class="p-1 text-green-600 hover:bg-green-100 rounded transition-colors"
                                            title="Opslaan (Enter)"
                                        >
                                            <svg
                                                class="w-3 h-3"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                ></path>
                                            </svg>
                                        </button>
                                        <button
                                            @click.stop="cancelEdit()"
                                            class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors"
                                            title="Annuleren (Escape)"
                                        >
                                            <svg
                                                class="w-3 h-3"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                ></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Delete dropdown menu -->
                                <div
                                    v-if="!sidebarCollapsed"
                                    class="relative"
                                    @click.stop
                                >
                                    <button
                                        @click="toggleDropdown(conv.id)"
                                        class="p-1.5 rounded-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-slate-200"
                                        :class="{
                                            'hover:bg-blue-100':
                                                route().current(
                                                    'conversation.read',
                                                    conv.guid
                                                ),
                                        }"
                                    >
                                        <svg
                                            class="w-4 h-4 text-slate-500"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"
                                            ></path>
                                        </svg>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div
                                        v-if="openDropdown === conv.id"
                                        class="absolute right-0 top-full mt-1 w-32 bg-white rounded-md shadow-lg border border-slate-200 z-50"
                                    >
                                        <button
                                            @click="
                                                updateConversation(conv.guid)
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-gray-800 hover:bg-slate-50 rounded-md transition-colors duration-150"
                                        >
                                            Pas naam aan
                                        </button>
                                        <button
                                            @click="
                                                deleteConversation(conv.guid)
                                            "
                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors duration-150"
                                        >
                                            Verwijderen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else-if="!sidebarCollapsed"
                            class="px-3 py-2 text-sm text-slate-400 italic"
                        >
                            Geen conversaties
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Sidebar Footer -->
            <div class="flex flex-col">
                <!-- Mooie Logout knop -->
                <!-- <div class="p-4">
                    <button
                        @click="$inertia.post(route('logout'))"
                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-gray-100 transition-all duration-150 rounded-md group"
                    >
                        <svg
                            class="w-5 h-5 transition"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"
                            />
                        </svg>
                        <span v-if="!sidebarCollapsed">Uitloggen</span>

                        <div
                            v-if="sidebarCollapsed"
                            class="absolute left-full ml-6 px-2 py-1 bg-slate-900 text-white text-sm rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50 whitespace-nowrap"
                        >
                            Uitloggen
                            <div
                                class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-slate-900"
                            ></div>
                        </div>
                    </button>
                </div> -->
                <div class="relative">
                    <button
                        @click="toggleSettingsDropdown"
                        class="flex items-center justify-center w-full px-3 py-2 text-start rounded-md hover:bg-slate-100 transition-colors duration-200 group focus:outline-none focus:ring-none focus:ring-none focus:ring-offset-none"
                    >
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-white text-sm font-medium"
                        >
                            {{
                                ($page.props.auth?.user?.name ||
                                    "U")[0].toUpperCase()
                            }}
                        </div>
                        <div
                            v-if="!sidebarCollapsed"
                            class="ml-3 flex-1 min-w-0"
                        >
                            <div
                                class="text-sm font-medium text-slate-900 truncate"
                            >
                                {{
                                    $page.props.auth?.user?.firstname &&
                                    $page.props.auth?.user?.name
                                        ? `${$page.props.auth.user.firstname} ${$page.props.auth.user.name}`
                                        : "User Name"
                                }}
                            </div>
                            <div class="text-xs text-slate-500 truncate">
                                {{
                                    $page.props.auth?.user?.email ||
                                    "user@example.com"
                                }}
                            </div>
                        </div>
                        <div
                            v-if="!sidebarCollapsed"
                            class="ml-2 flex-shrink-0"
                        >
                            <svg
                                class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': dropdownOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-if="dropdownOpen && !sidebarCollapsed"
                        class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                    >
                        <Link
                            :href="route('two-factor.setup')"
                            class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors duration-200"
                        >
                            <svg
                                class="w-4 h-4 mr-3 text-slate-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 17a1.5 1.5 0 100-3 1.5 1.5 0 000 3z M16.5 10V7a4.5 4.5 0 10-9 0v3m-1.5 0a1.5 1.5 0 00-1.5 1.5v7A1.5 1.5 0 005.5 20h13a1.5 1.5 0 001.5-1.5v-7a1.5 1.5 0 00-1.5-1.5h-13z"
                                />
                            </svg>
                            Tweestapsverificatie
                        </Link>

                        <Link
                            :href="route('requests.get')"
                            class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors duration-200"
                        >
                            <svg
                                class="w-4 h-4 mr-3 text-slate-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            Feedback
                        </Link>

                        <div class="border-t border-slate-200 my-1"></div>

                        <button
                            @click="$inertia.post(route('logout'))"
                            class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-200"
                        >
                            <svg
                                class="w-4 h-4 mr-3 text-red-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                />
                            </svg>
                            Uitloggen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backdrop for mobile when sidebar is open -->
        <div
            v-if="!sidebarCollapsed"
            @click="sidebarCollapsed = true"
            class="fixed inset-0 bg-black transition-opacity duration-300 ease-in-out z-40 md:hidden"
            :class="
                sidebarCollapsed
                    ? 'opacity-0 pointer-events-none'
                    : 'opacity-50'
            "
        ></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header
                class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6"
            >
                <!-- Left side -->
                <div class="flex items-center gap-4">
                    <!-- Sidebar Toggle -->
                    <button
                        @click="toggleSidebar"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition-colors duration-200"
                    >
                        <div class="flex justify-center">
                            <div class="icon-demo animated-icon">
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="5"
                                        height="16"
                                        rx="1"
                                        fill="currentColor"
                                        stroke="none"
                                    />
                                    <rect
                                        x="10"
                                        y="4"
                                        width="11"
                                        height="16"
                                        rx="1"
                                        stroke-width="1.5"
                                    />
                                </svg>
                            </div>
                        </div>
                    </button>

                    <!-- Breadcrumb or Page Title -->
                    <div class="flex items-center md:gap-4 gap-1 text-sm">
                        <a
                            :href="route('dashboard')"
                            :class="{
                                'bg-slate-100 text-slate-900 ':
                                    route().current('dashboard'),
                                'text-slate-600 hover:text-slate-900 hover:bg-slate-50':
                                    !route().current('dashboard'),
                            }"
                            class="px-4 py-2 rounded-md transition-colors duration-200"
                        >
                            Pinboard
                        </a>

                        <a
                            :href="route('reports.get')"
                            :class="{
                                'bg-slate-100 text-slate-900':
                                    route().current('reports.get'),
                                'text-slate-600 hover:text-slate-900 hover:bg-slate-50':
                                    !route().current('reports.get'),
                            }"
                            class="px-4 py-2 rounded-md transition-colors duration-200"
                        >
                            Rapporten
                        </a>
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <!-- Search
                    <div class="relative hidden md:block">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <svg
                                class="h-4 w-4 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                        <input
                            type="text"
                            placeholder="Search..."
                            class="block w-80 pl-10 pr-3 py-2 border border-slate-200 rounded-md text-sm placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                    </div> -->
                </div>
            </header>

            <!-- Mobile Navigation Menu -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="md:hidden bg-white border-b border-slate-200 shadow-sm"
            >
                <div class="px-4 pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink
                        v-for="item in sidebarItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="text-slate-700 hover:bg-slate-100 hover:text-slate-900"
                    >
                        {{ item.name }}
                    </ResponsiveNavLink>
                </div>

                <div class="pt-4 pb-1 border-t border-slate-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-slate-800">
                            {{ $page.props.auth?.user?.name || "User Name" }}
                        </div>
                        <div class="font-medium text-sm text-slate-500">
                            {{
                                $page.props.auth?.user?.email ||
                                "user@example.com"
                            }}
                        </div>
                    </div>

                    <!-- <div class="mt-3 space-y-1 px-4">
                        <ResponsiveNavLink
                            :href="route('profile.edit')"
                            class="text-slate-700 hover:bg-slate-100"
                        >
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-slate-700 hover:bg-slate-100"
                        >
                            Log Out
                        </ResponsiveNavLink>
                    </div> -->
                </div>
            </div>

            <main class="flex-1 overflow-y-auto p-6">
                <div
                    :class="[
                        route().current('reports.get')
                            ? 'max-w-[full]'
                            : 'max-w-7xl',
                        'mx-auto',
                    ]"
                >
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<!-- <style>
html,
body {
    overflow-x: hidden;
}
</style> -->
