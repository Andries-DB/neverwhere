<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">{{ $t("modals.refresh") }}</h2>

            <textarea
                class="w-full h-48 resize-none overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                :placeholder="$t('labels.textarea')"
                v-model="form.feedback"
                :disabled="isLoading"
            ></textarea>

            <div class="flex justify-end mt-4 gap-5">
                <InputError :message="error" />
                <PrimaryButton type="submit" :disabled="isLoading">
                    <span
                        v-if="isLoading"
                        class="animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                    ></span>
                    {{
                        isLoading
                            ? $t("modals.button_loading")
                            : $t("modals.button")
                    }}
                </PrimaryButton>
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
import { ref, computed } from "vue";
import axios from "axios";

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
        config: Array,
    },
    setup(props, { emit }) {
        const form = useForm({
            feedback: "",
        });

        const isLoading = ref(false);
        const error = ref("");

        const sendFeedback = async () => {
            error.value = "";
            isLoading.value = true;
            try {
                const response = await axios.patch(
                    route("conversation.changeInput", props.message?.id),
                    {
                        message: props.message,
                        feedback: form.feedback,
                        config: props.config,
                    }
                );

                console.info("hi");

                form.reset();
                props.close();
                emit("item-changed", {
                    message_id: props.message?.id,
                    message: response.data.message,
                });
            } catch (e) {
                error.value = e.response?.data?.error || "Onbekende fout";
                console.log(error);
            } finally {
                isLoading.value = false;
            }
        };

        const hasErrors = computed(() => Object.keys(form.errors).length > 0);

        return {
            form,
            sendFeedback,
            hasErrors,
            isLoading,
            error,
        };
    },
};
</script>
