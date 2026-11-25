<script setup lang="ts">
defineProps<{
    close: () => void;
}>();

defineEmits(['close']);
</script>

<template>
    <div class="shadows-2xl bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-transparent">
        <div class="w-full max-w-md rounded-lg bg-gray-100 p-6 shadow-lg">
            <slot name="header"></slot>
            <slot name="body"></slot>
        </div>
    </div>
</template>

<style scoped>
/* Modal styling */
</style>
