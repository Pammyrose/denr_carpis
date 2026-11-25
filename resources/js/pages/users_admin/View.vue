<!-- resources/js/Pages/Users/View.vue -->
<script setup lang="ts">
import { defineProps, toRefs } from 'vue';

interface Employee {
    sex?: string;
    emp_status?: string;
    position_name?: string;
    assignment_name?: string;
    div_sec_unit?: string;
}

interface Asset {
    id: number;
    name?: string;
    category?: string;
    location?: string;
    purchase_date?: string;
    return_date?: string;
}

interface User {
    id: number;
    full_name?: string;
    email: string;
    employee?: Employee;
    assets?: Asset[];
}

const props = defineProps<{
    user: User;
    closeModal: () => void;
}>();

const { user, closeModal } = toRefs(props);
</script>

<template>
    <div @click.self="closeModal">
        <div class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl border border-gray-300 bg-white shadow-2xl">
            <!-- Close Button -->
            <button @click="closeModal" class="absolute top-4 right-4 z-10 text-gray-600 hover:text-gray-900" aria-label="Close modal">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Content -->
            <div class="p-8">
                <h1 class="mb-6 text-2xl font-bold text-gray-800">User Details</h1>

                <div v-if="user" class="text:black space-y-8 dark:text-black">
                    <!-- Basic Info -->
                    <div>
                        <h2 class="mb-3 text-lg font-semibold text-black">User Information</h2>
                        <div class="grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-2">
                            <p><strong>Full Name:</strong> {{ user.full_name || 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ user.email }}</p>
                        </div>
                    </div>

                    <!-- Employee Details -->
                    <div v-if="user.employee">
                        <h2 class="mb-3 text-lg font-semibold text-black">Employee Details</h2>
                        <div class="grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-4 md:grid-cols-2">
                            <p><strong>Sex:</strong> {{ user.employee.sex || 'N/A' }}</p>
                            <p><strong>Employment Status:</strong> {{ user.employee.emp_status || 'N/A' }}</p>
                            <p><strong>Position:</strong> {{ user.employee.position_name || 'N/A' }}</p>
                            <p><strong>Assignment:</strong> {{ user.employee.assignment_name || 'N/A' }}</p>
                            <p><strong>Division/Section/Unit:</strong> {{ user.employee.div_sec_unit || 'N/A' }}</p>
                        </div>
                    </div>
                    <div v-else class="text-gray-500 italic">No employee details available.</div>
                </div>

                <div v-else class="text-center text-gray-500">Loading user details...</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: improve scroll behavior */
.max-h-\[90vh\] {
    max-height: 90vh;
}
</style>
