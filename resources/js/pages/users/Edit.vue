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

interface User {
    id: number;
    full_name: string;
    email: string;
    is_admin: number;
    office_scope?: string | null;
    employee?: unknown;
}

const props = defineProps<{
    user: User;
    closeModal: () => void;
}>();

const isSuperAdmin = Number(usePage<AppPageProps>().props.auth.user.is_admin) === 1;

const form = useForm({
    full_name: props.user.full_name,
    email: props.user.email,
    is_admin: Number(props.user.is_admin ?? 2),
    office_scope: props.user.office_scope ?? '',
    password: '',
    password_confirmation: '',
});

const errors = ref<{ [key: string]: string }>({});

const validate = () => {
    errors.value = {};
    if (!form.full_name) errors.value.full_name = 'Full name is required';
    else if (props.user.employee && !form.full_name.includes(',')) {
        errors.value.full_name = 'Use the format: Last Name, First Name Middle Name Suffix';
    }
    if (!form.email) errors.value.email = 'Email is required';
    if (![0, 1, 2].includes(form.is_admin)) errors.value.is_admin = 'Role is required';
    if (form.is_admin === 2 && !form.office_scope) errors.value.office_scope = 'Office scope is required for Admin';
    if (form.password && form.password !== form.password_confirmation) {
        errors.value.password_confirmation = 'Passwords do not match';
    }
    return Object.keys(errors.value).length === 0;
};

watch(
    () => form.is_admin,
    (role) => {
        if (role !== 2) form.office_scope = '';
    },
);

const submit = () => {
    console.log('Submitting form data:', {
        id: props.user.id,
        ...form.data(),
    });
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
        return;
    }
    form.put(route('users.update', props.user.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('Update successful');
            form.reset('password', 'password_confirmation');
            props.closeModal();
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = { ...errors.value, ...serverErrors };
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Edit User</h2>
        <div v-if="errors.general" class="rounded bg-red-100 p-2 text-sm text-red-600">
            {{ errors.general }}
        </div>

        <div class="grid gap-4 text-black">
            <div>
                <Label for="full_name">Full Name</Label>
                <Input
                    id="full_name"
                    type="text"
                    v-model="form.full_name"
                    required
                    autocomplete="name"
                    placeholder="Last Name, First Name Middle Name Suffix"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-white"
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-white"
                    :class="{ 'border-red-500': errors.email }"
                />
                <InputError :message="errors.email" />
            </div>
            <div v-if="isSuperAdmin">
                <Label for="is_admin">Role</Label>
                <select
                    id="is_admin"
                    v-model="form.is_admin"
                    class="mt-1 block w-full cursor-pointer rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.is_admin }"
                >
                    <option value="" disabled>Select Role</option>
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
                <Label for="password">Password (leave blank to keep unchanged)</Label>
                <Input
                    id="password"
                    type="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    placeholder="New password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-white"
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
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-white"
                    :class="{ 'border-red-500': errors.password_confirmation }"
                />
                <InputError :message="errors.password_confirmation" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Button type="button" @click="props.closeModal" class="bg-gray-200 text-gray-800 hover:bg-gray-300"> Cancel </Button>
            <Button type="submit" class="bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Update User
            </Button>
        </div>
    </form>
</template>
