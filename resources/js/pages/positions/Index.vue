<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PositionForm from '@/pages/employee/PositionForm.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

interface Position {
    id: number;
    item_code: string | null;
    org_code: string | null;
    name: string;
    desc: string | null;
    salary_grade: string | null;
    status: 'Vacant' | 'Occupied';
    occupied_by: {
        id: number;
        full_name: string;
        email: string;
        contact_no: string;
        employee_status: string;
        assignment: string | null;
        division: string | null;
    } | null;
}

const props = defineProps<{
    positions: Position[];
}>();

const page = usePage();
const flashMessage = computed(() => (page.props.flash as { message?: string } | undefined)?.message);
const showFlashAlert = ref(false);
let flashAlertTimer: ReturnType<typeof setTimeout> | undefined;
const searchQuery = ref('');
const statusFilter = ref('');
const positionFilter = ref('');
const officeDescriptionFilter = ref('');
const isCreateModalOpen = ref(false);
const selectedPosition = ref<Position | null>(null);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Positions', href: '/positions' }];

watch(
    () => page.props.flash,
    (flash) => {
        if (flashAlertTimer) clearTimeout(flashAlertTimer);

        const message = (flash as { message?: string } | undefined)?.message;
        showFlashAlert.value = Boolean(message);

        if (message) {
            flashAlertTimer = setTimeout(() => {
                showFlashAlert.value = false;
            }, 3000);
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (flashAlertTimer) clearTimeout(flashAlertTimer);
});

const positionOptions = computed(() =>
    [
        ...new Set(
            props.positions
                .filter((position) => position.desc === officeDescriptionFilter.value)
                .map((position) => position.name)
                .filter((value): value is string => Boolean(value?.trim())),
        ),
    ].sort((a, b) => a.localeCompare(b)),
);

const officeDescriptionOptions = computed(() =>
    [...new Set(props.positions.map((position) => position.desc).filter((value): value is string => Boolean(value?.trim())))].sort((a, b) =>
        a.localeCompare(b),
    ),
);

const filteredPositions = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return props.positions.filter((position) => {
        const matchesStatus = !statusFilter.value || position.status === statusFilter.value;
        const matchesPosition = !positionFilter.value || position.name === positionFilter.value;
        const matchesOfficeDescription = !officeDescriptionFilter.value || position.desc === officeDescriptionFilter.value;
        const matchesSearch =
            !query ||
            [
                position.item_code,
                position.org_code,
                position.name,
                position.desc,
                position.salary_grade,
                position.status,
                position.occupied_by?.full_name,
                position.occupied_by?.email,
                position.occupied_by?.assignment,
                position.occupied_by?.division,
            ]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(query));

        return matchesStatus && matchesPosition && matchesOfficeDescription && matchesSearch;
    });
});

const totalCount = computed(() => filteredPositions.value.length);
const vacantCount = computed(() => filteredPositions.value.filter((position) => position.status === 'Vacant').length);
const occupiedCount = computed(() => filteredPositions.value.filter((position) => position.status === 'Occupied').length);

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const onOfficeChange = () => {
    positionFilter.value = '';
};

const openPositionInfo = (position: Position) => {
    selectedPosition.value = position;
};

const closePositionInfo = () => {
    selectedPosition.value = null;
};
</script>

