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
                v-model="form.webhook"
                label="Webhook"
                placeholder="Webhook"
                :class="{ 'border-red-500': form.errors.webhook }"
            />

            <textarea
                class="w-full h-48 resize-none overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                placeholder="Typ hier je tekst..."
                v-model="form.model"
            ></textarea>

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
    },
    data() {
        return {
            errors: {},
            form: useForm({
                name: "",
                color: "",
                webhook: "",
                model: "",
                company: this.company.id,
            }),
        };
    },
    watch: {
        show(newValue) {
            if (newValue) {
                this.form.color = this.generateRandomColor();
            }
        },
    },
    methods: {
        click() {
            this.close();
        },
        addSource() {
            this.form.post(route("source.create"), {
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
        generateRandomColor() {
            // Hulpfunctie om een getal tussen min en max te genereren
            function randomLightValue(min = 180, max = 255) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            const r = randomLightValue();
            const g = randomLightValue();
            const b = randomLightValue();

            // Omzetten naar hex en samenvoegen
            return (
                "#" +
                r.toString(16).padStart(2, "0") +
                g.toString(16).padStart(2, "0") +
                b.toString(16).padStart(2, "0")
            );
        },
    },
    computed: {},
};
</script>
