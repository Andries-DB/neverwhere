<template>
    <Modal :show="show" @close="close">
        <form
            @submit.prevent="sendModal"
            class="flex flex-col gap-6 w-full mt-8"
        >
            <h2 class="text-xl font-semibold">Geef je knowledge door</h2>

            <TextInput
                v-model="form.key"
                label="Key"
                placeholder="Key"
                :class="{ 'border-red-500': form.errors.key }"
            />

            <textarea
                class="w-full h-28 resize-none overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                placeholder="Typ hier je tekst..."
                v-model="form.description"
            ></textarea>

            <textarea
                class="w-full h-48 resize-none overflow-y-auto overflow-x-hidden p-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                placeholder="Typ hier je tekst..."
                v-model="form.query"
            ></textarea>

            <div v-for="(messages, field) in form.errors" :key="field">
                <InputError
                    v-for="(message, i) in [].concat(messages)"
                    :key="i"
                    :message="message"
                />
            </div>

            <div class="flex justify-end mt-4 gap-2">
                <DangerButton
                    type="button"
                    v-if="mode === 'edit'"
                    @click="deleteType"
                    :disabled="loadingDelete || loadingSave"
                >
                    <span v-if="loadingDelete">Verwijderen...</span>
                    <span v-else>Verwijder</span>
                </DangerButton>

                <PrimaryButton
                    type="submit"
                    :disabled="loadingSave || loadingDelete"
                >
                    <span v-if="loadingSave">
                        <svg
                            class="animate-spin h-4 w-4 mr-2 inline-block"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>
                        Opslaan...
                    </span>
                    <span v-else>
                        <span v-if="mode === 'edit'">Opslaan</span>
                        <span v-else>Maak aan</span>
                    </span>
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
import { computed, watch, ref } from "vue";
import DangerButton from "../DangerButton.vue";

export default {
    name: "KnowledgeModal",
    components: {
        Modal,
        TextInput,
        PrimaryButton,
        InputError,
        DangerButton,
    },
    props: {
        show: Boolean,
        close: Function,
        type: Object,
        mode: String,
        source_id: String,
    },
    setup(props, { emit }) {
        const form = useForm({
            id: "",
            key: "",
            description: "",
            query: "",
            source_id: "",
        });

        const loadingSave = ref(false);
        const loadingDelete = ref(false);

        watch(
            [() => props.show, () => props.mode, () => props.type],
            ([show, mode, type]) => {
                if (show && mode === "edit" && type) {
                    form.id = type.id || "";
                    form.key = type.key || "";
                    form.description = type.description || "";
                    form.query = type.query || "";

                    form.clearErrors();
                } else if (show && mode === "add") {
                    form.reset();
                    form.clearErrors();
                }
            },
            { immediate: true, deep: true }
        );

        watch(
            () => props.type,
            (newType) => {
                if (props.show && props.mode === "edit" && newType) {
                    form.id = newType.id || "";
                    form.key = newType.key || "";
                    form.description = newType.description || "";
                    form.query = newType.query || "";
                    form.clearErrors();
                }
            },
            { deep: true }
        );

        const sendModal = () => {
            if (props.source_id) {
                form.source_id = props.source_id;
            }
            loadingSave.value = true;

            axios
                .patch(route("studio.patch.knowledge"), form)
                .then((response) => {
                    emit("submitted", response.data);
                    props.close();
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        form.setError(error.response.data.errors);
                    }
                })
                .finally(() => {
                    loadingSave.value = false;
                });
        };

        const deleteType = () => {
            if (props.source_id) {
                form.source_id = props.source_id;
            }
            loadingDelete.value = true;

            axios
                .delete(route("studio.delete.knowledge", form.id), {
                    data: { source_id: form.source_id },
                })
                .then((response) => {
                    emit("deleted", response.data);
                    props.close();
                })
                .finally(() => {
                    loadingDelete.value = false;
                });
        };

        const hasErrors = computed(() => Object.keys(form.errors).length > 0);

        return {
            form,
            sendModal,
            deleteType,
            hasErrors,
            loadingSave,
            loadingDelete,
        };
    },
};
</script>
