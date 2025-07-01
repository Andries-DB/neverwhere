<template>
    <Modal :show="show" @close="close()">
        <form
            @submit.prevent="addSource()"
            novalidate
            id="addTypeForm"
            class="flex flex-col gap-4 w-full justify-between mt-10"
        >
            <TextInput
                v-model="form.name"
                label="Naam"
                placeholder="Naam"
                :class="{ 'border-red-500': form.errors.name }"
            />

            <TextInput
                v-model="form.link"
                label="Link"
                placeholder="Link"
                :class="{ 'border-red-500': form.errors.link }"
            />

            <div class="flex justify-between items-start gap-3">
                <div class="flex flex-col flex-wrap gap-2">
                    <InputError v-for="error in errors" :message="error" />
                </div>
                <PrimaryButton type="submit">Voeg toe</PrimaryButton>
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
        user: Object,
        sort: {
            type: String,
            default: "Company",
        },
    },
    data() {
        return {
            errors: {},
            form: useForm({
                name: "",
                link: "",
                company: null,
                user: null,
            }),
        };
    },

    methods: {
        click() {
            this.close();
        },
        addSource() {
            if (this.sort === "User") {
                this.form.user = this.user?.id;
                this.form.post(route("user.report.create"), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.reset();
                        this.close();
                    },
                    onError: (errors) => {
                        this.errors = errors;
                    },
                });
            } else {
                this.form.company = this.company?.id;
                this.form.post(route("company.report.create"), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.form.reset();
                        this.close();
                    },
                    onError: (errors) => {
                        this.errors = errors;
                    },
                });
            }
        },
    },
    computed: {},
};
</script>
