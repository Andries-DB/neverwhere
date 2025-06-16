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
        class="border-gray-400 bg-white text-black rounded-md shadow-sm focus:border-gray-400 focus:ring-0"
        v-model="model"
        ref="input"
        :placeholder="placeholder"
    />
</template>
