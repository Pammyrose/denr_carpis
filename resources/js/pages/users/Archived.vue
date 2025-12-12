<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';

interface ArchivedUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  archived_at: string;
}

interface BreadcrumbItem {
  title: string;
  href: string;
}

const props = defineProps<{
  archivedUsers: ArchivedUser[] | null;
  error?: string | null;
}>();

const page = usePage();

const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

onMounted(() => {
  console.log('users/Archived.vue: Props received', {
    archivedUsers: props.archivedUsers,
    error: props.error,
    flash: (page.props as any).flash,
  });
  if (props.error) {
    console.log('users/Archived.vue: Error prop detected:', props.error);
    alertMessage.value = props.error;
    alertType.value = 'error';
    setTimeout(() => {
      alertMessage.value = '';
      alertType.value = '';
    }, 3000);
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/users' },
  { title: 'Archived Users', href: '/users/archived' },
];

const searchQuery = ref('');

const filteredArchivedUsers = computed(() => {
  if (!props.archivedUsers || !Array.isArray(props.archivedUsers)) {
    console.warn('users/Archived.vue: No archived users or invalid prop', props.archivedUsers);
    return [];
  }
  if (!searchQuery.value.trim()) {
    return props.archivedUsers;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return props.archivedUsers.filter(user => {
    return (
      user.name?.toLowerCase().includes(query) ||
      user.email?.toLowerCase().includes(query) ||
      (user.is_admin ? 'admin' : 'user').includes(query) ||
      user.archived_at?.toLowerCase().includes(query)
    );
  });
});

const openConfirmUnarchiveModal = (userId: number) => {
  console.log('Opening confirm unarchive modal for user:', userId);
  if (confirm('Are you sure you want to unarchive this user? This will restore them to the active users list.')) {
    unarchiveUser(userId);
  }
};

const unarchiveUser = (userId: number) => {
  console.log('Unarchiving user:', userId);
  router.post(route('users.unarchive', userId), {}, {
    onSuccess: () => {
      console.log('Unarchive successful');
      if (props.archivedUsers) {
        props.archivedUsers = props.archivedUsers.filter(user => user.id !== userId);
      }
      alertMessage.value = 'User unarchived successfully';
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
    onError: (errors) => {
      console.error('Unarchive failed:', errors);
      alertMessage.value = 'Failed to unarchive user: ' + Object.values(errors).join(', ');
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
    console.log('users/Archived.vue: Watch triggered, flash:', flash);
    if (flash?.message) {
      console.log('users/Archived.vue: Flash message detected:', flash.message);
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
  <Head title="Archived Users" />

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

      <!-- Search Input -->
      <div class="flex justify-end items-center mb-4">
        <div class="relative w-full max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by name, email, role, or archived date..."
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

      <!-- Archived Users Table -->
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
            <tr>
              <th scope="col" class="px-4 py-3">User ID</th>
              <th scope="col" class="px-4 py-3 text-center">Name</th>
              <th scope="col" class="px-4 py-3 text-center">Email</th>
              <th scope="col" class="px-4 py-3 text-center">Role</th>
              <th scope="col" class="px-4 py-3 text-center">Archived At</th>
              <th scope="col" class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="user in filteredArchivedUsers"
              :key="user.id"
              class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <th scope="row" class="px-4 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                {{ user.id || 'N/A' }}
              </th>
              <td class="px-2 py-1 text-center">{{ user.name || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ user.email || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">
                <span
                  :class="{
                    'text-white bg-blue-600': user.is_admin,
                    'text-white bg-gray-600': !user.is_admin,
                  }"
                  class="font-medium rounded-full text-sm px-5 py-1.5 text-center"
                >
                  {{ user.is_admin ? 'Admin' : 'User' }}
                </span>
              </td>
              <td class="px-2 py-1 text-center">{{ user.archived_at || 'N/A' }}</td>
              <td class="px-4 py-1 flex justify-center items-center gap-1">
                <Button
                  @click="openConfirmUnarchiveModal(user.id)"
                  class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                >
                  Unarchive
                </Button>
              </td>
            </tr>
            <tr v-if="!filteredArchivedUsers.length">
              <td colspan="6" class="text-center py-4">No archived users found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>