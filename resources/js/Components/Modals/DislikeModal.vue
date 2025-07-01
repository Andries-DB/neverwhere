<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">Geef je feedback</h2>

            <!-- <TextInput
                v-model="form.feedback"
                label="Feedback"
                placeholder="Wat wil je delen?"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.feedback }"
            /> -->

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
        message: Object, // optioneel, mag null zijn
    },
    setup(props) {
        const form = useForm({
            feedback: "",
            message_id: props.message?.id ?? null,
        });

        const sendFeedback = () => {
            // Optional: update message_id dynamically if it became available later
            if (props.message?.id) {
                form.message_id = props.message.id;
            }

            form.post(route("conversation.dislikeMessage"), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    props.close();
                },
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
