<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">Geef je aanpassingen door</h2>

            {{ this.sort }}
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
    name: "SendFeedbackModal",
    components: {
        Modal,
        TextInput,
        PrimaryButton,
        InputError,
    },
    props: {
        show: Boolean,
        close: Function,
        graph: Object, // optioneel, mag null zijn
        sort: String,
    },
    setup(props) {
        const form = useForm({
            feedback: "",
            graph_id: props.graph?.id ?? null,
        });

        const sendFeedback = () => {
            // Optional: update message_id dynamically if it became available later
            if (props.message?.id) {
                form.message_id = props.message.id;
            }

            if (props.sort === "table") {
                form.patch(
                    route("conversation.updateTableJson", props.graph?.id),
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            form.reset();
                            props.close();
                        },
                    }
                );
            } else {
                form.patch(
                    route("conversation.updateChartJson", props.graph?.id),
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            form.reset();
                            props.close();
                        },
                    }
                );
            }
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
