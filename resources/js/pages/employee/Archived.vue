<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import ViewEmployee from './View.vue';

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
    position_record_id?: number | null;
    position_item_code?: string | null;
    assignment_id?: string;
    div_sec_unit?: string;
    archived_at: string;
    date_of_birth?: string | null;
    tin_no?: string | null;
    date_appointment?: string | null;
    date_last_promotion?: string | null;
    civil_service?: string | null;
    education?: string | null;
    position_history?: Array<{
        id: number | null;
        event: string;
        position_name: string | null;
        item_code: string | null;
        assignment_name: string | null;
        div_sec_unit: string | null;
        status_from: string | null;
        status_to: string | null;
        recorded_at: string | null;
    }>;
}

interface VacantPosition {
    id: number;
    item_code: string | null;
    name: string;
    description: string | null;
    salary_grade: string | null;
}

interface BreadcrumbItem {
    title: string;
    href: string;
}

const props = defineProps<{
    archivedEmployees: ArchivedEmployee[] | null;
    vacantPositions: VacantPosition[];
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

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Archived Employees', href: '/employee/archived' }];

const searchQuery = ref('');
const selectedArchivedEmployee = ref<ArchivedEmployee | null>(null);
const isInfoModalOpen = ref(false);
const employeeToUnarchive = ref<ArchivedEmployee | null>(null);
const isUnarchiveModalOpen = ref(false);
const selectedPositionRecordId = ref('');
const isUnarchiving = ref(false);

const archivedEmployeeInfo = computed(() => {
    if (!selectedArchivedEmployee.value) return null;

    return {
        ...selectedArchivedEmployee.value,
        position_name: selectedArchivedEmployee.value.position_id ?? null,
        assignment_name: selectedArchivedEmployee.value.assignment_id ?? null,
    };
});

const formerPositionAvailable = computed(() => {
    if (!employeeToUnarchive.value) return false;

    return props.vacantPositions.some((position) =>
        employeeToUnarchive.value?.position_record_id
            ? position.id === employeeToUnarchive.value.position_record_id
            : position.name === employeeToUnarchive.value?.position_id,
    );
});

const openInfoModal = (employee: ArchivedEmployee) => {
    selectedArchivedEmployee.value = employee;
    isInfoModalOpen.value = true;
};

const closeInfoModal = () => {
    isInfoModalOpen.value = false;
    selectedArchivedEmployee.value = null;
};

const filteredArchivedEmployees = computed(() => {
    if (!props.archivedEmployees || !Array.isArray(props.archivedEmployees)) {
        console.warn('Archived.vue: No archived employees or invalid prop', props.archivedEmployees);
        return [];
    }
    if (!searchQuery.value.trim()) {
        return props.archivedEmployees;
    }
    const query = searchQuery.value.toLowerCase().trim();
    return props.archivedEmployees.filter((employee) => {
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

const openUnarchiveModal = (employee: ArchivedEmployee) => {
    employeeToUnarchive.value = employee;
    const formerPosition = props.vacantPositions.find((position) =>
        employee.position_record_id ? position.id === employee.position_record_id : position.name === employee.position_id,
    );
    selectedPositionRecordId.value = formerPosition ? String(formerPosition.id) : '';
    isUnarchiveModalOpen.value = true;
};

const closeUnarchiveModal = () => {
    if (isUnarchiving.value) return;
    isUnarchiveModalOpen.value = false;
    employeeToUnarchive.value = null;
    selectedPositionRecordId.value = '';
};

const unarchiveEmployee = () => {
    if (!employeeToUnarchive.value || !selectedPositionRecordId.value) {
        alertMessage.value = 'Please select a vacant position before restoring the employee.';
        alertType.value = 'error';
        return;
    }

    isUnarchiving.value = true;
    router.post(
        route('employee.unarchive', employeeToUnarchive.value.id),
        { position_record_id: Number(selectedPositionRecordId.value) },
        {
            onSuccess: () => {
                isUnarchiveModalOpen.value = false;
                employeeToUnarchive.value = null;
                selectedPositionRecordId.value = '';
                alertMessage.value = 'Employee unarchived successfully';
                alertType.value = 'success';
                setTimeout(() => {
                    alertMessage.value = '';
                    alertType.value = '';
                }, 3000);
            },
            onError: (errors) => {
                alertMessage.value = 'Failed to unarchive employee: ' + Object.values(errors).join(', ');
                alertType.value = 'error';
                setTimeout(() => {
                    alertMessage.value = '';
                    alertType.value = '';
                }, 3000);
            },
            onFinish: () => {
                isUnarchiving.value = false;
            },
        },
    );
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
    { immediate: true },
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
                    'border-green-400 bg-green-100 text-green-700': alertType === 'success',
                    'border-red-400 bg-red-100 text-red-700': alertType === 'error',
                }"
                class="mb-4 rounded-r-md border-l-4 p-4"
                role="alert"
            >
                <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
                <p>{{ alertMessage }}</p>
            </div>

            <div class="mb-4 flex items-center justify-between gap-4">
                <!-- Back Button -->
                <div>
                    <Link
                        :href="route('employee.index')"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                </div>

                <!-- Search Input -->
                <div class="relative w-full max-w-md">
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
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div
                v-if="isInfoModalOpen && archivedEmployeeInfo"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-xs"
                @click.self="closeInfoModal"
            >
                <div class="relative max-h-[90vh] w-full max-w-6xl overflow-y-auto rounded-xl border border-gray-300 bg-white p-6 shadow-lg">
                    <button
                        type="button"
                        class="absolute top-3 right-3 z-10 text-gray-600 hover:text-gray-900"
                        aria-label="Close employee info"
                        @click="closeInfoModal"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <ViewEmployee :employee="archivedEmployeeInfo" />
                </div>
            </div>

            <div
                v-if="isUnarchiveModalOpen && employeeToUnarchive"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-xs"
                @click.self="closeUnarchiveModal"
            >
                <div class="w-full max-w-xl rounded-xl bg-white p-6 text-gray-900 shadow-xl">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold">Restore Employee</h2>
                            <p class="mt-1 text-sm text-gray-600">{{ employeeToUnarchive.last_name }}, {{ employeeToUnarchive.first_name }}</p>
                        </div>
                        <button
                            type="button"
                            class="text-gray-500 hover:text-gray-900"
                            aria-label="Close restore dialog"
                            @click="closeUnarchiveModal"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                        <p><span class="font-medium">Former position:</span> {{ employeeToUnarchive.position_id || 'N/A' }}</p>
                        <p v-if="employeeToUnarchive.position_item_code" class="mt-1 text-gray-600">
                            Item code: {{ employeeToUnarchive.position_item_code }}
                        </p>
                    </div>

                    <div v-if="!formerPositionAvailable" class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
                        The former position is not vacant. Select another vacant position to restore this employee.
                    </div>

                    <label for="restore_position" class="mb-2 block text-sm font-medium">Vacant position</label>
                    <select
                        id="restore_position"
                        v-model="selectedPositionRecordId"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :disabled="isUnarchiving || !props.vacantPositions.length"
                    >
                        <option value="" disabled>Select a vacant position</option>
                        <option v-for="position in props.vacantPositions" :key="position.id" :value="String(position.id)">
                            {{ position.item_code || 'No item code' }} - {{ position.name
                            }}{{ position.description ? ` (${position.description})` : '' }}
                        </option>
                    </select>

                    <p v-if="!props.vacantPositions.length" class="mt-2 text-sm text-red-600">
                        No vacant positions are currently available in your office scope.
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <Button
                            type="button"
                            class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                            :disabled="isUnarchiving"
                            @click="closeUnarchiveModal"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            class="bg-blue-600 text-white hover:bg-blue-700"
                            :disabled="isUnarchiving || !selectedPositionRecordId"
                            @click="unarchiveEmployee"
                        >
                            {{ isUnarchiving ? 'Restoring...' : 'Restore Employee' }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Archived Employees Table -->
            <div class="relative mt-4 overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gradient-to-b from-green-300 to-green-600 text-xs text-white uppercase">
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
                            class="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-white dark:text-black dark:hover:bg-gray-600 dark:hover:text-white"
                        >
                            <th scope="row" class="px-4 py-1 font-medium">
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
                                        'bg-green-600 text-white hover:bg-green-800': employee.emp_status === 'Regular',
                                        'bg-red-700 text-white hover:bg-red-800': employee.emp_status === 'Retired',
                                    }"
                                    class="me-2 mb-2 rounded-full px-5 py-1.5 text-center text-sm font-medium"
                                >
                                    {{ employee.emp_status || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-2 py-1 text-center">{{ employee.archived_at || 'N/A' }}</td>
                            <td class="flex items-center justify-center gap-1 px-4 py-1">
                                <Button
                                    @click="openInfoModal(employee)"
                                    class="border-0 bg-transparent font-medium text-green-600 shadow-none hover:bg-transparent hover:underline"
                                >
                                    View
                                </Button>
                                <Button
                                    @click="openUnarchiveModal(employee)"
                                    class="border-0 bg-transparent font-medium text-blue-600 shadow-none hover:bg-transparent hover:underline"
                                >
                                    Unarchive
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!filteredArchivedEmployees.length">
                            <td colspan="8" class="py-4 text-center">No archived employees found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
