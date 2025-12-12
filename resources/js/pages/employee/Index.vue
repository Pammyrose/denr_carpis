<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import ViewEmployee from './View.vue';

const excelForm = useForm({
  excel_file: null as File | null,
});

const handleExcelUpload = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (!input.files?.length) return;

  excelForm.excel_file = input.files[0];
  excelForm.post(route('employee.import.excel'), {
    forceFormData: true,
    onSuccess: () => {
      alertMessage.value = 'Excel imported successfully';
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
      input.value = '';
    },
    onError: (errors) => {
      alertMessage.value = Object.values(errors).join(', ');
      alertType.value = 'error';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 5000);
    },
  });
};

interface Employee {
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
  org_unit_id?: string;
  position_name?: string;
  assignment_name?: string;
  div_sec_unit?: string;
}

interface Dropdowns {
  orgUnits: Array<{ value: string; label: string }>;
  positions: Array<{ value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }>;
  assignmentPlaces: Array<{ value: string; label: string }>;
  empStatuses: Array<{ value: string; label: string }>;
}

interface BreadcrumbItem {
  title: string;
  href: string;
}

const props = defineProps<{
  employees: Employee[] | null;
  dropdowns: Dropdowns | null;
  error?: string | null;
  divisions?: string[];
  selectedDivision?: string | null;
}>();

const page = usePage();

const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

