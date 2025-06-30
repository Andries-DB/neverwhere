<template>
    <Head title="2FA Verificatie" />

    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
    >
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <div class="mb-4 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Verificatie vereist
                </h2>
                <p class="text-gray-600">
                    Voer de 6-cijferige code in uit je authenticator app.
                </p>
            </div>

            <form @submit.prevent="submit">
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
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center text-2xl tracking-widest"
                        placeholder="123456"
                        maxlength="6"
                        required
                        autofocus
                        @input="formatCode"
                    />
                    <div
                        v-if="form.errors.code"
                        class="text-red-600 text-sm mt-1 text-center"
                    >
                        {{ form.errors.code }}
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        :disabled="form.processing || form.code.length !== 6"
                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Verifiëren...</span>
                        <span v-else>Verifiëren</span>
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <form @submit.prevent="logout" class="inline">
                    <button
                        type="submit"
                        class="text-sm text-gray-600 hover:text-gray-900 underline"
                    >
                        Uitloggen
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";

const form = useForm({
    code: "",
});

const submit = () => {
    form.post(route("two-factor.confirm"));
};

const formatCode = (event) => {
    // Alleen cijfers toestaan
    event.target.value = event.target.value.replace(/\D/g, "");
    form.code = event.target.value;
};

const logout = () => {
    router.post(route("logout"));
};
</script>
