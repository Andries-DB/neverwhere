<template>
    <img :src="buildImagePath('logo.png')" alt="Logo" />
</template>

<script>
export default {
    methods: {
        buildImagePath(filename) {
            return `../../../../assets/${filename}`;
        },
    },
};
</script>
