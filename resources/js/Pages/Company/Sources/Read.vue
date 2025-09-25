<template>
    <Head :title="source.name" />
    <AuthenticatedLayout>
        <template #title>Sources</template>

        <div
            class="flex md:flex-row gap-2 md:gap-0 flex-col md:items-center items-start justify-between"
        >
            <div class="flex items-center gap-3">
                <button
                    class="flex items-center justify-center w-9 h-9 rounded-md bg-gray-200 text-gray-600 hover:bg-gray-300 transition cursor-pointer"
                    @click="goBack"
                >
                    <i class="fas fa-arrow-left text-sm"></i>
                </button>
                <h1 class="text-black font-bold text-4xl">{{ source.name }}</h1>
            </div>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i
                        :class="[
                            editSource ? 'fas fa-times' : 'fas fa-edit',
                            'mr-2',
                        ]"
                    ></i>
                    {{ editSource ? $t("buttons.cancel") : $t("buttons.edit") }}
                </PrimaryButton>
                <PrimaryButton v-if="editSource" @click="saveSource">
                    <i class="fas fa-check mr-2"></i>{{ $t("buttons.save") }}
                </PrimaryButton>
                <SecondaryButton @click="deleteSource">
                    <i class="fas fa-trash-alt mr-2"></i
                    >{{ $t("buttons.delete") }}
                </SecondaryButton>
            </div>
        </div>

        <div>
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="name" :value="$t('labels.name') + '*'" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        :disabled="!editSource"
                        :placeholder="$t('labels.name')"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel
                        for="webhook"
                        :value="$t('labels.webhook') + '*'"
                    />
                    <TextInput
                        id="webhook"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.webhook"
                        :disabled="!editSource"
                        :placeholder="$t('labels.webhook')"
                    />
                    <InputError class="mt-2" :message="form.errors.webhook" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="model" :value="$t('labels.model') + '*'" />
                    <textarea
                        class="w-full h-[500px] resize-vertical overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                        :placeholder="
                            $t('labels.textarea') +
                            ' ' +
                            $t('labels.model') +
                            '...'
                        "
                        v-model="form.model"
                        :disabled="!editSource"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.model" />
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
        source: Object,
        company: Object,
    },
    data() {
        return {
            form: useForm({
                name: this.source.name,
                hex_color: this.source.hex_color,
                webhook: this.source.webhook,
                model: this.source.model,
            }),
            editSource: false,
        };
    },
    methods: {
        goBack() {
            this.$inertia.visit(route("company.read", this.company.guid));
        },
        saveSource() {
            this.form.patch(
                route("company.source.update", {
                    guid: this.company.guid,
                    source_id: this.source.id,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        deleteSource() {
            this.form.delete(
                route("company.source.delete", {
                    guid: this.company.guid,
                    source_id: this.source.id,
                }),
                {
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleEdit() {
            this.editSource = !this.editSource;
        },
    },
    computed: {},
};
</script>
