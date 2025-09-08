<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">Geef je aanpassingen door</h2>

            <textarea
                class="w-full h-48 resize-none overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                placeholder="Typ hier je tekst..."
                v-model="form.feedback"
            ></textarea>

            <div
                v-if="hasErrors"
                class="flex flex-col gap-2 text-sm text-red-600"
            >
                <InputError
                    v-for="(error, key) in form.errors"
                    :key="key"
                    :message="error"
                />
            </div>

            <div class="flex justify-end mt-4">
                <PrimaryButton type="submit">Verstuur</PrimaryButton>
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
import { computed } from "vue";

export default {
    name: "UpdateGraphModal",
    components: {
        Modal,
        TextInput,
        PrimaryButton,
        InputError,
    },
    props: {
        show: Boolean,
        close: Function,
        message: Object,
    },
    setup(props, { emit }) {
        const form = useForm({
            feedback: "",
        });

        const sendFeedback = () => {
            axios
                .patch(route("conversation.changeInput", props.message?.id))
                .then((response) => {
                    form.reset();
                    props.close();
                    console.log(response);
                    emit("item-changed", {
                        message_id: props.message?.id,
                        message: response.data.message,
                    });
                });
        };

        const hasErrors = computed(() => Object.keys(form.errors).length > 0);

        return {
            form,
            sendFeedback,
            hasErrors,
        };
    },
};
</script>
