<template>
    <Head title="2FA Instellen" />

    <AuthenticatedLayout>
        <div class="p-6 text-gray-900">
            <div v-if="!is_enabled" class="mb-6">
                <h3 class="text-lg font-medium mb-4">2FA Activeren</h3>
                <p class="text-gray-600 mb-4">
                    Scan de QR-code met je Google Authenticator app en voer de
                    6-cijferige code in om 2FA te activeren.
                </p>

                <!-- QR Code weergave -->
                <div class="mb-4">
                    <canvas ref="qrCanvas" class="border rounded"></canvas>
                </div>

                <!-- Handmatige invoer secret -->
                <div class="mb-4 p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-600 mb-2">
                        Of voer handmatig in:
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
                        <span v-if="form.processing">Activeren...</span>
                        <span v-else>2FA Activeren</span>
                    </button>
                </form>
            </div>

            <!-- 2FA is actief -->
            <div v-else class="mb-6">
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
                    <span class="text-green-600 font-medium"
                        >2FA is actief</span
                    >
                </div>

                <p class="text-gray-600 mb-4">
                    Tweestapsverificatie is ingeschakeld voor je account. Je
                    wordt om de 14 dagen om een verificatiecode gevraagd.
                </p>

                <!-- Deactivatie formulier -->
                <form @submit.prevent="disableTwoFactor" class="max-w-sm">
                    <div class="mb-4">
                        <label
                            for="disable_code"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Voer je huidige verificatiecode in om 2FA uit te
                            schakelen:
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
                        <span v-if="disableForm.processing"
                            >Uitschakelen...</span
                        >
                        <span v-else>2FA Uitschakelen</span>
                    </button>
                </form>
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
});

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
