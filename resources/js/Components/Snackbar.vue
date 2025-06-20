<!-- Snackbar.vue -->
<template>
    <transition name="fade">
        <div
            v-if="visible"
            :class="[
                'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-lg shadow-md text-sm flex items-center gap-2',
                type === 'success'
                    ? 'bg-green-100 text-green-800 border border-green-300'
                    : 'bg-red-100 text-red-800 border border-red-300',
            ]"
        >
            <i
                :class="[
                    'fas',
                    type === 'success'
                        ? 'fa-check-circle'
                        : 'fa-exclamation-circle',
                ]"
            ></i>
            <span>{{ message }}</span>
        </div>
    </transition>
</template>

<script>
export default {
    name: "Snackbar",
    data() {
        return {
            message: "",
            type: "success", // 'success' | 'error'
            visible: false,
            timeoutId: null,
        };
    },
    methods: {
        show(message, type = "success", duration = 3000) {
            this.message = message;
            this.type = type;
            this.visible = true;

            if (this.timeoutId) clearTimeout(this.timeoutId);
            this.timeoutId = setTimeout(() => {
                this.visible = false;
            }, duration);
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
