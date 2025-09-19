<template>
    <Modal :show="show" @close="close()">
        <form
            @submit.prevent="addUser()"
            novalidate
            id="addTypeForm"
            class="flex flex-col gap-4 w-full justify-between mt-10"
        >
            <TextInput
                v-model="form.company"
                :label="$t('labels.name')"
                :placeholder="$t('labels.name')"
                :class="{ 'border-red-500': form.errors.company }"
            />

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
    },
    data() {
        return {
            errors: {},
            form: useForm({
                company: "",
            }),
        };
    },
    methods: {
        click() {
            this.close();
        },
        addUser() {
            this.form.post(route("company.create"), {
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
    },
    computed: {},
};
</script>
