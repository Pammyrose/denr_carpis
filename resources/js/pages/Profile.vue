<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

defineProps<{
    user?: {
        email?: string;
        employee?: {
            first_name?: string;
            middle_name?: string;
            last_name?: string;
            suffix?: string;
            sex?: string;
            emp_status?: string;
            position_name?: string;
            assignment_name?: string;
            div_sec_unit?: string;
        } | null;
    } | null;
}>();

const timeLeft = ref(10);
let timer: ReturnType<typeof setTimeout> | undefined;
let interval: ReturnType<typeof setInterval> | undefined;

// FULLY FIXED: Bypass Inertia completely on logout (prevents 500/error flash)
const performLogout = () => {
    if (interval) clearInterval(interval);
    if (timer) clearTimeout(timer);

    router.post(route('logout'), {}, { onFinish: () => window.location.assign(route('home')) });
};

const startAutoLogout = () => {
    interval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            if (interval) clearInterval(interval);
        }
    }, 1000);

    timer = setTimeout(performLogout, 10000); // 10 seconds
};

const leaveNow = () => {
    performLogout();
};

onMounted(() => {
    startAutoLogout();
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
    if (timer) clearTimeout(timer);
});
</script>

<template>
    <div class="bg-custom-bg custom-bg flex min-h-svh flex-col items-center justify-center gap-6 bg-cover bg-center p-6 md:p-10">
        <div class="relative w-full max-w-4xl rounded-lg bg-white p-8 shadow-2xl">
            <!-- Close/Logout Button -->
            <button @click="leaveNow" class="absolute top-4 right-4 text-2xl font-bold text-gray-500 hover:text-red-600">✕</button>

            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="text-gray-600">{{ user?.email }}</p>
            </div>

            <div v-if="user?.employee" class="space-y-6 text-left">
                <h2 class="text-2xl font-semibold text-gray-800">Employee Information</h2>
                <div class="grid grid-cols-1 gap-4 rounded-lg bg-gray-50 p-6 text-sm text-gray-900 md:grid-cols-2 dark:text-gray-900">
                    <div>
                        <strong>Full Name:</strong> {{ user.employee.first_name }} {{ user.employee.middle_name }} {{ user.employee.last_name }}
                        {{ user.employee.suffix }}
                    </div>
                    <div><strong>Sex:</strong> {{ user.employee.sex }}</div>
                    <div><strong>Status:</strong> {{ user.employee.emp_status }}</div>
                    <div><strong>Position:</strong> {{ user.employee.position_name }}</div>
                    <div><strong>Assignment:</strong> {{ user.employee.assignment_name }}</div>
                    <div><strong>Unit:</strong> {{ user.employee.div_sec_unit }}</div>
                </div>
            </div>

            <div v-else class="py-10 text-center text-gray-500">No employee record found.</div>

            <div class="mt-8 text-center text-sm text-gray-500">
                This page will automatically log you out in <strong>{{ timeLeft }}</strong> seconds for security.
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-bg {
    background-image: url('bg.jpg');
    background-size: cover;
    background-position: center;
}
</style>
