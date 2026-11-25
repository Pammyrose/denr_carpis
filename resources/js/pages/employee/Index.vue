<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import ViewEmployee from './View.vue';

interface EmployeeImportStatus {
    id: number;
    filename: string;
    status: 'queued' | 'processing' | 'completed' | 'failed';
    total_rows: number;
    processed_rows: number;
    created_count: number;
    updated_count: number;
    failed_count: number;
    percentage: number;
    errors: string[];
}

const isUploadingExcel = ref(false);
const uploadPercentage = ref(0);
const employeeImport = ref<EmployeeImportStatus | null>(null);
let importPollTimer: ReturnType<typeof setTimeout> | null = null;

const stopImportPolling = () => {
    if (importPollTimer) {
        clearTimeout(importPollTimer);
        importPollTimer = null;
    }
};

const pollEmployeeImport = async (importId: number) => {
    try {
        const response = await axios.post(route('employee.import.process', { employeeImport: importId }));
        employeeImport.value = response.data.import;

        if (employeeImport.value?.status === 'completed') {
            stopImportPolling();
            localStorage.removeItem('employeeImportId');
            const result = employeeImport.value;
            const completionMessage = `Import completed: ${result.created_count} created, ${result.updated_count} updated, ${result.failed_count} failed.`;
            alertMessage.value = completionMessage;
            alertType.value = result.failed_count > 0 ? 'error' : 'success';
            setTimeout(() => {
                if (alertMessage.value === completionMessage) {
                    alertMessage.value = '';
                    alertType.value = '';
                }
            }, 5000);
            router.reload({ only: ['employees', 'dropdowns'] });
        } else if (employeeImport.value?.status === 'failed') {
            stopImportPolling();
            localStorage.removeItem('employeeImportId');
            alertMessage.value = employeeImport.value.errors[0] || 'The Excel import failed.';
            alertType.value = 'error';
        } else {
            importPollTimer = setTimeout(() => void pollEmployeeImport(importId), 250);
        }
    } catch (error: any) {
        if (error.response?.data?.import) {
            employeeImport.value = error.response.data.import;
        }
        if (error.response?.status === 404) {
            stopImportPolling();
            localStorage.removeItem('employeeImportId');
        } else if (employeeImport.value?.status === 'failed') {
            stopImportPolling();
            localStorage.removeItem('employeeImportId');
            alertMessage.value = employeeImport.value.errors[0] || 'The Excel import failed.';
            alertType.value = 'error';
        } else {
            importPollTimer = setTimeout(() => void pollEmployeeImport(importId), 1500);
        }
    }
};

const startImportPolling = (importId: number) => {
    stopImportPolling();
    localStorage.setItem('employeeImportId', String(importId));
    void pollEmployeeImport(importId);
};

const handleExcelUpload = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    const formData = new FormData();
    formData.append('excel_file', input.files[0]);
    isUploadingExcel.value = true;
    uploadPercentage.value = 0;

    try {
        const response = await axios.post(route('employee.import.excel'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadPercentage.value = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                }
            },
        });

        employeeImport.value = response.data.import;
        alertMessage.value = 'Excel uploaded. Importing employees now; keep this page open until it completes.';
        alertType.value = 'success';
        startImportPolling(response.data.import.id);
    } catch (error: any) {
        const validationErrors = error.response?.data?.errors;
        alertMessage.value = validationErrors
            ? Object.values(validationErrors).flat().join(', ')
            : error.response?.data?.message || 'Excel upload failed.';
        alertType.value = 'error';
    } finally {
        isUploadingExcel.value = false;
        input.value = '';
    }
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
    positions: Array<{ value: string; label: string; salary_grade: string | null; item_code: string; org_code: string; status: string }>;
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
    divisionOptionsByAssignment?: Record<string, string[]>;
    positionOptionsByAssignment?: Record<string, string[]>;
    positionOptionsByAssignmentAndDivision?: Record<string, Record<string, string[]>>;
    selectedDivision?: string | null;
    selectedAssignment?: string | null;
    selectedPosition?: string | null;
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

    const activeImportId = Number(localStorage.getItem('employeeImportId'));
    if (activeImportId > 0) {
        startImportPolling(activeImportId);
    }
});

onUnmounted(stopImportPolling);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Employee', href: '/employee' }];

