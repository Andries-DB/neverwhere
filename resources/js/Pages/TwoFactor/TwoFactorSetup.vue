<template>
    <Head title="2FA Instellen" />

    <AuthenticatedLayout>
        <div class="p-6 text-gray-900">
            <div v-if="!is_enabled" class="mb-6">
                <h3 class="text-lg font-medium mb-4">
                    {{ $t("two_factor.activate") }}
                </h3>
                <p class="text-gray-600 mb-4">
                    {{ $t("two_factor.activate_description") }}
                </p>

                <!-- QR Code weergave -->
                <div class="mb-4">
                    <canvas ref="qrCanvas" class="border rounded"></canvas>
                </div>

                <!-- Handmatige invoer secret -->
                <div class="mb-4 p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600 mb-2">
                        {{ $t("two_factor.manual") }}
                    </p>
                    <code
                        class="text-sm font-mono bg-gray-200 px-2 py-1 rounded"
                        >{{ secret }}</code
                    >
                </div>

                <!-- Activatie formulier -->
                <form @submit.prevent="enableTwoFactor" class="max-w-sm">
                    <div class="mb-4">
                        <label
                            for="code"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Verificatiecode
                        </label>
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="123456"
                            maxlength="6"
                            required
                        />
                        <div
                            v-if="form.errors.code"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors.code }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                    >
                        <span v-if="form.processing">{{
                            $t("two_factor.activate_button_loading")
                        }}</span>
                        <span v-else>{{
                            $t("two_factor.activate_button")
                        }}</span>
                    </button>
                </form>
            </div>

            <!-- 2FA is actief -->
            <div v-else class="mb-6">
                <div class="mb-6">
                    <div class="flex items-center mb-4">
                        <svg
                            class="w-5 h-5 text-green-500 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <span class="text-green-600 font-medium">{{
                            $t("two_factor.active")
                        }}</span>
                    </div>

                    <div
                        class="bg-green-50 border border-green-200 rounded-md p-4 mb-4"
                    >
                        <p class="text-sm text-green-700">
                            {{ $t("two_factor.active_description") }}
                        </p>
                        <p
                            class="text-sm text-green-600 mt-1"
                            v-if="$page.props.last_verified"
                        >
                            {{ $t("two_factor.last_verified") }}:
                            {{ $page.props.last_verified }}
                        </p>
                    </div>
                </div>

                <!-- Informatie -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-2">
                        {{ $t("two_factor.how") }}?
                    </h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• {{ $t("two_factor.steps.1") }}</li>
                        <li>• {{ $t("two_factor.steps.2") }}</li>
                        <li>• {{ $t("two_factor.steps.3") }}</li>
                    </ul>
                </div>

                <div class="border-t pt-6">
                    <!-- Deactivatie formulier -->
                    <form @submit.prevent="disableTwoFactor" class="max-w-sm">
                        <div class="mb-4">
                            <label
                                for="disable_code"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                {{ $t("two_factor.disable_title") }}
                            </label>
                            <input
                                id="disable_code"
                                v-model="disableForm.code"
                                type="text"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="123456"
                                maxlength="6"
                                required
                            />
                            <div
                                v-if="disableForm.errors.code"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ disableForm.errors.code }}
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="disableForm.processing"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                        >
                            <span v-if="disableForm.processing">{{
                                $t("two_factor.disable_button_loading")
                            }}</span>
                            <span v-else>{{
                                $t("two_factor.disable_button")
                            }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QRCode from "qrcode";

const props = defineProps({
    qrCodeUrl: String,
    secret: String,
    is_enabled: Boolean,
    last_verified: String,
});

console.log(props);

const qrCanvas = ref(null);

const form = useForm({
    code: "",
});

const disableForm = useForm({
    code: "",
});

const enableTwoFactor = () => {
    form.post(route("two-factor.enable"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const disableTwoFactor = () => {
    disableForm.post(route("two-factor.disable"), {
        onSuccess: () => {
            disableForm.reset();
        },
    });
};

onMounted(() => {
    if (props.qrCodeUrl && qrCanvas.value) {
        QRCode.toCanvas(qrCanvas.value, props.qrCodeUrl, function (error) {
            if (error) console.error(error);
        });
    }
});
</script>
