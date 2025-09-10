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
                                        <!-- Change graph button -->
                                        <button
                                            :class="[
                                                'absolute -top-10 right-14 z-10 transition-all duration-200 ease-in-out',
                                                'w-9 h-9 rounded-full shadow-md flex items-center justify-center border',
                                                'bg-blue-400 text-blue-800 border-blue-500 hover:bg-blue-500',
                                                changingInput &&
                                                changingInput.id === message.id
                                                    ? 'animate-pulse'
                                                    : '',
                                            ]"
                                            @click="toggleChangeInput(message)"
                                            title="Verande grafiek layout"
                                            :disabled="
                                                changingInput &&
                                                changingInput.id === message.id
                                            "
                                        >
                                            <i
                                                :class="[
                                                    'text-base',
                                                    changingInput &&
                                                    changingInput.id ===
                                                        message.id
                                                        ? 'fas fa-spinner fa-spin'
                                                        : 'fas fa-comment',
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

                                    <ChartBuilder
                                        :message="message"
                                        :ref="`chartBuilder_${message.id}`"
                                    />
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

                                    <TableBuilder
                                        :message="message"
                                        :ref="`tableBuilder_${message.id}`"
                                    />
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
                                    <div
                                        class="flex justify-start items-center gap-2"
                                    >
                                        <span
                                            :style="{
                                                visibility:
                                                    message.json &&
                                                    message.json.length > 0
                                                        ? 'visible'
                                                        : 'hidden',
                                            }"
                                            class="text-xs ml-2 text-gray-600"
                                        >
                                            {{ message.json?.length || 0 }}
                                            records
                                        </span>

                                        <button
                                            @click="
                                                manualSaveGridState(message)
                                            "
                                            v-if="
                                                message.displayAsChart ||
                                                message.displayAsTable
                                            "
                                            class="px-3 py-1.5 text-xs rounded-md focus:outline-none flex items-center text-gray-600 hover:bg-gray-200"
                                        >
                                            <template
                                                v-if="message.displayAsTable"
                                            >
                                                <span v-if="message.col_def">
                                                    <i
                                                        class="fas fa-bookmark mr-1"
                                                    ></i>
                                                    Opgeslagen
                                                </span>
                                                <span v-else>
                                                    <i
                                                        class="far fa-bookmark mr-1"
                                                    ></i>
                                                    Opslaan
                                                </span>
                                            </template>
                                            <template
                                                v-if="message.displayAsChart"
                                            >
                                                <span
                                                    v-if="
                                                        message._sort &&
                                                        message._x &&
                                                        message._y &&
                                                        message._agg &&
                                                        message._order
                                                    "
                                                >
                                                    <i
                                                        class="fas fa-bookmark mr-1"
                                                    ></i>
                                                    Opgeslagen
                                                </span>
                                                <span v-else>
                                                    <i
                                                        class="far fa-bookmark mr-1"
                                                    ></i>
                                                    Opslaan
                                                </span>
                                            </template>
                                        </button>
                                        <div
                                            class="relative inline-block"
                                            v-if="message.displayAsTable"
                                            v-click-outside="
                                                () =>
                                                    (message.openFeatures = false)
                                            "
                                        >
                                            <!-- Button -->
                                            <button
                                                @click="
                                                    message.openFeatures =
                                                        !message.openFeatures
                                                "
                                                class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-md transition-colors flex items-center"
                                            >
                                                <i
                                                    class="fas fa-sliders-h mr-1.5"
                                                ></i>
                                                Grid Features
                                                <i
                                                    class="fas fa-chevron-down ml-1.5 text-xs transition-transform"
                                                    :class="{
                                                        'rotate-180':
                                                            message.openFeatures,
                                                    }"
                                                ></i>
                                            </button>

                                            <!-- Dropdown -->
                                            <div
                                                v-if="message.openFeatures"
                                                class="absolute bottom-full mb-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
                                            >
                                                <div class="pace-y-1">
                                                    <!-- Sorting -->
                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Sorting</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .sorting
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>

                                                    <!-- Filtering -->
                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Filtering</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .filtering
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>

                                                    <!-- Grouping -->
                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Grouping</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .grouping
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>

                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Pagination</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .pagination
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>

                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Multi line
                                                            text</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .multiline_text
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>

                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Floating
                                                            filters</span
                                                        >
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .floating_filters
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>
                                                    <label
                                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                    >
                                                        <span
                                                            class="text-xs text-gray-700"
                                                            >Total row
                                                        </span>
                                                        <input
                                                            type="checkbox"
                                                            v-model="
                                                                message.features
                                                                    .total_row
                                                            "
                                                            class="w-3 h-3 text-yellow-500 rounded border-gray-300"
                                                        />
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            @click="exportTable(message)"
                                            v-if="message.displayAsTable"
                                            class="px-3 py-1.5 text-xs rounded-md focus:outline-none flex items-center text-gray-600 hover:bg-gray-200"
                                        >
                                            <span>
                                                <i class="fas fa-download"></i>
                                                Exporteer
                                            </span>
                                        </button>
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

                <div v-if="suggestions.length" class="w-full px-2 hidden">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="s in suggestions"
                            :key="s.id"
                            type="button"
                            @click="useSuggestion(s.question)"
                            class="px-3 py-1.5 rounded-full bg-slate-200 text-xs text-gray-700 hover:bg-slate-300 transition-colors"
                        >
                            {{ s.question }}
                        </button>
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
        :pinnedDef="pinnedDef"
        @item-pinned="handleItemPinned"
    />

    <UpdateGraph
        :show="changeInput"
        :close="toggleChangeInput"
        :message="changedMessage"
        :config="this.changedConfig"
        @item-changed="handleItemChanged"
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
import UpdateGraph from "@/Components/Modals/UpdateGraph.vue";