const searchQuery = ref('');
const assignmentFilter = ref(props.selectedAssignment ?? '');
const divisionFilter = ref(props.selectedDivision ?? '');
const positionFilter = ref(props.selectedPosition ?? '');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedEmployee = ref<Employee | null>(null);
const viewedEmployee = ref<Employee | null>(null);

// ADDED: Sorting state for Name column
const sortColumn = ref<'name' | ''>('');
const sortDirection = ref<'asc' | 'desc' | ''>('');

// Unique sorted assignments from dropdowns (all possible assignment places)
const uniqueSortedAssignment = computed(() => {
    if (!props.dropdowns?.assignmentPlaces) return [];

    return props.dropdowns.assignmentPlaces.map((place) => place.value).sort((a, b) => a.localeCompare(b));
});

const uniqueSortedPositions = computed(() => {
    if (!props.dropdowns?.positions) return [];

    let positions: string[];

    if (assignmentFilter.value && divisionFilter.value) {
        positions = props.positionOptionsByAssignmentAndDivision?.[assignmentFilter.value]?.[divisionFilter.value] ?? [];
    } else if (assignmentFilter.value) {
        positions = props.positionOptionsByAssignment?.[assignmentFilter.value] ?? [];
    } else {
        positions = props.dropdowns.positions.map((position) => position.value);
    }

    return [...new Set(positions.filter((value): value is string => Boolean(value)))].sort((a, b) => a.localeCompare(b));
});

const uniqueSortedDivisions = computed(() => {
    if (assignmentFilter.value) {
        const divisionsForAssignment = props.divisionOptionsByAssignment?.[assignmentFilter.value] ?? [];
        return [...new Set(divisionsForAssignment)].filter((value): value is string => Boolean(value)).sort((a, b) => a.localeCompare(b));
    }

    return [];
});

const onAssignmentChange = () => {
    // Always reset division when assignment changes to avoid stale cross-assignment selections.
    divisionFilter.value = '';
    positionFilter.value = '';

    applyCombinedFilters();
};

const onDivisionChange = () => {
    // A position from another division must not remain active.
    positionFilter.value = '';

    applyCombinedFilters();
};

