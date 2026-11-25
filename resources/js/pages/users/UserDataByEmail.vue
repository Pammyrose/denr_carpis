<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const email = ref('');
const user = ref<any>(null);
const error = ref<string | null>(null);
const loading = ref(false);

const fetchUserData = async () => {
    if (!email.value) {
        error.value = 'Please enter an email address';
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/api/user-data-by-email', {
            params: { email: email.value },
        });
        user.value = response.data.user;
    } catch (err: any) {
        error.value = err.response?.data?.message || 'Failed to fetch user data';
        console.error('Error fetching user data:', err);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="User Data by Email" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Users', href: '/users' },
            { title: 'User Data by Email', href: '/users/by-email' },
        ]"
    >
        <div class="p-4">
            <div class="rounded bg-white p-6 shadow">
                <h2 class="mb-4 text-lg font-semibold">Fetch User Data by Email</h2>

                <!-- Email Input Form -->
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input
                        v-model="email"
                        type="email"
                        id="email"
                        class="focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                        placeholder="Enter email address"
                        @keyup.enter="fetchUserData"
                    />
                    <button
                        @click="fetchUserData"
                        :disabled="loading"
                        class="mt-2 rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:bg-indigo-300"
                    >
                        {{ loading ? 'Fetching...' : 'Fetch User Data' }}
                    </button>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mb-4 text-red-600">
                    {{ error }}
                </div>

                <!-- User Details -->
                <div v-if="user">
                    <!-- User Basic Info -->
                    <div>
                        <h2 class="mb-2 text-lg font-semibold">User Information</h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <p><strong>Full Name:</strong> {{ user.full_name || 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ user.email }}</p>
                            <p><strong>Role:</strong> {{ user.role || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Employee Details -->
                    <div v-if="user.employee" class="mt-6">
                        <h2 class="mb-2 text-lg font-semibold">Employee Details</h2>
                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                            <p><strong>First Name:</strong> {{ user.employee.first_name || 'N/A' }}</p>
                            <p><strong>Middle Name:</strong> {{ user.employee.middle_name || 'N/A' }}</p>
                            <p><strong>Last Name:</strong> {{ user.employee.last_name || 'N/A' }}</p>
                            <p><strong>Suffix:</strong> {{ user.employee.suffix || 'N/A' }}</p>
                            <p><strong>Sex:</strong> {{ user.employee.sex || 'N/A' }}</p>
                            <p><strong>Employment Status:</strong> {{ user.employee.emp_status || 'N/A' }}</p>
                            <p><strong>Position:</strong> {{ user.employee.position_name || 'N/A' }}</p>
                            <p><strong>Assignment:</strong> {{ user.employee.assignment_name || 'N/A' }}</p>
                            <p><strong>Division/Section/Unit:</strong> {{ user.employee.div_sec_unit || 'N/A' }}</p>
                        </div>
                    </div>
                    <div v-else class="mt-6">
                        <p>No employee details available.</p>
                    </div>

                    <!-- Assets Section -->
                    <div v-if="user.assets && user.assets.length > 0" class="mt-6">
                        <h2 class="mb-2 text-lg font-semibold">Assigned Assets</h2>
                        <table class="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2">Name</th>
                                    <th class="border border-gray-300 p-2">Category</th>
                                    <th class="border border-gray-300 p-2">Location</th>
                                    <th class="border border-gray-300 p-2">Assigned Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="asset in user.assets" :key="asset.id" class="text-black hover:bg-gray-50">
                                    <td class="border border-gray-300 p-2">{{ asset.name || 'N/A' }}</td>
                                    <td class="border border-gray-300 p-2">{{ asset.category || 'N/A' }}</td>
                                    <td class="border border-gray-300 p-2">{{ asset.location || 'N/A' }}</td>
                                    <td class="border border-gray-300 p-2">{{ asset.purchase_date || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="mt-6">
                        <p>No assets connected to this user.</p>
                    </div>
                </div>
                <div v-else-if="!loading && !error" class="mt-6">
                    <p>Enter an email to fetch user details.</p>
                </div>
                <div v-if="loading" class="mt-6">
                    <p>Loading user details...</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