export default {
    name: "Read Conversation",
    components: {
        AuthenticatedLayout,
        Head,
        Snackbar,
        DislikeModal,
        ChartBuilder,
        PinItem,
        TableBuilder,
        UpdateGraph,
    },
    props: {
        conversation: Object,
        pinned_charts: Array,
        pinned_tables: Array,
        pinned_items: Array,
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
            pinnedMessage: null,
            pinnedSort: "",
            pinnedDef: null,
            pinModal: false,
            suggestions: [],
            openFeatures: false,
            features: {
                sorting: true,
                filtering: true,
                grouping: true,
                pagination: false,
                multiline_text: false,
                floating_filters: false,
                total_row: false,
            },
            changeInput: false,
            changedMessage: null,
            changedConfig: null,
        };
    },
    methods: {
        getAllInformation(message) {},
        handleItemChanged({ message_id, message }) {
            const index = this.messages.findIndex((m) => m.id === message_id);
            if (index !== -1) {
                // Bewaar de lokale props
                const {
                    selectedXAxis,
                    selectedYAxis,
                    selectedChartType,
                    selectedOrder,
                    selectedAggregation,
                    selectedOrderDirection,
                    selectedSortField,
                } = this.messages[index];

                // Overschrijf de message met de nieuwe van backend
                this.messages[index] = {
                    ...message,
                    selectedXAxis,
                    selectedYAxis,
                    selectedChartType,
                    selectedOrder,
                    selectedAggregation,
                    selectedSortField,
                    selectedOrderDirection,
                };

                this.setDisplayMode(this.messages[index], "chart");
            }
        },
        toggleChangeInput(message) {
            this.changeInput = !this.changeInput;
            this.changedMessage = message;

            const refName = `chartBuilder_${message.id}`;
            const chartBuilderRef = this.$refs[refName];
            const chartBuilder = Array.isArray(chartBuilderRef)
                ? chartBuilderRef[0]
                : chartBuilderRef;

            console.log(chartBuilder);

            if (
                chartBuilderRef &&
                typeof chartBuilder.getCustomChartOptions === "function"
            ) {
                let result = chartBuilder.getCustomChartOptions(message);
                result.data = [];

                this.changedConfig = result;
            }
        },
        manualSaveGridState(message) {
            if (message.displayAsTable) {
                const refName = `tableBuilder_${message.id}`;
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
                const refName = `chartBuilder_${message.id}`;
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
        exportTable(message) {
            if (message.displayAsTable) {
                const refName = `tableBuilder_${message.id}`;
                const tableBuilderRef = this.$refs[refName];
                const tableBuilder = Array.isArray(tableBuilderRef)
                    ? tableBuilderRef[0]
                    : tableBuilderRef;

                if (
                    tableBuilder &&
                    typeof tableBuilder.SaveGridState === "function"
                ) {
                    tableBuilder
                        .exportTable()
                        .then((result) => {})
                        .catch((error) => {
                            this.$refs.snackbar.show(
                                "Er is een fout opgetreden bij het opslaan van de tabel",
                                "error"
                            );
                        });
                }
            }
        },
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
                const data = {
                    type: type,
                    message_id: message.id,
                    data: message.json,
                    context: {
                        xAxis: message.selectedXAxis,
                        yAxis: message.selectedYAxis,
                        chartType: message.chartType,
                    },
                };

                const response = await fetch("/dr-itchy/summary", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
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
            } catch (error) {}
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
        hasChartData(message) {
            if (message.send_by !== "ai") return false;

            if (
                !message.json ||
                !Array.isArray(message.json) ||
                message.json.length === 0
            ) {
                return false;
            }

            const firstItem = message.json[0];
            const keys = Object.keys(firstItem);

            return keys.length >= 1;
        },
        togglePinModal(message, sort) {
            if (sort === "table") {
                const refName = `tableBuilder_${message.id}`;
                const tableBuilderRef = this.$refs[refName];
                const tableBuilder = Array.isArray(tableBuilderRef)
                    ? tableBuilderRef[0]
                    : tableBuilderRef;

                if (
                    tableBuilder &&
                    typeof tableBuilder.getColdef === "function"
                ) {
                    const result = tableBuilder.getColdef();

                    this.pinnedDef = result;
                }
            }

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
            const storageKey = `selected_source_${this.conversation.guid}`;
            localStorage.setItem(storageKey, this.form.source.id);

            this.suggestions = this.form.source.suggestions || [];
        },
        loadSourceSelection() {
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
                        this.suggestions = sourceExists.suggestions || [];
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

        async useSuggestion(message) {
            if (!message.trim()) return;

            this.no_message = false;
            if (message === "" || this.form.source === "x") {
                this.no_message = true;
                return;
            }

            const params = new URLSearchParams({
                message: message,
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
                        col_def: null,
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
                            selectedChartType: bot_message._sort,
                            selectedXAxis: bot_message._x,
                            selectedYAxis: bot_message._y,
                            selectedAggregation: "sum",
                            col_def: null,
                            _x: bot_message._x,
                            _y: bot_message._y,
                            _order: null,
                            _agg: null,
                            _sort: bot_message._sort,
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
        async sendMessage(message = null) {
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
                        selectedSortField: null,
                        selectedSortDirection: null,
                        col_def: null,
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
                            selectedChartType: bot_message._sort,
                            selectedXAxis: bot_message._x,
                            selectedYAxis: bot_message._y,
                            selectedAggregation: "sum",
                            selectedSortField: bot_message._order,
                            selectedSortDirection: bot_message._order_dir,
                            col_def: null,
                            _x: bot_message._x,
                            _y: bot_message._y,
                            _order: bot_message._order,
                            _agg: null,
                            _sort: bot_message._sort,
                            _order_dir: bot_message._order_dir,
                            features: {
                                sorting: true,
                                filtering: true,
                                grouping: true,
                                pagination: false,
                                multiline_text: false,
                                floating_filters: false,
                                total_row: false,
                            },
                            openFeatures: false,
                        });

                        console.log(this.messages);
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
    mounted() {
        this.messages = this.conversation.messages.map((m) => ({
            ...m,
            displayAsTable: m.respond_type === "Table",
            displayAsChart: m.respond_type === "Chart",
            displayAsQuery: m.respond_type === "Query",
            thumbs_up: m.thumbs_up || 0,
            thumbs_down: m.thumbs_down || 0,
            selectedChartType: m._sort || "bar",
            selectedXAxis: m._x || null,
            selectedYAxis: m._y || null,
            selectedSortField: m._order || null,
            selectedSortDirection: m._order_dir || null,
            selectedAggregation: m._agg || "sum",
            selectedOrder: m._order || "value_desc",
            col_def: m.col_def || null,
            _x: m._x,
            _y: m._y,
            _order: m._order,
            _agg: m._agg,
            _sort: m._sort,
            _order_dir: m._order_dir,
            features: m.features || {
                sorting: true,
                filtering: true,
                grouping: true,
                pagination: false,
                multiline_text: false,
                floating_filters: false,
            },
            openFeatures: false,
        }));

        this.form.source ??= this.conversation.user.sources?.[0] ?? null;

        this.pinnedCharts = this.pinned_items
            .filter((item) => item.type === "graph")
            .map((chart) => ({
                messageId: chart.message_id,
            }));

        this.pinnedTables = this.pinned_items
            .filter((item) => item.type === "table")
            .map((table) => ({
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
