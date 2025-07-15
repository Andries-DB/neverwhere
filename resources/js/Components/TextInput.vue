<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border border-gray-300 bg-white text-black rounded-md focus:outline-none focus:ring-0 px-3 py-[6px]"
        v-model="model"
        ref="input"
        :placeholder="placeholder"
    />
</template>
