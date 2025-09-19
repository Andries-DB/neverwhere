<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendFeedback"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">{{ $t("modals.settings") }}</h2>

            <TextInput
                v-model="form.name"
                :label="$t('labels.name')"
                :placeholder="$t('labels.name')"
                :class="{ 'border-red-500': form.errors.name }"
            />

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
                <PrimaryButton type="submit">{{
                    $t("modals.button")
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
    },
    setup(props) {
        const form = useForm({
            name: "",
        });

        const sendFeedback = () => {
            form.post(route("dashboard.create"), {
                preserveScroll: true,
                // preserveState: false,
                onSuccess: () => {
                    location.reload();
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
