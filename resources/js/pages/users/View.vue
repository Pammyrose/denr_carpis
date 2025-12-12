<!-- resources/js/Pages/Users/View.vue -->
<script setup lang="ts">
import { defineProps, toRefs } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

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
  <div
   
    @click.self="closeModal"
  >
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative border border-gray-300">
      <!-- Close Button -->
      <button
        @click="closeModal"
        class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 z-10"
        aria-label="Close modal"
      >
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Modal Content -->
      <div class="p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">User Details</h1>

        <div v-if="user" class="space-y-8 text:black dark:text-black">
          <!-- Basic Info -->
          <div>
            <h2 class="text-lg font-semibold text-black mb-3">User Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
              <p><strong>Full Name:</strong> {{ user.full_name || 'N/A' }}</p>
              <p><strong>Email:</strong> {{ user.email }}</p>
            </div>
          </div>

          <!-- Employee Details -->
          <div v-if="user.employee">
            <h2 class="text-lg font-semibold text-black mb-3">Employee Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
              <p><strong>Sex:</strong> {{ user.employee.sex || 'N/A' }}</p>
              <p><strong >Employment Status:</strong> {{ user.employee.emp_status || 'N/A' }}</p>
              <p><strong>Position:</strong> {{ user.employee.position_name || 'N/A' }}</p>
              <p><strong >Assignment:</strong> {{ user.employee.assignment_name || 'N/A' }}</p>
              <p><strong >Division/Section/Unit:</strong> {{ user.employee.div_sec_unit || 'N/A' }}</p>
            </div>
          </div>
          <div v-else class="text-gray-500 italic">
            No employee details available.
          </div>



        </div>

        <div v-else class="text-center text-gray-500">
          Loading user details...
        </div>
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