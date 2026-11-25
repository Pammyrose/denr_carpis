<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import View from './View.vue';

// Define the User interface
interface User {
    id: number;
    full_name: string;
    email: string;
    is_admin: number;
    employee?: any;
    assets?: any[];
}

interface Props {
    users: User[];
    error?: string | null;
}

const props = defineProps<Props>();
const page = usePage();

// Alert state
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

// Search
const searchQuery = ref<string>('');

// ADDED: Sorting state
const sortColumn = ref<'name' | ''>('');
const sortDirection = ref<'asc' | 'desc' | ''>('');

// Filtered + Sorted users
const filteredUsers = computed(() => {
    let list = [...props.users];

    // Apply search filter
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        list = list.filter((user) => {
            return user.full_name.toLowerCase().includes(query) || user.email.toLowerCase().includes(query);
        });
    }

    // Apply sorting by full name
    if (sortColumn.value === 'name' && sortDirection.value) {
        list.sort((a, b) => {
            const nameA = a.full_name.toLowerCase();
            const nameB = b.full_name.toLowerCase();
            const comparison = nameA.localeCompare(nameB);
            return sortDirection.value === 'asc' ? comparison : -comparison;
        });
    }

    return list;
});

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Users', href: '/users' }];

// Modal States
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isViewModalOpen = ref(false);

const selectedUser = ref<User | null>(null);
const selectedViewUser = ref<User | null>(null);

// Watch for flash messages
watch(
    () => (page.props as any).flash,
    (flash) => {
        if (flash?.message) {
            alertMessage.value = flash.message;
            alertType.value = 'success';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        }
    },
    { immediate: true },
);

// Handle error prop
onMounted(() => {
    if (props.error) {
        alertMessage.value = props.error;
        alertType.value = 'error';
        setTimeout(() => {
            alertMessage.value = '';
            alertType.value = '';
        }, 5000);
    }
});

// Modal Controls
const openCreateModal = () => {
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const openEditModal = (user: User) => {
    selectedUser.value = { ...user };
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedUser.value = null;
};

const openViewModal = (user: User) => {
    selectedViewUser.value = { ...user };
    isViewModalOpen.value = true;
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedViewUser.value = null;
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Alert Notification -->
            <div
                v-if="alertMessage"
                :class="{
                    'border-green-500 bg-green-100 text-green-800': alertType === 'success',
                    'border-red-500 bg-red-100 text-red-800': alertType === 'error',
                }"
                class="mb-6 rounded-r-lg border-l-4 p-4 shadow-md"
                role="alert"
            >
                <p class="font-bold">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
                <p>{{ alertMessage }}</p>
            </div>

            <!-- Header: Create Button + Search -->
            <div class="items-right mb-4 flex justify-end gap-4">
                <button
                    @click="openCreateModal"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 font-medium whitespace-nowrap text-white hover:bg-blue-700"
                >
                    Create user
                </button>
                <div class="relative w-full max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 transition focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"
                    />
                    <svg
                        class="pointer-events-none absolute top-1/2 right-3 h-5 w-5 -translate-y-1/2 transform text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
                        <thead class="bg-gradient-to-b from-green-300 to-green-600 text-xs text-white uppercase">
                            <tr>
                                <th class="px-4 py-3">No</th>

                                <!-- SORTABLE FULL NAME COLUMN -->
                                <th
                                    scope="col"
                                    class="cursor-pointer px-4 py-3 text-center select-none"
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
                                        Full Name
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

                                <th class="px-4 py-3 text-center">Email</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(user, index) in filteredUsers"
                                :key="user.id"
                                class="border-b text-black transition hover:bg-gray-50 dark:hover:bg-gray-700 dark:hover:text-white"
                            >
                                <td class="px-4 py-3 font-medium">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-center">{{ user.full_name }}</td>
                                <td class="px-4 py-3 text-center">{{ user.email }}</td>
                                <td class="flex justify-center gap-6 px-4 py-3">
                                    <button
                                        @click="openViewModal(user)"
                                        class="border-0 bg-transparent font-medium text-green-600 shadow-none hover:bg-transparent hover:underline"
                                    >
                                        View
                                    </button>
                                    <button
                                        @click="openEditModal(user)"
                                        class="border-0 bg-transparent font-medium text-blue-600 shadow-none hover:bg-transparent hover:underline"
                                    >
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="!filteredUsers.length" class="py-16 text-center text-gray-500">
                        <p class="text-lg font-medium">
                            {{ searchQuery ? 'No users match your search.' : 'No users found.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Modals (unchanged) -->
    <teleport to="body">
        <div
            v-if="isCreateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="closeCreateModal"
        >
            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl">
                <button
                    @click="closeCreateModal"
                    class="absolute top-4 right-4 z-10 rounded-full bg-white p-1 text-gray-500 shadow hover:text-gray-700 hover:shadow-md"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="p-6">
                    <Create :closeModal="closeCreateModal" />
                </div>
            </div>
        </div>
    </teleport>

    <teleport to="body">
        <div
            v-if="isEditModalOpen && selectedUser"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="closeEditModal"
        >
            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl">
                <button
                    @click="closeEditModal"
                    class="absolute top-4 right-4 z-10 rounded-full bg-white p-1 text-gray-500 shadow hover:text-gray-700 hover:shadow-md"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="p-6">
                    <Edit :user="selectedUser" :closeModal="closeEditModal" />
                </div>
            </div>
        </div>
    </teleport>

    <teleport to="body">
        <div v-if="isViewModalOpen && selectedViewUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-xs">
            <View :user="selectedViewUser" :closeModal="closeViewModal" />
        </div>
    </teleport>
</template>
