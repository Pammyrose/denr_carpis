<script setup lang="ts">
defineProps<{
    close: () => void;
}>();

defineEmits(['close']);
</script>

<template>
    <div class="fixed inset-0 bg-transparent shadows-2xl bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-100 rounded-lg shadow-lg p-6 w-full max-w-md">
            <slot name="header"></slot>
            <slot name="body"></slot>
        </div>
    </div>
</template>

<style scoped>
/* Modal styling */
</style>