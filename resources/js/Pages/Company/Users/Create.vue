<template>
    <Modal :show="show" @close="close()">
        <form
            @submit.prevent="addUser()"
            novalidate
            id="addTypeForm"
            class="flex flex-col gap-4 w-full justify-between mt-10"
        >
            <TextInput
                v-model="this.form.firstname"
                label="Voornaam"
                placeholder="Voornaam"
                :class="{ 'border-red-500': this.form.errors.firstname }"
            />

            <TextInput
                v-model="this.form.name"
                label="Naam"
                placeholder="Naam"
                :class="{ 'border-red-500': this.form.errors.name }"
            />

            <TextInput
                v-model="this.form.email"
                label="Email"
                placeholder="Email"
                type="email"
                :class="{ 'border-red-500': this.form.errors.email }"
            />
            <TextInput
                v-model="this.form.password"
                label="Password"
                placeholder="Wachtwoord"
                type="password"
                :class="{ 'border-red-500': this.form.errors.password }"
            />

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700"
                    >Rol</label
                >
                <div class="relative" v-click-outside="closeRoleDropdown">
                    <button
                        type="button"
                        @click="toggleRoleDropdown"
                        class="w-full inline-flex items-center justify-between px-3 py-2 text-sm font-base text-gray-900 hover:bg-gray-50 rounded-md border border-gray-300 transition-colors"
                        :class="{ 'border-red-500': form.errors.role }"
                    >
                        <span class="truncate">{{
                            this.selectedRole
                                ? this.selectedRole.name
                                : "Selecteer rol"
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
                        v-if="isRoleDropdownOpen"
                        class="absolute z-50 mt-1 w-full rounded-lg bg-white shadow-lg border border-gray-200"
                    >
                        <div class="py-1 max-h-60 overflow-auto">
                            <div
                                v-for="role in roles"
                                :key="role.value"
                                @click="this.selectRole(role)"
                                class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="{
                                    'bg-gray-100 text-gray-900':
                                        selectedRole &&
                                        selectedRole.value === role.value,
                                }"
                            >
                                <div class="flex items-center">
                                    <span class="truncate">{{
                                        role.name
                                    }}</span>
                                    <span
                                        v-if="role.value === 'user'"
                                        class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"
                                    >
                                        Default
                                    </span>
                                </div>
                                <svg
                                    v-if="
                                        selectedRole &&
                                        selectedRole.value === role.value
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
                                v-if="!this.roles || this.roles.length === 0"
                                class="px-3 py-2 text-sm text-gray-500"
                            >
                                Geen dashboards beschikbaar
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-slate-700"
                    >Gebruikersgroepen</label
                >
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="userGroup in company.usergroups"
                        :key="userGroup.id"
                        @click="selectUserGroup(userGroup)"
                        class="cursor-pointer px-3 py-1 rounded-md text-sm font-medium border transition-all duration-200 flex items-center gap-1 hover:bg-gray-50"
                        :class="[
                            selectedUserGroups.some(
                                (g) => g.id === userGroup.id
                            )
                                ? 'bg-blue-100 border-blue-300 text-blue-700'
                                : 'bg-white border-gray-300 text-gray-700',
                        ]"
                    >
                        <span>{{ userGroup.name }}</span>
                        <span class="text-xs opacity-75"
                            >({{ userGroup.sources.length }} bronnen)</span
                        >
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Selecteer een gebruikersgroep om automatisch de bijbehorende
                    bronnen te koppelen.
                </p>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-slate-700">
                    Koppel bronnen
                    <span
                        v-if="selectedUserGroups.length"
                        class="text-xs text-blue-600 font-normal"
                    >
                        (Geladen van:
                        {{ selectedUserGroups.map((g) => g.name).join(", ") }})
                    </span>
                </label>
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="source in sources"
                        :key="source.id"
                        @click="toggleSource(source.id)"
                        class="cursor-pointer px-3 py-1 rounded-md text-sm font-medium border transition-all duration-200 flex items-center gap-1"
                        :style="{
                            backgroundColor: isSelected(source.id)
                                ? source.hex_color
                                : '#f8fafc',
                            borderColor: source.hex_color,
                            color: isSelected(source.id)
                                ? '#1e293b'
                                : '#475569',
                        }"
                    >
                        <span>{{ source.name }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-start gap-3">
                <div class="flex flex-col flex-wrap gap-2">
                    <InputError v-for="error in errors" :message="error" />
                </div>
                <PrimaryButton type="submit">{{
                    $t("buttons.add")
                }}</PrimaryButton>
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

export default {
    name: "AddUser",
    components: {
        Modal,
        TextInput,
        PrimaryButton,
        InputError,
    },
    props: {
        close: Function,
        show: Boolean,
        company: Object,
        sources: Array,
    },
    data() {
        return {
            errors: {},
            selectedUserGroups: [],
            isRoleDropdownOpen: false,
            selectedRole: null,
            roles: [
                { name: "User", value: "user" }, // Default
                { name: "Admin", value: "admin" }, // Admin met alle rechten
                { name: "CDO", value: "cdo" }, // Specifieke rol
            ],

            form: useForm({
                firstname: "",
                name: "",
                email: "",
                password: "",
                sources: [],
                user_group_ids: [],
                role: "",
            }),
        };
    },
    methods: {
        click() {
            this.close();
        },
        selectRole(role) {
            this.selectedRole = role;
            console.log(this.selectedRole);
            this.form.role = role.value;
            this.isRoleDropdownOpen = false;
        },
        toggleRoleDropdown() {
            this.isRoleDropdownOpen = !this.isRoleDropdownOpen;
            // console.log(this.isRoleDropdownOpen);
        },

        closeRoleDropdown() {
            this.isRoleDropdownOpen = false;
        },
        addUser() {
            this.form.post(route("company.user.store", this.company.guid), {
                preserveScroll: true,
                onSuccess: () => {
                    this.form.reset();
                    this.close();
                },
                onError: (errors) => {
                    this.errors = errors;
                },
            });
        },
        toggleSource(id) {
            const index = this.form.sources.indexOf(id);
            if (index > -1) {
                this.form.sources.splice(index, 1); // remove
            } else {
                this.form.sources.push(id); // add
            }
        },
        isSelected(id) {
            return this.form.sources.includes(id);
        },
        selectUserGroup(userGroup) {
            const index = this.selectedUserGroups.findIndex(
                (g) => g.id === userGroup.id
            );

            if (index > -1) {
                this.selectedUserGroups.splice(index, 1); // deselect
            } else {
                this.selectedUserGroups.push(userGroup); // select
            }

            this.form.user_group_ids = this.selectedUserGroups.map((g) => g.id);

            // Bronnen automatisch toevoegen (zonder duplicaten)
            const allSources = this.selectedUserGroups.flatMap((g) =>
                g.sources.map((s) => s.id)
            );

            // Unieke samenvoegen met manuele selectie
            this.form.sources = [
                ...new Set([...this.form.sources, ...allSources]),
            ];
        },
    },
    computed: {},
};
</script>
