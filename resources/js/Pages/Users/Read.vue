<template>
    <Head :title="user.firstname + ' ' + user.name" />
    <AuthenticatedLayout>
        <div
            class="flex md:flex-row gap-4 md:gap-0 flex-col items-start justify-between"
        >
            <div class="flex flex-col">
                <div
                    v-if="user.sources && user.sources.length"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <div
                        v-for="source in user.sources"
                        :key="source.id"
                        class="px-2 py-[1px] rounded-md text-xs font-medium border transition-all duration-200 flex items-center gap-1"
                        :style="{
                            backgroundColor: source.hex_color,
                            borderColor: source.hex_color,
                            color: '#1e293b',
                        }"
                    >
                        <span>{{ source.name }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        class="flex items-center justify-center w-9 h-9 rounded-md bg-gray-200 text-gray-600 hover:bg-gray-300 transition cursor-pointer"
                        @click="goBack"
                    >
                        <i class="fas fa-arrow-left text-sm"></i>
                    </button>
                    <h1 class="text-black font-bold text-4xl">
                        {{ user.firstname }} {{ user.name }}
                    </h1>
                </div>
            </div>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i
                        :class="[
                            editUser ? 'fas fa-times' : 'fas fa-edit',
                            'mr-2',
                        ]"
                    ></i>
                    {{ editUser ? $t("buttons.cancel") : $t("buttons.edit") }}
                </PrimaryButton>
                <PrimaryButton v-if="editUser" @click="saveUser">
                    <i class="fas fa-check mr-2"></i>{{ $t("buttons.save") }}
                </PrimaryButton>
                <SecondaryButton @click="deleteUser">
                    <i class="fas fa-trash-alt mr-2"></i
                    >{{ $t("buttons.delete") }}
                </SecondaryButton>
            </div>
        </div>

        <div>
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel
                        for="firstname"
                        :value="$t('labels.firstname') + '*'"
                    />
                    <TextInput
                        id="firstname"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.firstname"
                        :disabled="!editUser"
                        :placeholder="$t('labels.firstname')"
                    />
                    <InputError class="mt-2" :message="form.errors.firstname" />
                </div>
                <div class="w-full mt-2">
                    <InputLabel for="name" :value="$t('labels.name') + '*'" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        :disabled="!editUser"
                        :placeholder="$t('labels.name')"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="email" :value="$t('labels.email') + '*'" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        :disabled="!editUser"
                        :placeholder="$t('labels.email')"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="user.role === 'admin'">
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    {{ $t("labels.company") }}
                </label>

                <div class="relative" v-click-outside="closeCompanyDropdown">
                    <!-- Button -->
                    <button
                        type="button"
                        @click="toggleCompanyDropdown"
                        class="w-full inline-flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-900 bg-white rounded-md border border-gray-300 transition-colors"
                        :disabled="!editUser"
                    >
                        <span class="truncate">
                            {{
                                selectedCompany
                                    ? selectedCompany.company
                                    : "..."
                            }}
                        </span>
                        <svg
                            class="ml-2 h-4 w-4 text-gray-500"
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
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="isCompanyDropdownOpen"
                        class="absolute z-50 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200"
                    >
                        <div class="py-1 max-h-60 overflow-auto">
                            <div
                                v-for="company in companies"
                                :key="company.guid"
                                @click="selectCompany(company)"
                                class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{
                                    'bg-gray-100 text-gray-900':
                                        selectedCompany &&
                                        selectedCompany.guid === company.guid,
                                }"
                            >
                                <span class="truncate">{{
                                    company.company
                                }}</span>

                                <svg
                                    v-if="
                                        selectedCompany &&
                                        selectedCompany.guid === company.guid
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
                                v-if="!companies || companies.length === 0"
                                class="px-3 py-2 text-sm text-gray-500"
                            >
                                {{ $t("labels.noresults") }}
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
import { Head, useForm } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";

export default {
    name: "",
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        SecondaryButton,
        TextInput,
        InputError,
        InputLabel,
    },
    props: {
        user: Object,
        sources: Array,
        companies: Array,
    },
    data() {
        return {
            editUser: false,
            form: useForm({
                name: this.user.name,
                firstname: this.user.firstname,
                email: this.user.email,
                source_ids: this.user.sources.map((source) => source.id),
                company_guid: this.user.companies
                    ? this.user.companies[0]
                        ? this.user.companies[0].guid
                        : null
                    : null,
            }),

            isCompanyDropdownOpen: false,
            selectedCompany: this.user.companies
                ? this.user.companies[0]
                : null,
        };
    },
    methods: {
        goBack() {
            this.$inertia.visit(route("user.get"));
        },
        deleteUser() {
            this.form.delete(
                route("user.delete", {
                    guid: this.user.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        saveUser() {
            this.form.patch(
                route("user.update", {
                    guid: this.user.guid,
                }),
                {
                    onSuccess: () => location.reload(),
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleSource(id) {
            const index = this.form.source_ids.indexOf(id);
            if (index > -1) {
                this.form.source_ids.splice(index, 1);
            } else {
                this.form.source_ids.push(id);
            }
        },
        isSelected(id) {
            return this.form.source_ids.includes(id);
        },
        toggleEdit() {
            this.editUser = !this.editUser;
        },
        toggleCompanyDropdown() {
            this.isCompanyDropdownOpen = !this.isCompanyDropdownOpen;
        },
        closeCompanyDropdown() {
            this.isCompanyDropdownOpen = false;
        },
        selectCompany(company) {
            this.selectedCompany = company;
            this.isCompanyDropdownOpen = false;

            this.form.company_guid = company.guid;
        },
    },
    computed: {},
};
</script>
