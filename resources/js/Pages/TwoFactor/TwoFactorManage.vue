<template>
    <Head title="2FA Beheren" />

    <AuthenticatedLayout>
        <div class="p-6 text-gray-900">
            <!-- Status -->
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
                    <span class="text-green-600 font-medium"
                        >2FA is actief</span
                    >
                </div>

                <div
                    class="bg-green-50 border border-green-200 rounded-md p-4 mb-4"
                >
                    <p class="text-sm text-green-700">
                        Tweestapsverificatie is ingeschakeld voor je account en
                        werkt correct.
                    </p>
                    <p class="text-sm text-green-600 mt-1" v-if="lastVerified">
                        Laatste verificatie: {{ lastVerified }}
                    </p>
                </div>
            </div>

            <!-- Informatie -->
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Hoe werkt het?</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>
                        • Je wordt om de 14 dagen gevraagd om een
                        verificatiecode in te voeren
                    </li>
                    <li>
                        • Gebruik je Google Authenticator app om de code te
                        genereren
                    </li>
                    <li>
                        • Zorg ervoor dat je altijd toegang hebt tot je
                        authenticator app
                    </li>
                </ul>
            </div>

            <!-- Gevaar zone -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-red-600 mb-4">
                    Gevaar Zone
                </h3>
                <div
                    class="bg-red-50 border border-red-200 rounded-md p-4 mb-4"
                >
                    <p class="text-sm text-red-700 mb-2">
                        <strong>Waarschuwing:</strong> Als je 2FA uitschakelt,
                        moet je het opnieuw instellen voordat je verder kunt
                        werken.
                    </p>
                    <p class="text-sm text-red-600">
                        Zorg ervoor dat je toegang hebt tot je authenticator app
                        voordat je 2FA uitschakelt.
                    </p>
                </div>

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
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center"
                            placeholder="123456"
                            maxlength="6"
                            required
                            @input="formatCode"
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
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :disabled="disableForm.processing"
                    >
                        Uitschakelen
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const { props } = usePage();

const disableForm = useForm({
    code: "",
});

const lastVerified = props.lastVerified;

function formatCode() {
    // Alleen cijfers toelaten en trimmen op 6 cijfers
    disableForm.code = disableForm.code.replace(/\D/g, "").slice(0, 6);
}

function disableTwoFactor() {
    disableForm.post(route("two-factor.disable"), {
        preserveScroll: true,
        onSuccess: () => {
            disableForm.reset();
        },
    });
}
</script>