<template>
    <Head title="Positions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4">
            <Transition name="app-alert">
                <div
                    v-if="showFlashAlert && flashMessage"
                    class="rounded-r-lg border-l-4 border-green-500 bg-green-100 p-4 text-green-800"
                    role="status"
                >
                    {{ flashMessage }}
                </div>
            </Transition>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border bg-white p-4 shadow-sm dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total positions</p>
                    <p class="text-2xl font-semibold">{{ totalCount }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4 shadow-sm dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Vacant</p>
                    <p class="text-2xl font-semibold text-green-600">{{ vacantCount }}</p>
                </div>
                <div class="rounded-lg border bg-white p-4 shadow-sm dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Occupied</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ occupiedCount }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex shrink-0 items-center">
                    <Button class="bg-blue-600 text-white hover:bg-blue-700" @click="isCreateModalOpen = true"> Add Position </Button>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 lg:w-2/3 lg:grid-cols-4 xl:w-1/2">
                    <select
                        v-model="officeDescriptionFilter"
                        class="min-w-0 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                        aria-label="Filter by office"
                        @change="onOfficeChange"
                    >
                        <option value="">All Offices</option>
                        <option v-for="officeDescription in officeDescriptionOptions" :key="officeDescription" :value="officeDescription">
                            {{ officeDescription }}
                        </option>
                    </select>

                    <select
                        v-model="positionFilter"
                        :disabled="!officeDescriptionFilter"
                        :class="[
                            'min-w-0 rounded-lg border px-4 py-2 text-sm',
                            officeDescriptionFilter
                                ? 'border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white'
                                : 'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400',
                        ]"
                        aria-label="Filter by position"
                    >
                        <option value="">{{ officeDescriptionFilter ? 'All Positions' : 'Select Office First' }}</option>
                        <option v-for="positionName in positionOptions" :key="positionName" :value="positionName">
                            {{ positionName }}
                        </option>
                    </select>

                    <select
                        v-model="statusFilter"
                        class="min-w-0 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white"
                        aria-label="Filter positions by status"
                    >
                        <option value="">All Statuses</option>
                        <option value="Vacant">Vacant</option>
                        <option value="Occupied">Occupied</option>
                    </select>

                    <div class="relative min-w-0">
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search positions..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-gray-900 dark:text-white"
                        />
                        <svg
                            class="pointer-events-none absolute top-1/2 right-3 h-5 w-5 -translate-y-1/2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full bg-white text-left text-sm text-gray-900">
                        <thead class="bg-gradient-to-b from-green-300 to-green-600 text-xs text-white uppercase">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3 text-center">Item Code</th>
                                <th class="px-4 py-3 text-center">Position</th>
                                <th class="px-4 py-3 text-center">Salary Grade</th>
                                <th class="px-4 py-3 text-center">Office / Description</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Info</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr
                                v-for="(position, index) in filteredPositions"
                                :key="position.id"
                                class="border-b border-gray-200 bg-white transition hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-medium">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-center">{{ position.item_code || 'N/A' }}</td>
                                <td class="px-4 py-3 text-center font-medium text-gray-900">{{ position.name }}</td>
                                <td class="px-4 py-3 text-center">{{ position.salary_grade || 'N/A' }}</td>
                                <td class="px-4 py-3 text-center">{{ position.desc || 'N/A' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            position.status === 'Vacant'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-blue-100 text-blue-700'
                                        "
                                    >
                                        {{ position.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                        @click="openPositionInfo(position)"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!filteredPositions.length">
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    {{
                                        searchQuery || statusFilter || positionFilter || officeDescriptionFilter
                                            ? 'No positions match the selected filters.'
                                            : 'No positions found.'
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <Teleport to="body">
        <div
            v-if="isCreateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="closeCreateModal"
        >
            <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-2xl">
                <button
                    type="button"
                    class="absolute top-4 right-4 rounded-full p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Close add position dialog"
                    @click="closeCreateModal"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <h2 class="mb-5 pr-10 text-xl font-semibold text-gray-900">Add Position</h2>
                <PositionForm :offices="officeDescriptionOptions" @store-position="closeCreateModal" @close="closeCreateModal" />
            </div>
        </div>
    </Teleport>

    <Teleport to="body">
        <div
            v-if="selectedPosition"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="closePositionInfo"
        >
            <div
                class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-700 dark:bg-gray-900"
            >
                <button
                    type="button"
                    class="absolute top-4 right-4 rounded-full p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    aria-label="Close position information dialog"
                    @click="closePositionInfo"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="pr-10">
                    <p class="text-sm font-medium text-green-600">Position information</p>
                    <h2 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ selectedPosition.name }}</h2>
                </div>

                <dl class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Item Code</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.item_code || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Salary Grade</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.salary_grade || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Organization Code</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.org_code || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Status</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                :class="
                                    selectedPosition.status === 'Vacant'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300'
                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                "
                            >
                                {{ selectedPosition.status }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Office / Description</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.desc || 'No office or description provided.' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Occupied by</h3>

                    <dl v-if="selectedPosition.occupied_by" class="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Employee</dt>
                            <dd class="mt-1 font-medium text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Employee Status</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.employee_status || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Contact Number</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.contact_no || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Email</dt>
                            <dd class="mt-1 break-all text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.email || 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Assignment</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.assignment || 'N/A' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Division / Section / Unit</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ selectedPosition.occupied_by.division || 'N/A' }}</dd>
                        </div>
                    </dl>

                    <p v-else class="mt-3 text-sm text-gray-500">
                        {{
                            selectedPosition.status === 'Vacant'
                                ? 'This position is currently vacant.'
                                : 'No employee record is linked to this occupied position.'
                        }}
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
