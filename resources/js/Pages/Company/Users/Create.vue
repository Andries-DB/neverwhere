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

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-slate-700"
                    >Koppel bronnen</label
                >
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
        sources: Array,
    },
    data() {
        return {
            errors: {},
            form: useForm({
                firstname: "",
                name: "",
                email: "",
                password: "",
                sources: [], // <-- nieuwe property
            }),
        };
    },
    methods: {
        click() {
            this.close();
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
    },
    computed: {},
};
</script>