// Combined filter application
const applyCombinedFilters = () => {
    const params: Record<string, string> = {};

    if (divisionFilter.value) {
        params.div_sec_unit = divisionFilter.value;
    }
    if (assignmentFilter.value) {
        params.assignment_name = assignmentFilter.value;
    }
    if (positionFilter.value) {
        params.position_name = positionFilter.value;
    }

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

    if (positionFilter.value) {
        list = list.filter((employee) => employee.position_name === positionFilter.value);
    }

    // Apply search filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        list = list.filter((employee) => {
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

const excelDownloadUrl = computed(() => {
    const params: Record<string, string> = {};

    if (assignmentFilter.value) {
        params.assignment_name = assignmentFilter.value;
    }
    if (divisionFilter.value) {
        params.div_sec_unit = divisionFilter.value;
    }
    if (positionFilter.value) {
        params.position_name = positionFilter.value;
    }
    if (searchQuery.value.trim()) {
        params.search = searchQuery.value.trim();
    }
    if (sortColumn.value === 'name' && sortDirection.value) {
        params.sort_direction = sortDirection.value;
    }

    return route('employee.download.excel.template', params);
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
    if (confirm('Are you sure you want to archive this employee? You can restore the employee later from Archived Employees.')) {
        archiveEmployee(employeeId);
    }
};

const archiveEmployee = (employeeId: number) => {
    console.log('Archiving employee:', employeeId);
    router.post(
        route('employee.store'),
        { archive_employee_id: employeeId },
        {
            onSuccess: () => {
                console.log('Archive successful');
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
        },
    );
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
    { immediate: true },
);
</script>

<template>
    <Head title="Employee" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Alert -->
            <Transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-700"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="alertMessage"
                    :class="{
                        'border-green-400 bg-green-100 text-green-700': alertType === 'success',
                        'border-red-400 bg-red-100 text-red-700': alertType === 'error',
                    }"
                    class="mb-4 rounded-r-md border-l-4 p-4"
                    role="alert"
                >
                    <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
                    <p>{{ alertMessage }}</p>
                </div>
            </Transition>

            <div
                v-if="employeeImport && ['queued', 'processing'].includes(employeeImport.status)"
                class="mb-4 rounded-md border border-blue-200 bg-blue-50 p-4 text-blue-900"
            >
                <div class="mb-2 flex items-center justify-between gap-4 text-sm font-medium">
                    <span>
                        {{ employeeImport.status === 'queued' ? 'Preparing import…' : `Importing ${employeeImport.filename}` }}
                    </span>
                    <span>{{ employeeImport.processed_rows }} / {{ employeeImport.total_rows }} rows</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-blue-100">
                    <div class="h-full bg-blue-600 transition-all duration-300" :style="{ width: `${employeeImport.percentage}%` }"></div>
                </div>
                <p class="mt-2 text-xs">
                    {{ employeeImport.created_count }} created · {{ employeeImport.updated_count }} updated · {{ employeeImport.failed_count }} failed
                </p>
            </div>

            <details
                v-if="employeeImport && employeeImport.failed_count > 0 && employeeImport.errors.length"
                class="mb-4 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"
            >
                <summary class="cursor-pointer font-medium">View failed Excel rows</summary>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li v-for="error in employeeImport.errors" :key="error">{{ error }}</li>
                </ul>
            </details>

            <!-- Search Input + Division Filter + Create Button -->
            <div class="mb-4 flex flex-col items-start justify-between gap-4 lg:flex-row lg:items-center">
                <!-- Left: Action Buttons -->
                <div class="flex gap-2">
                    <Button @click="openCreateModal" class="bg-blue-600 text-white hover:bg-blue-700"> Create </Button>
                    <Link :href="route('employee.archived')" class="rounded-md bg-gray-200 px-4 py-2 text-white">
                        <svg
                            class="h-4 w-4 text-gray-800 dark:text-black"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            width="36"
                            height="36"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M20 10H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8ZM9 13v-1h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1Z"
                                clip-rule="evenodd"
                            />
                            <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Z" />
                        </svg>
                    </Link>

                    <!-- Excel Upload Button -->
                    <label
                        :class="{
                            'pointer-events-none cursor-not-allowed opacity-60':
                                isUploadingExcel || (employeeImport && ['queued', 'processing'].includes(employeeImport.status)),
                        }"
                        class="flex cursor-pointer items-center gap-1 rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                        title="Upload Excel file"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"
                            />
                        </svg>
                        <input
                            ref="excelFileInput"
                            type="file"
                            accept=".xlsx,.xls"
                            class="hidden"
                            :disabled="isUploadingExcel || (!!employeeImport && ['queued', 'processing'].includes(employeeImport.status))"
                            @change="handleExcelUpload"
                        />
                        <span>{{ isUploadingExcel ? `Uploading ${uploadPercentage}%` : 'Upload Excel' }}</span>
                    </label>

                    <a
                        :href="excelDownloadUrl"
                        class="flex items-center gap-1 rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                        title="Download displayed employees"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
                            />
                        </svg>
                    </a>
                </div>

                <!-- Right: Assignment Filter + Division Filter + Search -->
                <div class="grid w-full grid-cols-1 gap-3 lg:w-1/2 lg:grid-cols-4">
                    <select
                        v-model="assignmentFilter"
                        @change="onAssignmentChange"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:text-white"
                    >
                        <option value="" class="dark:text-black">All Offices</option>
                        <option v-for="assignment in uniqueSortedAssignment" :key="assignment" :value="assignment" class="text-black dark:text-black">
                            {{ assignment }}
                        </option>
                    </select>
                    <select
                        v-model="divisionFilter"
                        @change="onDivisionChange"
                        :disabled="!assignmentFilter"
                        :class="[
                            'rounded-lg border px-2 py-2 text-sm transition-colors',
                            assignmentFilter
                                ? 'border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:text-white'
                                : 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-500',
                        ]"
                    >
                        <option value="" class="dark:text-black">
                            {{ assignmentFilter ? 'All Divisions' : 'Select Assignment First' }}
                        </option>
                        <option v-for="division in uniqueSortedDivisions" :key="division" :value="division" class="text-black dark:text-black">
                            {{ division }}
                        </option>
                    </select>
                    <select
                        v-model="positionFilter"
                        @change="applyCombinedFilters"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:text-white"
                    >
                        <option value="" class="dark:text-black">All Positions</option>
                        <option v-for="position in uniqueSortedPositions" :key="position" :value="position" class="text-black dark:text-black">
                            {{ position }}
                        </option>
                    </select>

                    <div class="relative w-full">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"
                        />
                        <svg
                            class="absolute top-1/2 right-3 h-5 w-5 -translate-y-1/2 transform text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Modals (unchanged) -->
            <div
                v-if="isCreateModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-xs"
                @click.self="closeCreateModal"
            >
                <div class="relative w-full max-w-6xl rounded-xl border border-gray-300 bg-white p-6 shadow-lg">
                    <button
                        @click="closeCreateModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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

            <div
                v-if="isEditModalOpen && selectedEmployee"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-xs"
                @click.self="closeEditModal"
            >
                <div class="relative w-full max-w-6xl rounded-xl border border-gray-300 bg-white p-6 shadow-lg">
                    <button
                        @click="closeEditModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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

            <div
                v-if="isViewModalOpen && viewedEmployee"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-xs"
                @click.self="closeViewModal"
            >
                <div class="relative w-full max-w-6xl rounded-xl border border-gray-300 bg-white p-6 shadow-lg">
                    <button
                        @click="closeViewModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <ViewEmployee :employee="viewedEmployee" />
                </div>
            </div>

            <!-- Employee Table -->
            <div class="overflow-hidden overflow-x-auto rounded-lg bg-white shadow-xl">
                <table class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                    <thead class="bg-gradient-to-b from-green-300 to-green-600 text-xs text-white uppercase">
                        <tr>
                            <th scope="col" class="px-4 py-3">No</th>

                            <!-- SORTABLE NAME COLUMN -->
                            <th
                                scope="col"
                                class="cursor-pointer px-4 py-2 text-center select-none"
                                @click="
                                    () => {
                                        if (sortColumn === 'name') {
                                            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                                        } else {
                                            sortColumn = 'name';
                                            sortDirection = 'asc';
                                        }
                                    }
                                "
                            >
                                <div class="flex items-center justify-center gap-2">
                                    Name
                                    <div class="flex flex-col">
                                        <svg
                                            class="h-3 w-3 text-white transition-opacity"
                                            :class="{
                                                'opacity-100': sortColumn === 'name' && sortDirection === 'asc',
                                                'opacity-30': sortColumn !== 'name' || sortDirection !== 'asc',
                                            }"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M12 8l-6 6h12l-6-6z" />
                                        </svg>
                                        <svg
                                            class="-mt-1 h-3 w-3 rotate-180 text-white transition-opacity"
                                            :class="{
                                                'opacity-100': sortColumn === 'name' && sortDirection === 'desc',
                                                'opacity-30': sortColumn !== 'name' || sortDirection !== 'desc',
                                            }"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M12 8l-6 6h12l-6-6z" />
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
                            class="border-b bg-white text-black transition hover:bg-gray-50 dark:hover:bg-gray-700 dark:hover:text-white"
                        >
                            <th scope="row" class="px-4 py-1 font-medium">
                                {{ index + 1 }}
                            </th>
                            <td class="px-2 py-1 text-center uppercase">
                                {{ employee.last_name }}, {{ employee.first_name }} {{ employee.middle_name || '' }} {{ employee.suffix || '' }}
                            </td>
                            <td class="px-2 py-1 text-center">{{ employee.position_name || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ employee.div_sec_unit || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ employee.assignment_name || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">
                                <span
                                    :class="{
                                        'bg-green-600 text-white hover:bg-green-800': employee.emp_status === 'Regular',
                                        'bg-red-700 text-white hover:bg-red-800': employee.emp_status === 'Retired',
                                    }"
                                    class="me-2 mb-2 rounded-full px-5 py-1.5 text-center text-sm font-medium"
                                >
                                    {{ employee.emp_status || 'N/A' }}
                                </span>
                            </td>
                            <td class="flex items-center justify-center gap-1 px-4 py-1">
                                <Button
                                    @click="openViewModal(employee)"
                                    class="border-0 bg-transparent font-medium text-green-600 shadow-none hover:bg-transparent hover:underline"
                                >
                                    View
                                </Button>
                                <Button
                                    @click="openEditModal(employee)"
                                    class="border-0 bg-transparent font-medium text-blue-600 shadow-none hover:bg-transparent hover:underline"
                                >
                                    Edit
                                </Button>
                                <Button
                                    @click="openConfirmArchiveModal(employee.id)"
                                    class="border-0 bg-transparent font-medium text-yellow-600 shadow-none hover:bg-transparent hover:underline"
                                >
                                    Archive
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!filteredEmployees.length">
                            <td colspan="7" class="py-4 text-center">No employees found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
