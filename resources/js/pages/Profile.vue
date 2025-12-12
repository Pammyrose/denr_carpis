<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
  user: Object
});

const timeLeft = ref(10);
let timer = null;
let interval = null;

// FULLY FIXED: Bypass Inertia completely on logout (prevents 500/error flash)
const performLogout = () => {
  clearInterval(interval);
  clearTimeout(timer);

  // Get CSRF token from meta tag (Laravel includes it by default)
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  // Regular fetch POST (not Inertia) → backend logs out → redirects normally
  fetch('/carpis/public/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken || '',
      // Important for session cookies
      'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    },
    body: JSON.stringify({}),
    credentials: 'same-origin', // sends cookies (session)
  })
  .then(response => {
    // Even if response is 302, we don't care — just force full reload
    window.location.href = '/carpis/public/';
  })
  .catch(() => {
    // Even on network error, force redirect
    window.location.href = '/carpis/public/';
  });
};

const startAutoLogout = () => {
  interval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--;
    } else {
      clearInterval(interval);
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
  clearInterval(interval);
  clearTimeout(timer);
});
</script>

<template>
  <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-custom-bg bg-cover bg-center custom-bg p-6 md:p-10">
    
    <div class="relative bg-white rounded-lg shadow-2xl max-w-4xl w-full p-8">

      <!-- Close/Logout Button -->
      <button @click="leaveNow" class="absolute top-4 right-4 text-gray-500 hover:text-red-600 font-bold text-2xl">
        ✕
      </button>

      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600">{{ user?.email }}</p>
      </div>

      <div v-if="user?.employee" class="space-y-6 text-left">
        <h2 class="text-2xl font-semibold text-gray-800">Employee Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-gray-50 p-6 rounded-lg">
          <div><strong>Full Name:</strong> {{ user.employee.first_name }} {{ user.employee.middle_name }} {{ user.employee.last_name }} {{ user.employee.suffix }}</div>
          <div><strong>Sex:</strong> {{ user.employee.sex }}</div>
          <div><strong>Status:</strong> {{ user.employee.emp_status }}</div>
          <div><strong>Position:</strong> {{ user.employee.position_name }}</div>
          <div><strong>Assignment:</strong> {{ user.employee.assignment_name }}</div>
          <div><strong>Unit:</strong> {{ user.employee.div_sec_unit }}</div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500 py-10">
        No employee record found.
      </div>

      <div class="text-center mt-8 text-sm text-gray-500">
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