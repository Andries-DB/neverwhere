<template>
    <Head :title="user_group.name" />
    <AuthenticatedLayout>
        <div
            class="flex md:flex-row gap-4 md:gap-0 flex-col items-start justify-between"
        >
            <div class="flex flex-col">
                <div
                    v-if="user_group.sources && user_group.sources.length"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <div
                        v-for="source in user_group.sources"
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
                        {{ user_group.name }}
                    </h1>
                </div>
            </div>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i
                        :class="[
                            editUserGroup ? 'fas fa-times' : 'fas fa-edit',
                            'mr-2',
                        ]"
                    ></i>
                    {{
                        editUserGroup
                            ? $t("buttons.cancel")
                            : $t("buttons.edit")
                    }}
                </PrimaryButton>
                <PrimaryButton v-if="editUserGroup" @click="saveUserGroup">
                    <i class="fas fa-check mr-2"></i>{{ $t("buttons.save") }}
                </PrimaryButton>
                <SecondaryButton @click="deleteUserGroup">
                    <i class="fas fa-trash-alt mr-2"></i
                    >{{ $t("buttons.delete") }}
                </SecondaryButton>
            </div>
        </div>

        <div class="py-6">
            <div class="w-full">
                <InputLabel for="name" :value="$t('labels.model') + '*'" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    :disabled="!editUser"
                    :placeholder="$t('labels.model')"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="flex flex-col gap-2 mt-4">
                <label class="text-sm font-medium text-slate-700">
                    {{ $t("labels.connectsources") }}
                </label>
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="source in this.company.sources"
                        :key="source.id"
                        @click="editUserGroup && toggleSource(source.id)"
                        :class="[
                            'px-3 py-1 rounded-md text-sm font-medium border transition-all duration-200 flex items-center gap-1',
                            editUserGroup
                                ? 'cursor-pointer'
                                : 'opacity-50 cursor-default',
                        ]"
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
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
export default {
    name: "",
    components: {
        Head,
        AuthenticatedLayout,
        PrimaryButton,
        SecondaryButton,
        TextInput,
        InputError,
        InputLabel,
    },
    props: {
        user_group: Object,
        company: Array,
    },
    data() {
        return {
            editUserGroup: false,
            form: useForm({
                name: this.user_group.name,
                source_ids: this.user_group.sources.map((source) => source.id),
            }),
        };
    },
    methods: {
        goBack() {
            this.$inertia.visit(route("company.read", this.company.guid));
        },
        deleteUserGroup() {
            this.form.delete(
                route("company.usergroup.delete", {
                    guid: this.company.guid,
                    usergroup_guid: this.user_group.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        saveUserGroup() {
            this.form.patch(
                route("company.usergroup.update", {
                    guid: this.company.guid,
                    usergroup_guid: this.user_group.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleEdit() {
            this.editUserGroup = !this.editUserGroup;
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
    },
    computed: {},
};
</script>
