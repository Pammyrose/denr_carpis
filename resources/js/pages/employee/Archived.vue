<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';

interface ArchivedEmployee {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
  suffix: string;
  sex: string;
  email: string;
  emp_status: string;
  position_id?: string;
  assignment_id?: string;
  div_sec_unit?: string;
  archived_at: string;
}

interface BreadcrumbItem {
  title: string;
  href: string;
}

const props = defineProps<{
  archivedEmployees: ArchivedEmployee[] | null;
  error?: string | null;
}>();

const page = usePage();

const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

onMounted(() => {
  console.log('Archived.vue: Props received', {
    archivedEmployees: props.archivedEmployees,
    error: props.error,
    flash: (page.props as any).flash,
  });
  if (props.error) {
    console.log('Archived.vue: Error prop detected:', props.error);
    alertMessage.value = props.error;
    alertType.value = 'error';
    setTimeout(() => {
      alertMessage.value = '';
      alertType.value = '';
    }, 3000);
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  
  { title: 'Archived Employees', href: '/archived' },
  
];

const searchQuery = ref('');

const filteredArchivedEmployees = computed(() => {
  if (!props.archivedEmployees || !Array.isArray(props.archivedEmployees)) {
    console.warn('Archived.vue: No archived employees or invalid prop', props.archivedEmployees);
    return [];
  }
  if (!searchQuery.value.trim()) {
    return props.archivedEmployees;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return props.archivedEmployees.filter(employee => {
    const fullName = `${employee.first_name} ${employee.middle_name || ''} ${employee.last_name} ${employee.suffix || ''}`.toLowerCase();
    return (
      fullName.includes(query) ||
      (employee.position_id?.toLowerCase().includes(query) ?? false) ||
      (employee.emp_status?.toLowerCase().includes(query) ?? false) ||
      (employee.assignment_id?.toLowerCase().includes(query) ?? false) ||
      (employee.div_sec_unit?.toLowerCase().includes(query) ?? false) ||
      (employee.archived_at?.toLowerCase().includes(query) ?? false)
    );
  });
});

const openConfirmUnarchiveModal = (employeeId: number) => {
  console.log('Opening confirm unarchive modal for employee:', employeeId);
  if (confirm('Are you sure you want to unarchive this employee? This will restore them to the active employees list.')) {
    unarchiveEmployee(employeeId);
  }
};

const unarchiveEmployee = (employeeId: number) => {
  console.log('Unarchiving employee:', employeeId);
  router.post(route('employee.unarchive', employeeId), {}, {
    onSuccess: () => {
      console.log('Unarchive successful');
      // Update the archived employees list by filtering out the unarchived employee
      if (props.archivedEmployees) {
        props.archivedEmployees = props.archivedEmployees.filter(emp => emp.id !== employeeId);
      }
      alertMessage.value = 'Employee unarchived successfully';
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
    onError: (errors) => {
      console.error('Unarchive failed:', errors);
      alertMessage.value = 'Failed to unarchive employee: ' + Object.values(errors).join(', ');
      alertType.value = 'error';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
  });
};

watch(
  () => (page.props as any).flash,
  (flash) => {
    console.log('Archived.vue: Watch triggered, flash:', flash);
    if (flash?.message) {
      console.log('Archived.vue: Flash message detected:', flash.message);
      alertMessage.value = flash.message;
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 2000);
    }
  },
  { immediate: true }
);
</script>

<template>
  <Head title="Archived Employees" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Alert -->
      <div
        v-if="alertMessage"
        :class="{
          'bg-green-100 border-green-400 text-green-700': alertType === 'success',
          'bg-red-100 border-red-400 text-red-700': alertType === 'error',
        }"
        class="border-l-4 p-4 mb-4 rounded-r-md"
        role="alert"
      >
        <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
        <p>{{ alertMessage }}</p>
      </div>

<div class="flex justify-between items-center mb-4 gap-4">

  <!-- Back Button -->
  <div>
    <Link
      :href="route('employee.index')"
      class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 inline-flex items-center gap-2"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
          stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
        <path stroke-linecap="round" stroke-linejoin="round" 
              d="M15.75 19.5 8.25 12l7.5-7.5" />
      </svg>
     
    </Link>
  </div>

  <!-- Search Input -->
  <div class="relative w-full max-w-md">
    <input
      v-model="searchQuery"
      type="text"
      placeholder="Search..."
      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"
    />
    <svg
      class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
  </div>

</div>


      

      <!-- Archived Employees Table -->
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
            <tr>
              <th scope="col" class="px-4 py-3">No</th>
              <th scope="col" class="px-4 py-3 text-center">Name</th>
              <th scope="col" class="px-4 py-3 text-center">Position</th>
              <th scope="col" class="px-4 py-3 text-center">Division</th>
              <th scope="col" class="px-4 py-3 text-center">Assignment</th>
              <th scope="col" class="px-4 py-3 text-center">Status</th>
              <th scope="col" class="px-4 py-3 text-center">Archived At</th>
              <th scope="col" class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
<tr
  v-for="(employee, index) in filteredArchivedEmployees"
  :key="employee.id"
  class="bg-white border-b dark:bg-white dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 dark:text-black dark:hover:text-white"
>

              <th scope="row" class="px-4 py-1 font-medium ">
                 {{ index + 1 }}
              </th>
              <td class="px-2 py-1 text-center">
                 {{ employee.last_name }}, {{ employee.first_name }} {{ employee.middle_name || '' }} {{ employee.suffix || '' }}
              </td>
              <td class="px-2 py-1 text-center">{{ employee.position_id || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ employee.div_sec_unit || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ employee.assignment_id || 'N/A' }}</td>
<td class="px-2 py-1 text-center">
                <span
                  :class="{
                    'text-white bg-green-600 hover:bg-green-800': employee.emp_status === 'Regular',
                    'text-white bg-red-700 hover:bg-red-800': employee.emp_status === 'Retired',
                    
                  }"
                  class="font-medium rounded-full text-sm px-5 py-1.5 text-center me-2 mb-2"
                >
                  {{ employee.emp_status || 'N/A' }}
                </span>
              </td>
              <td class="px-2 py-1 text-center">{{ employee.archived_at || 'N/A' }}</td>
              <td class="px-4 py-1 flex justify-center items-center gap-1">
                <Button
                  @click="openConfirmUnarchiveModal(employee.id)"
                  class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                >
                  Unarchive
                </Button>
              </td>
            </tr>
            <tr v-if="!filteredArchivedEmployees.length">
              <td colspan="7" class="text-center py-4">No archived employees found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>