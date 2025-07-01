<template>
    <Head :title="report.name" />
    <AuthenticatedLayout :breadcrumbs="breadcrumbs">
        <template #title>Sources</template>

        <div
            class="flex md:flex-row gap-2 md:gap-0 flex-col md:items-center items-start justify-between"
        >
            <h1 class="text-black font-bold text-4xl">{{ report.name }}</h1>

            <div class="flex gap-2">
                <PrimaryButton @click="toggleEdit">
                    <i class="fas fa-edit mr-2"></i> Pas aan
                </PrimaryButton>
                <PrimaryButton v-if="editReport" @click="saveReport">
                    <i class="fas fa-check mr-2"></i>Sla op
                </PrimaryButton>
                <SecondaryButton @click="deleteReport">
                    <i class="fas fa-trash-alt mr-2"></i>Verwijder
                </SecondaryButton>
            </div>
        </div>

        <div>
            <div class="py-6 rounded-md">
                <div class="w-full">
                    <InputLabel for="name" value="Naam*" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        :disabled="!editReport"
                        placeholder="Naam"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="w-full mt-2">
                    <InputLabel for="link" value="Link*" />
                    <TextInput
                        id="link"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.link"
                        :disabled="!editReport"
                        placeholder="Link"
                    />
                    <InputError class="mt-2" :message="form.errors.link" />
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
        report: Object,
        company: Object,
    },
    data() {
        return {
            form: useForm({
                name: this.report.name,
                link: this.report.link,
            }),
            editReport: false,
        };
    },
    methods: {
        saveReport() {
            this.form.patch(
                route("company.report.update", {
                    guid: this.company.guid,
                    report_guid: this.report.guid,
                }),
                {
                    onSuccess: () => this.toggleEdit(),
                    onError: (error) => console.log(error),
                }
            );
        },
        deleteReport() {
            this.form.delete(
                route("company.report.delete", {
                    guid: this.company.guid,
                    report_guid: this.report.guid,
                }),
                {
                    onError: (error) => console.log(error),
                }
            );
        },
        toggleEdit() {
            this.editReport = !this.editReport;
        },
    },
    computed: {},
};
</script>
