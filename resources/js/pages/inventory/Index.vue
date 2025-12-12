<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage, useForm, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Define the Inventory interface
interface InventoryItem {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    unit_qty: string;
    condition: string;
    assigned_to: string;
    employee_id: number | null;
    return_date: string;
    status: string;
}

// Define the Employee interface
interface Employee {
    id: number;
    full_name: string;
}

// Define props for inventory items, employees, and error
interface Props {
    assets: InventoryItem[];
    employees: Employee[];
    error?: string | null;
}

const props = defineProps<Props>();

const page = usePage();

// Alert state
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

// Log props for debugging
onMounted(() => {
    console.log('Index.vue Props:', { assets: props.assets, employees: props.employees, error: props.error });
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

// Watch for flash messages
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: '/inventory',
    },
];

// Search input
const searchQuery = ref('');

// Computed property to filter assets based on search query
const filteredAssets = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.assets;
    }
    const query = searchQuery.value.toLowerCase().trim();
    return props.assets.filter(item =>
        item.name.toLowerCase().includes(query) ||
        item.category.toLowerCase().includes(query) ||
        item.location.toLowerCase().includes(query) ||
        item.assigned_to.toLowerCase().includes(query)
    );
});

// Modal visibility for Create
const isCreateModalOpen = ref(false);
// Modal visibility for Edit
const isEditModalOpen = ref(false);
// Modal visibility for Archive Confirmation
const isConfirmArchiveModalOpen = ref(false);
const itemToArchive = ref<string | null>(null);

// Modal controls for Archive Confirmation
const openConfirmArchiveModal = (id: string) => {
    itemToArchive.value = id;
    isConfirmArchiveModalOpen.value = true;
};

const closeConfirmArchiveModal = () => {
    isConfirmArchiveModalOpen.value = false;
    itemToArchive.value = null;
};

// Modal controls for Create
const openCreateModal = () => {
    console.log('Opening create modal');
    isCreateModalOpen.value = true;
    console.log('isCreateModalOpen:', isCreateModalOpen.value);
};

const closeCreateModal = () => {
    console.log('Closing create modal');
    isCreateModalOpen.value = false;
};

// Modal controls for Edit
const openEditModal = (item: InventoryItem) => {
    selectedItem.value = {
        ...item,
        assigned_to: item.employee_id,
    };
    console.log('Opening edit modal with item:', selectedItem.value);
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedItem.value = null;
};

// Selected inventory item for editing
const selectedItem = ref<InventoryItem | null>(null);

const archiveItem = (item: InventoryItem | undefined) => {
    if (!item) {
        console.error('No item provided for archiving');
        alertMessage.value = 'Error: No item selected for archiving';
        alertType.value = 'error';
        setTimeout(() => {
            alertMessage.value = '';
            alertType.value = '';
        }, 3000);
        return;
    }
    const form = useForm({
        archive_inventory_id: item.id,
    });
    console.log('Attempting to archive asset ID:', item.id);
    form.post(route('inventory.store'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Asset archived successfully');
            alertMessage.value = 'Asset archived successfully';
            alertType.value = 'success';
            closeConfirmArchiveModal();
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
        onError: (errors) => {
            console.error('Error archiving asset:', errors);
            const errorMessage = Object.values(errors).join(', ') || 'Failed to archive asset';
            alertMessage.value = errorMessage;
            alertType.value = 'error';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
    });
};

// Close create modal after navigation
onMounted(() => {
    router.on('finish', () => {
        console.log('Navigation finished, closing create modal');
        isCreateModalOpen.value = false;
    });
});
</script>

<template>
    <Head title="Inventory" />

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

            <!-- Search Input and Buttons -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-2">
                    <Button
                        @click="openCreateModal"
                        class="bg-blue-600 text-white hover:bg-blue-700"
                    >
                        Create
                    </Button>
                    <Link
    :href="route('inventory.archived')"
    class="bg-gray-200 text-white px-4 py-2 rounded-md"
>
     <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd" d="M20 10H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8ZM9 13v-1h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
  <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Z"/>
</svg>
</Link>
                </div>
                <div class="relative w-full max-w-md ml-5">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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

            <!-- Create Modal -->
            <div
                v-if="isCreateModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
                @click.self="closeCreateModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
                    <button
                        @click="closeCreateModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <Create :closeModal="closeCreateModal" :employees="props.employees" />
                </div>
            </div>

            <!-- Edit Modal -->
            <div
                v-if="isEditModalOpen && selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs"
                @click.self="closeEditModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
                    <button
                        @click="closeEditModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <Edit :item="selectedItem" :employees="props.employees" :closeModal="closeEditModal" />
                </div>
            </div>

            <!-- Confirmation Archive Modal -->
            <div
                v-if="isConfirmArchiveModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
                @click.self="closeConfirmArchiveModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative border border-gray-300">
                    <button
                        @click="closeConfirmArchiveModal"
                        class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <h2 class="text-lg font-semibold">Confirm Archiving</h2>
                    <p class="mt-2">Are you sure you want to archive this asset? This action cannot be undone without unarchiving.</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            @click="closeConfirmArchiveModal"
                            class="bg-gray-300 text-gray-800 hover:bg-gray-400"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="archiveItem(props.assets.find(item => item.id === itemToArchive))"
                            class="bg-yellow-600 text-white hover:bg-yellow-700"
                        >
                            Archive
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Inventory table -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Asset ID</th>
                            <th scope="col" class="px-4 py-3 text-center">Name</th>
                            <th scope="col" class="px-4 py-3 text-center">Category</th>
                            <th scope="col" class="px-4 py-3 text-center">Location</th>
                            <th scope="col" class="px-4 py-3 text-center">Purchase Date</th>
                            <th scope="col" class="px-4 py-3 text-center">Units / Qty</th>
                            <th scope="col" class="px-4 py-3 text-center">Condition</th>
                            <th scope="col" class="px-4 py-3 text-center">Assigned To</th>
                            <th scope="col" class="px-4 py-3 text-center">Return Date</th>
                            <th scope="col" class="px-4 py-3 text-center">Status</th>
                            <th scope="col" class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredAssets" :key="item.id"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            <th scope="row" class="px-4 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ item.id || 'N/A' }}
                            </th>
                            <td class="px-2 py-1 text-center">{{ item.name || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.category || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.location || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.purchase_date || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.unit_qty || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.condition || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.assigned_to || 'Unassigned' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.return_date || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">
                                <span
                                    :class="{
                                        'text-white bg-yellow-600 hover:bg-yellow-700': item.status === 'Good',
                                        'text-white bg-red-700 hover:bg-red-800': item.status === 'Check',
                                        'text-white bg-green-600 hover:bg-green-800': item.status === 'Repair',
                                        'text-white bg-blue-700 hover:bg-blue-800': item.status === 'Upgrade',
                                    }"
                                    class="font-medium rounded-full text-sm px-5 py-1.5 text-center me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                                >
                                    {{ item.status || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-1 flex justify-center items-center gap-1">
                                <Button
                                    @click="openEditModal(item)"
                                    class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                >
                                    Edit
                                </Button>
                                <Link
                                    :href="route('inventory.view', { inventory: item.id })"
                                    class="font-medium text-green-600 dark:text-green-500 hover:underline"
                                >
                                    View
                                </Link>
                                <Button
                                    @click="openConfirmArchiveModal(item.id)"
                                    class="font-medium text-yellow-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                >
                                    Archive
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!filteredAssets.length" class="text-center py-4">
                    No assets found.
                </div>
            </div>
        </div>
    </AppLayout>
</template>