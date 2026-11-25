<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { type AppPageProps } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const officeScopes = ['Regional Office', 'PENRO Abra', 'PENRO Apayao', 'PENRO Benguet', 'PENRO Ifugao', 'PENRO Kalinga', 'PENRO MT.Province'];

const props = defineProps<{
    closeModal: () => void;
}>();

const isSuperAdmin = Number(usePage<AppPageProps>().props.auth.user.is_admin) === 1;

const form = useForm({
    full_name: '',
    email: '',
    is_admin: 0,
    office_scope: '',
    password: '',
    password_confirmation: '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

const validate = () => {
    errors.value = {};
    if (!form.full_name) errors.value.full_name = 'Full name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (![0, 1, 2].includes(form.is_admin)) errors.value.is_admin = 'Role is required';
    if (form.is_admin === 2 && !form.office_scope) errors.value.office_scope = 'Office scope is required for Admin';
    if (!form.password) errors.value.password = 'Password is required';
    if (!form.password_confirmation) errors.value.password_confirmation = 'Password confirmation is required';
    if (form.password !== form.password_confirmation) errors.value.password_confirmation = 'Passwords do not match';
    return Object.keys(errors.value).length === 0;
};

watch(
    () => form.is_admin,
    (role) => {
        if (role !== 2) form.office_scope = '';
    },
);

const submit = () => {
    alertMessage.value = '';
    alertType.value = '';
    if (!validate()) {
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            alertMessage.value = 'User created successfully.';
            alertType.value = 'success';
            form.reset();
            props.closeModal();
        },
        onError: (serverErrors) => {
            errors.value = { ...errors.value, ...serverErrors };
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create user';
            alertType.value = 'error';
        },
    });
};
</script>

<template>
    <div class="relative">
        <div
            v-if="alertMessage"
            :class="{
                'border-green-400 bg-green-100 text-green-700': alertType === 'success',
                'border-red-400 bg-red-100 text-red-700': alertType === 'error',
            }"
            class="mb-4 rounded-r-md border-l-4 p-4"
            role="alert"
            style="position: relative; z-index: 1000"
        >
            <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
            <p>{{ alertMessage }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Create User</h2>
            <div class="grid gap-4 text-black">
                <div>
                    <Label for="full_name">Full name</Label>
                    <Input
                        id="full_name"
                        v-model="form.full_name"
                        type="text"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.full_name }"
                    />
                    <InputError :message="errors.full_name" />
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <InputError :message="errors.email" />
                </div>
                <div v-if="isSuperAdmin">
                    <Label for="is_admin">Role</Label>
                    <select
                        id="is_admin"
                        v-model="form.is_admin"
                        required
                        class="mt-1 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.is_admin }"
                    >
                        <option :value="1">Super Admin</option>
                        <option :value="2">Admin</option>
                        <option :value="0">User</option>
                    </select>
                    <InputError :message="errors.is_admin" />
                </div>
                <div v-if="form.is_admin === 2">
                    <Label for="office_scope">Office Scope</Label>
                    <select
                        id="office_scope"
                        v-model="form.office_scope"
                        required
                        class="mt-1 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.office_scope }"
                    >
                        <option value="" disabled>Select office</option>
                        <option v-for="office in officeScopes" :key="office" :value="office">{{ office }}</option>
                    </select>
                    <InputError :message="errors.office_scope" />
                </div>
                <div>
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': errors.password_confirmation }"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <Button type="button" @click="closeModal" class="bg-gray-200 text-gray-800 hover:bg-gray-300"> Cancel </Button>
                <Button type="submit" class="bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>
        </form>
    </div>
</template>