onMounted(() => {
  console.log('Index.vue: Props received', {
    employees: props.employees,
    dropdowns: props.dropdowns,
    error: props.error,
    flash: (page.props as any).flash,
  });
  if (props.error) {
    console.log('Index.vue: Error prop detected:', props.error);
    alertMessage.value = props.error;
    alertType.value = 'error';
    setTimeout(() => {
      alertMessage.value = '';
      alertType.value = '';
    }, 3000);
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Employee', href: '/employee' },
];

const searchQuery = ref('');
const divisionFilter = ref(props.selectedDivision ?? "");
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedEmployee = ref<Employee | null>(null);
const viewedEmployee = ref<Employee | null>(null);

// ADDED: Sorting state for Name column
const sortColumn = ref<'name' | ''>('');
const sortDirection = ref<'asc' | 'desc' | ''>('');

// ONLY THIS FUNCTION WAS MISSING — NOW ADDED
const applyDivisionFilter = () => {
  const params = divisionFilter.value ? { div_sec_unit: divisionFilter.value } : {};
  router.visit(route('employee.index'), {
    data: params,
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

// UPDATED: filteredEmployees now includes sorting by name
const filteredEmployees = computed(() => {
  if (!props.employees || !Array.isArray(props.employees)) {
    console.warn('Index.vue: No employees or invalid employees prop', props.employees);
    return [];
  }

  let list = [...props.employees];

  // Apply search filter
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    list = list.filter(employee => {
      const fullName = `${employee.first_name} ${employee.middle_name || ''} ${employee.last_name} ${employee.suffix || ''}`.toLowerCase();
      return (
        fullName.includes(query) ||
        (employee.position_name?.toLowerCase().includes(query) ?? false) ||
        (employee.emp_status?.toLowerCase().includes(query) ?? false) ||
        (employee.assignment_name?.toLowerCase().includes(query) ?? false) ||
        (employee.div_sec_unit?.toLowerCase().includes(query) ?? false)
      );
    });
  }

  // Apply sorting by name if active
  if (sortColumn.value === 'name' && sortDirection.value) {
    list.sort((a, b) => {
      const nameA = `${a.last_name} ${a.first_name} ${a.middle_name || ''} ${a.suffix || ''}`.trim().toLowerCase();
      const nameB = `${b.last_name} ${b.first_name} ${b.middle_name || ''} ${b.suffix || ''}`.trim().toLowerCase();
      const comparison = nameA.localeCompare(nameB);
      return sortDirection.value === 'asc' ? comparison : -comparison;
    });
  }

  return list;
});

const openCreateModal = () => {
  console.log('Opening create modal, current state:', isCreateModalOpen.value);
  isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
  console.log('Closing create modal');
  isCreateModalOpen.value = false;
};

const openEditModal = (employee: Employee) => {
  console.log('Opening edit modal for employee:', employee);
  selectedEmployee.value = { ...employee };
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  console.log('Closing edit modal');
  isEditModalOpen.value = false;
  selectedEmployee.value = null;
};

const openViewModal = (employee: Employee) => {
  viewedEmployee.value = { ...employee };
  isViewModalOpen.value = true;
};

const closeViewModal = () => {
  isViewModalOpen.value = false;
  viewedEmployee.value = null;
};

const openConfirmArchiveModal = (employeeId: number) => {
  console.log('Opening confirm archive modal for employee:', employeeId);
  if (confirm('Are you sure you want to archive this employee? This action cannot be undone.')) {
    archiveEmployee(employeeId);
  }
};

const archiveEmployee = (employeeId: number) => {
  console.log('Archiving employee:', employeeId);
  router.post(route('employee.store'), { archive_employee_id: employeeId }, {
    onSuccess: () => {
      console.log('Archive successful');
      if (props.employees) {
        props.employees = props.employees.filter(emp => emp.id !== employeeId);
      }
      alertMessage.value = 'Employee archived successfully';
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
    onError: (errors) => {
      console.error('Archive failed:', errors);
      alertMessage.value = 'Failed to archive employee: ' + Object.values(errors).join(', ');
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
    console.log('Index.vue: Watch triggered, flash:', flash);
    if (flash?.message) {
      console.log('Index.vue: Flash message detected:', flash.message);
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

const uniqueSortedDivisions = computed(() => {
  if (!props.divisions || !Array.isArray(props.divisions)) return [];

  const divisions = props.divisions.map(item => {
    if (typeof item === 'string') return item;
    if (item && typeof item === 'object' && item.div_sec_unit) return item.div_sec_unit;
    return null;
  });

  return [...new Set(divisions.filter(Boolean))]
    .sort((a, b) => a.localeCompare(b));
});
</script>

<template>
  <Head title="Employee" />

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

      <!-- Search Input + Division Filter + Create Button -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4 gap-4">
        <!-- Left: Action Buttons -->
        <div class="flex gap-2">
          <Button
            @click="openCreateModal"
            class="bg-blue-600 text-white hover:bg-blue-700"
          >
            Create
          </Button>
          <Link
            :href="route('employee.archived')"
            class="bg-gray-200 text-white px-4 py-2 rounded-md"
          >
            <svg class="w-4 h-4 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" viewBox="0 0 24 24">
              <path fill-rule="evenodd" d="M20 10H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8ZM9 13v-1h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
              <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Z"/>
            </svg>
          </Link>

          <!-- Excel Upload Button -->
          <label class="cursor-pointer bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            <input
              ref="excelFileInput"
              type="file"
              accept=".xlsx,.xls"
              class="hidden"
              @change="handleExcelUpload"
            />
          </label>
        </div>

        <!-- Right: Division Filter + Search -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 w-full lg:w-auto">
          <select
            v-model="divisionFilter"
            @change="applyDivisionFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full dark:text-white"
          >
            <option value="" class="dark:text-black">All Divisions</option>
            <option
              v-for="division in uniqueSortedDivisions"
              :key="division"
              :value="division"
              class="dark:text-black"
            >
              {{ division }}
            </option>
          </select>

          <div class="relative w-full">
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
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- Modals (unchanged) -->
      <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs" @click.self="closeCreateModal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
          <button @click="closeCreateModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none" aria-label="Close modal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <Create
            :orgUnits="props.dropdowns?.orgUnits ?? []"
            :positions="props.dropdowns?.positions ?? []"
            :assignmentPlaces="props.dropdowns?.assignmentPlaces ?? []"
            :empStatuses="props.dropdowns?.empStatuses ?? []"
            :close="closeCreateModal"
          />
        </div>
      </div>

      <div v-if="isEditModalOpen && selectedEmployee" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs" @click.self="closeEditModal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
          <button @click="closeEditModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none" aria-label="Close modal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <Edit
            :employee="selectedEmployee"
            :orgUnits="props.dropdowns?.orgUnits ?? []"
            :positions="props.dropdowns?.positions ?? []"
            :assignmentPlaces="props.dropdowns?.assignmentPlaces ?? []"
            :empStatuses="props.dropdowns?.empStatuses ?? []"
            :close="closeEditModal"
          />
        </div>
      </div>

      <div v-if="isViewModalOpen && viewedEmployee" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs" @click.self="closeViewModal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
          <button @click="closeViewModal" class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none" aria-label="Close modal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <ViewEmployee :employee="viewedEmployee" />
        </div>
      </div>

      <!-- Employee Table -->
      <div class="bg-white shadow-xl rounded-lg overflow-hidden overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
            <tr>
              <th scope="col" class="px-4 py-3">No</th>
              
              <!-- SORTABLE NAME COLUMN -->
              <th 
  scope="col" 
  class="px-4 py-2 text-center cursor-pointer select-none"
  @click="() => {
    if (sortColumn === 'name') {
      sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn = 'name';
      sortDirection = 'asc';
    }
  }"
>
  <div class="flex items-center justify-center gap-2">
    Name
    <div class="flex flex-col">
      <svg 
        class="w-3 h-3 text-white transition-opacity" 
        :class="{ 'opacity-100': sortColumn === 'name' && sortDirection === 'asc', 'opacity-30': sortColumn !== 'name' || sortDirection !== 'asc' }"
        fill="currentColor" 
        viewBox="0 0 24 24"
      >
        <path d="M12 8l-6 6h12l-6-6z"/>
      </svg>
      <svg 
        class="w-3 h-3 text-white -mt-1 transition-opacity rotate-180" 
        :class="{ 'opacity-100': sortColumn === 'name' && sortDirection === 'desc', 'opacity-30': sortColumn !== 'name' || sortDirection !== 'desc' }"
        fill="currentColor" 
        viewBox="0 0 24 24"
      >
        <path d="M12 8l-6 6h12l-6-6z"/>
      </svg>
    </div>
  </div>
</th>

              <th scope="col" class="px-4 py-3 text-center">Position</th>
              <th scope="col" class="px-4 py-3 text-center">Division</th>
              <th scope="col" class="px-4 py-3 text-center">Assignment</th>
              <th scope="col" class="px-4 py-3 text-center">Status</th>
              <th scope="col" class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(employee, index) in filteredEmployees"
              :key="employee.id"
              class="bg-white border-b hover:bg-gray-50 dark:hover:bg-gray-700 transition text-black dark:hover:text-white"
            >
              <th scope="row" class="px-4 py-1 font-medium">
                {{ index + 1 }}
              </th>
              <td class="px-2 py-1 text-center uppercase">
                {{ employee.last_name }}, {{ employee.first_name }} {{ employee.middle_name || '' }}{{ employee.suffix || '' }}
              </td>
              <td class="px-2 py-1 text-center">{{ employee.position_name || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ employee.div_sec_unit || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ employee.assignment_name || 'N/A' }}</td>
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
              <td class="px-4 py-1 flex justify-center items-center gap-1">
                <Button @click="openEditModal(employee)" class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none">
                  Edit
                </Button>
                <Button @click="openViewModal(employee)" class="font-medium text-green-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none">
                  View
                </Button>
                <Button @click="openConfirmArchiveModal(employee.id)" class="font-medium text-yellow-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none">
                  Archive
                </Button>
              </td>
            </tr>
            <tr v-if="!filteredEmployees.length">
              <td colspan="7" class="text-center py-4">No employees found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>