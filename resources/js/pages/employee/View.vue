<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';

interface Asset {
  id: number;
  name: string | null;
  category: string | null;
  location: string | null;
  purchase_date: string | null;
  return_date?: string | null;
}

interface Employee {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
  suffix: string;
  sex: string;
  email: string;
  emp_status: string;
  position_name: string | null;
  assignment_name: string | null;
  div_sec_unit: string | null;
  date_of_birth: string | null;
  tin_no: string | null;
  date_appointment: string | null;
  date_last_promotion: string | null;
  civil_service: string | null;
  education: string | null;
  assets: Asset[] | null;
}

const props = defineProps<{
  employee: Employee | null;
  error?: string | null;
}>();
</script>

<template>
  <Head :title="employee ? `Employee #${employee.id}` : 'Employee Not Found'" />

  <div class="relative">
    <!-- Full-screen modal-like container (same as Create.vue) -->
    


      <!-- Main Content -->
      <div class="p-4 pt-0">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Employee Details</h2>

        <!-- Error Message -->
        <div v-if="props.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
          <strong class="font-bold">Error!</strong>
          <span class="block sm:inline ml-2">{{ props.error }}</span>
        </div>

        <!-- Employee Details -->
        <div v-else-if="props.employee" class="bg-white shadow-md rounded-lg p-8">

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-black">

            <!-- Column 1 -->
            <div class="space-y-2 md:space-y-5">
              <p><strong class="text-gray-700">Employee ID:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.id }}</span>
              </p>
              <p><strong class="text-gray-700">Full Name:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.first_name }} {{ props.employee.middle_name || '' }} {{ props.employee.last_name }} {{ props.employee.suffix || '' }}</span>
              </p>
              <p><strong class="text-gray-700">Sex:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.sex || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Email:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.email || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Date of Birth:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.date_of_birth || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">TIN No:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.tin_no || 'N/A' }}</span>
              </p>
            </div>

            <!-- Column 2 -->
            <div class="space-y-2 md:space-y-5">
              <p><strong class="text-gray-700">Status:</strong> 
                <span class="ml-2 inline-block px-3 py-1 text-sm font-medium rounded-full"
                      :class="{
                        'bg-green-600 text-white': props.employee.emp_status === 'Active',
                        'bg-red-700 text-white': props.employee.emp_status === 'Inactive',
                        'bg-yellow-600 text-white': props.employee.emp_status === 'On Leave',
                        'bg-gray-500 text-white': !['Active', 'Inactive', 'On Leave'].includes(props.employee.emp_status)
                      }">
                  {{ props.employee.emp_status || 'N/A' }}
                </span>
              </p>
              <p><strong class="text-gray-700">Position:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.position_name || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Assignment:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.assignment_name || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Division/Unit:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.div_sec_unit || 'N/A' }}</span>
              </p>
            </div>

            <!-- Column 3 -->
            <div class="space-y-2 md:space-y-5">
              <p><strong class="text-gray-700">Date of Appointment:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.date_appointment || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Date of Last Promotion:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.date_last_promotion || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Civil Service:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.civil_service || 'N/A' }}</span>
              </p>
              <p><strong class="text-gray-700">Education:</strong> 
                <span class="ml-2 text-sm md:text-base">{{ props.employee.education || 'N/A' }}</span>
              </p>
            </div>
          </div>


        </div>

        <!-- No Employee Found -->
        <div v-else class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded" role="alert">
          <strong class="font-bold">Error!</strong>
          <span class="block sm:inline ml-2">Employee not found or failed to load.</span>
        </div>
      </div>
   
      
  </div>
</template>

<style scoped>
/* Same styling as Create.vue for consistency */
select,
input,
textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #fff;
}

textarea {
  min-height: 100px;
}

/* Ensure consistent spacing and responsiveness */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
  }
}


</style>