<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Asset {
    id: number;
    name: string | null;
    category: string | null;
    location: string | null;
    purchase_date: string | null;
    return_date?: string | null;
}

interface PositionHistory {
    id: number | null;
    event: 'archived' | 'restored' | 'archived_legacy' | string;
    position_name: string | null;
    item_code: string | null;
    assignment_name: string | null;
    div_sec_unit: string | null;
    status_from: string | null;
    status_to: string | null;
    recorded_at: string | null;
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
    position_name?: string | null;
    assignment_name?: string | null;
    div_sec_unit?: string | null;
    date_of_birth?: string | null;
    tin_no?: string | null;
    date_appointment?: string | null;
    date_last_promotion?: string | null;
    civil_service?: string | null;
    education?: string | null;
    assets?: Asset[] | null;
    position_history?: PositionHistory[] | null;
    archived_at?: string | null;
}

const props = defineProps<{
    employee: Employee | null;
    error?: string | null;
}>();

const isCurrentPosition = (history: PositionHistory) => {
    if (!props.employee || props.employee.archived_at || !props.employee.position_history) return false;

    const currentHistory = props.employee.position_history.find(
        (entry) => ['restored', 'restored_reassigned'].includes(entry.event) && entry.position_name === props.employee?.position_name,
    );

    return currentHistory?.id === history.id;
};

const displayedPositionHistory = computed(() => {
    const histories = props.employee?.position_history ?? [];
    const seenFormerPositions = new Set<string>();

    return histories.filter((history) => {
        const positionKey =
            history.item_code?.trim().toLowerCase() ||
            [history.position_name, history.assignment_name, history.div_sec_unit].map((value) => value?.trim().toLowerCase() ?? '').join('|');

        if (['restored', 'restored_reassigned'].includes(history.event)) {
            // Only an active employee's latest restoration is current. Keep
            // the separate archived event even when it uses the same position.
            return !props.employee?.archived_at && isCurrentPosition(history);
        }

        if (history.event === 'archived' || history.event === 'archived_legacy') {
            if (seenFormerPositions.has(positionKey)) return false;
            seenFormerPositions.add(positionKey);
        }

        return true;
    });
});

const historyEventLabel = (history: PositionHistory) => {
    if (history.event === 'archived' || history.event === 'archived_legacy') return 'Former Position';
    if (['restored', 'restored_reassigned'].includes(history.event)) {
        return isCurrentPosition(history) ? 'Current Position' : 'Previous Position';
    }
    return history.event.replaceAll('_', ' ');
};
</script>

<template>
    <Head :title="employee ? `Employee #${employee.id}` : 'Employee Not Found'" />

    <div class="relative">
        <!-- Full-screen modal-like container (same as Create.vue) -->

        <!-- Main Content -->
        <div class="p-4 pt-0">
            <h2 class="mb-6 text-2xl font-semibold text-gray-900">Employee Details</h2>

            <!-- Error Message -->
            <div v-if="props.error" class="mb-6 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="ml-2 block sm:inline">{{ props.error }}</span>
            </div>

            <!-- Employee Details -->
            <div v-else-if="props.employee" class="rounded-lg bg-white p-8 shadow-md">
                <div class="grid grid-cols-1 gap-8 text-black md:grid-cols-3">
                    <!-- Column 1 -->
                    <div class="space-y-2 md:space-y-5">
                        <p>
                            <strong class="text-gray-700">Employee ID:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.id }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Full Name:</strong>
                            <span class="ml-2 text-sm md:text-base"
                                >{{ props.employee.first_name }} {{ props.employee.middle_name || '' }} {{ props.employee.last_name }}
                                {{ props.employee.suffix || '' }}</span
                            >
                        </p>
                        <p>
                            <strong class="text-gray-700">Sex:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.sex || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Email:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.email || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Date of Birth:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.date_of_birth || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">TIN No:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.tin_no || 'N/A' }}</span>
                        </p>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-2 md:space-y-5">
                        <p>
                            <strong class="text-gray-700">Status:</strong>
                            <span
                                class="ml-2 inline-block rounded-full px-3 py-1 text-sm font-medium"
                                :class="{
                                    'bg-green-600 text-white': props.employee.emp_status === 'Active',
                                    'bg-red-700 text-white': props.employee.emp_status === 'Inactive',
                                    'bg-yellow-600 text-white': props.employee.emp_status === 'On Leave',
                                    'bg-gray-500 text-white': !['Active', 'Inactive', 'On Leave'].includes(props.employee.emp_status),
                                }"
                            >
                                {{ props.employee.emp_status || 'N/A' }}
                            </span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Position:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.position_name || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Assignment:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.assignment_name || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Division/Unit:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.div_sec_unit || 'N/A' }}</span>
                        </p>
                    </div>

                    <!-- Column 3 -->
                    <div class="space-y-2 md:space-y-5">
                        <p>
                            <strong class="text-gray-700">Date of Appointment:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.date_appointment || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Date of Last Promotion:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.date_last_promotion || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Civil Service:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.civil_service || 'N/A' }}</span>
                        </p>
                        <p>
                            <strong class="text-gray-700">Education:</strong>
                            <span class="ml-2 text-sm md:text-base">{{ props.employee.education || 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-200 pt-6 text-black">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Position Assignment History</h3>

                    <div v-if="displayedPositionHistory.length" class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                                <tr>
                                    <th class="px-4 py-3">Event</th>
                                    <th class="px-4 py-3">Position / Item Code</th>
                                    <th class="px-4 py-3">Assignment / Division</th>
                                    <th class="px-4 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="history in displayedPositionHistory"
                                    :key="history.id ?? `${history.event}-${history.recorded_at}`"
                                    class="border-t transition-colors"
                                    :class="{ 'bg-green-100 ring-1 ring-green-400 ring-inset': isCurrentPosition(history) }"
                                >
                                    <td class="px-4 py-3 font-medium capitalize">
                                        {{ historyEventLabel(history) }}
                                    </td>
                                    <td class="px-4 py-3" :class="{ 'font-semibold text-green-800': isCurrentPosition(history) }">
                                        <div>{{ history.position_name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ history.item_code || 'No item code recorded' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>{{ history.assignment_name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ history.div_sec_unit || 'N/A' }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ history.recorded_at || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">No position history has been recorded yet.</p>
                </div>
            </div>

            <!-- No Employee Found -->
            <div v-else class="rounded border border-red-400 bg-red-100 px-6 py-4 text-red-700" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="ml-2 block sm:inline">Employee not found or failed to load.</span>
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
