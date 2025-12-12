<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';

// Define the ArchivedInventory interface to match the data structure from InventoryController
interface ArchivedInventory {
    id: string;
    asset_id?: string; // Optional for backward compatibility
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: number;
    condition: string;
    assigned_to: string;
    employee_id: number | null;
    status: string;
    property_no: string;
    serial_no: string;
    serviceable: string;
    unserviceable: string;
    coa_representative: string;
    coa_date: string;
    assigned_date: string;
    unit_qty: number;
    return_date: string | null;
    image: string | null;
    archived_at: string;
}

// Define breadcrumb interface
interface BreadcrumbItem {
    title: string;
    href: string;
}

// Define props
const props = defineProps<{
    archivedInventory: ArchivedInventory[] | null;
    error?: string | null;
}>();

// Access page props for flash messages
const page = usePage();

// Alert state
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

// Debug props on mount
onMounted(() => {
    console.log('Archived.vue: Props received', {
        archivedInventory: props.archivedInventory,
        error: props.error,
        flash: (page.props as any).flash,
    });
    if (props.error) {
        console.warn('Archived.vue: Error prop detected:', props.error);
        alertMessage.value = props.error;
        alertType.value = 'error';
        setTimeout(() => {
            alertMessage.value = '';
            alertType.value = '';
        }, 5000);
    }
    if (!props.archivedInventory || props.archivedInventory.length === 0) {
        console.warn('Archived.vue: No archived inventory data received');
    }
});

// Watch for flash messages
watch(
    () => (page.props as any).flash,
    (flash) => {
        console.log('Archived.vue: Flash message watch triggered:', flash);
        if (flash?.message) {
            alertMessage.value = flash.message;
            alertType.value = 'success';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        }
    },
    { immediate: true }
);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/inventory' },
    { title: 'Archived Inventory', href: '/inventory/archived' },
];

// Search input
const searchQuery = ref('');

// Computed property to filter archived inventory
const filteredArchivedInventory = computed(() => {
    console.log('Archived.vue: Computing filteredArchivedInventory', {
        archivedInventory: props.archivedInventory,
        searchQuery: searchQuery.value,
    });
    if (!props.archivedInventory || !Array.isArray(props.archivedInventory)) {
        console.warn('Archived.vue: archivedInventory is null or not an array', props.archivedInventory);
        return [];
    }
    if (!searchQuery.value.trim()) {
        return props.archivedInventory;
    }
    const query = searchQuery.value.toLowerCase().trim();
    return props.archivedInventory.filter(item => {
        return (
            (item.id?.toLowerCase().includes(query) || '') ||
            (item.asset_id?.toLowerCase().includes(query) || '') ||
            item.name.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query) ||
            item.location.toLowerCase().includes(query) ||
            item.assigned_to.toLowerCase().includes(query) ||
            item.status.toLowerCase().includes(query) ||
            item.archived_at.toLowerCase().includes(query)
        );
    });
});

// Unarchive confirmation and action
const openConfirmUnarchiveModal = (inventoryId: string) => {
    console.log('Archived.vue: Opening confirm unarchive modal for inventory:', inventoryId);
    if (confirm('Are you sure you want to unarchive this asset? This will restore it to the active inventory list.')) {
        unarchiveInventory(inventoryId);
    }
};

const unarchiveInventory = (inventoryId: string) => {
    console.log('Archived.vue: Unarchiving inventory:', inventoryId);
    router.post(route('inventory.unarchive', inventoryId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Archived.vue: Unarchive successful for inventory:', inventoryId);
            alertMessage.value = `Asset ${inventoryId} unarchived successfully`;
            alertType.value = 'success';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
        onError: (errors) => {
            console.error('Archived.vue: Unarchive failed:', errors);
            const errorMessage = Object.values(errors).join(', ') || 'Failed to unarchive asset';
            alertMessage.value = errorMessage;
            alertType.value = 'error';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
    });
};
</script>

<template>
    <Head title="Archived Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Alert -->
            <div
                v-if="alertMessage || props.error"
                :class="{
                    'bg-green-100 border-green-400 text-green-700': alertType === 'success',
                    'bg-red-100 border-red-400 text-red-700': alertType === 'error' || props.error,
                }"
                class="border-l-4 p-4 mb-4 rounded-r-md"
                role="alert"
            >
                <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
                <p>{{ alertMessage || props.error }}</p>
            </div>

            <!-- Search Input -->
            <div class="flex justify-end items-center mb-4">
  
                <div class="relative w-full max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search archived assets..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <svg
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Archived Inventory Table -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Asset ID</th>
                            <th scope="col" class="px-4 py-3 text-center">Name</th>
                            <th scope="col" class="px-4 py-3 text-center">Category</th>
                            <th scope="col" class="px-4 py-3 text-center">Location</th>
                            <th scope="col" class="px-4 py-3 text-center">Purchase Date</th>
                            <th scope="col" class="px-4 py-3 text-center">Units/Qty</th>
                            <th scope="col" class="px-4 py-3 text-center">Condition</th>
                            <th scope="col" class="px-4 py-3 text-center">Assigned To</th>
                            <th scope="col" class="px-4 py-3 text-center">Status</th>
                            <th scope="col" class="px-4 py-3 text-center">Archived At</th>
                            <th scope="col" class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in filteredArchivedInventory"
                            :key="item.id"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            <th scope="row" class="px-4 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ item.id || item.asset_id || 'N/A' }}
                            </th>
                            <td class="px-2 py-1 text-center">{{ item.name || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.category || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.location || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.purchase_date || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.unit_qty || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.condition || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.assigned_to || 'Unassigned' }}</td>
                            <td class="px-2 py-1 text-center">
                                <span
                                    :class="{
                                        'text-white bg-yellow-600': item.status === 'Good',
                                        'text-white bg-red-700': item.status === 'Check',
                                        'text-white bg-green-600': item.status === 'Repair',
                                        'text-white bg-blue-700': item.status === 'Upgrade',
                                    }"
                                    class="font-medium rounded-full text-sm px-5 py-1.5 text-center"
                                >
                                    {{ item.status || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-2 py-1 text-center">{{ item.archived_at || 'N/A' }}</td>
                            <td class="px-4 py-1 flex justify-center items-center gap-1">
                                <Button
                                    @click="openConfirmUnarchiveModal(item.id)"
                                    class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                >
                                    Unarchive
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!filteredArchivedInventory.length && !props.error">
                            <td colspan="8" class="text-center py-4">No archived assets found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>