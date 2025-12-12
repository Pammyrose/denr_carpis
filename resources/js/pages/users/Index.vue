<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Edit from './Edit.vue';
import View from './View.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Define the User interface
interface User {
    id: number;
    full_name: string;
    email: string;
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
    list = list.filter(user => {
      return (
        user.full_name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
      );
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/users' },
];

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
    { immediate: true }
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

// Delete User
const deleteUser = (userId: number) => {
    if (!confirm('Are you sure you want to delete this user?')) return;

    router.delete(route('users.destroy', userId), {
        preserveScroll: true,
        onSuccess: () => {
            alertMessage.value = 'User deleted successfully';
            alertType.value = 'success';
            setTimeout(() => clearAlert(), 3000);
        },
        onError: (errors) => {
            const errorMessage = Object.values(errors).join(', ') || 'Failed to delete user';
            alertMessage.value = errorMessage;
            alertType.value = 'error';
            setTimeout(() => clearAlert(), 5000);
        },
    });
};

const clearAlert = () => {
    alertMessage.value = '';
    alertType.value = '';
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
                    'bg-green-100 border-green-500 text-green-800': alertType === 'success',
                    'bg-red-100 border-red-500 text-red-800': alertType === 'error',
                }"
                class="border-l-4 p-4 mb-6 rounded-r-lg shadow-md"
                role="alert"
            >
                <p class="font-bold">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
                <p>{{ alertMessage }}</p>
            </div>

            <!-- Header: Create Button + Search -->
            <div class="flex justify-end items-right mb-4 gap-4">


                <div class="relative w-full max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition dark:text-white"
                    />
                    <svg
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                
                                <!-- SORTABLE FULL NAME COLUMN -->
                                <th 
                                    scope="col" 
                                    class="px-4 py-3 text-center cursor-pointer select-none"
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
                                        Full Name
                                        <div class="flex flex-col">
                                            <svg class="w-3 h-3 text-white transition-opacity"
                                                 :class="{ 'opacity-100': sortColumn === 'name' && sortDirection === 'asc', 'opacity-30': sortColumn !== 'name' || sortDirection !== 'asc' }"
                                                 fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 8l-6 6h12l-6-6z"/>
                                            </svg>
                                            <svg class="w-3 h-3 text-white -mt-1 transition-opacity rotate-180"
                                                 :class="{ 'opacity-100': sortColumn === 'name' && sortDirection === 'desc', 'opacity-30': sortColumn !== 'name' || sortDirection !== 'desc' }"
                                                 fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 8l-6 6h12l-6-6z"/>
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
                                class="border-b hover:bg-gray-50 dark:hover:bg-gray-700 transition text-black dark:hover:text-white"
                            >
                                <td class="px-4 py-3 font-medium">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-center">{{ user.full_name }}</td>
                                <td class="px-4 py-3 text-center">{{ user.email }}</td>
                                <td class="px-4 py-3 flex justify-center gap-6">
                                    <button
                                        @click="openEditModal(user)"
                                        class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="openViewModal(user)"
                                        class="font-medium text-green-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="!filteredUsers.length" class="text-center py-16 text-gray-500">
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
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
             @click.self="closeCreateModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative border border-gray-200">
                <button @click="closeCreateModal"
                        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10 bg-white rounded-full p-1 shadow hover:shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div v-if="isEditModalOpen && selectedUser" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
             @click.self="closeEditModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative border border-gray-200">
                <button @click="closeEditModal"
                        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10 bg-white rounded-full p-1 shadow hover:shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div v-if="isViewModalOpen && selectedViewUser"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-xs">
            <View :user="selectedViewUser" :closeModal="closeViewModal" />
        </div>
    </teleport>
</template>