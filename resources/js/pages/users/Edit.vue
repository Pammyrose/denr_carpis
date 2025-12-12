<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';

interface User {
    id: number;
    full_name: string;
    email: string;
}

const props = defineProps<{
    user: User;
    closeModal: () => void;
}>();

const form = useForm({
    full_name: props.user.full_name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const errors = ref<{ [key: string]: string }>({});

const validate = () => {
    errors.value = {};
    if (!form.full_name) errors.value.full_name = 'Full name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (form.password && form.password !== form.password_confirmation) {
        errors.value.password_confirmation = 'Passwords do not match';
    }
    return Object.keys(errors.value).length === 0;
};

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
        <div v-if="errors.general" class="text-red-600 text-sm p-2 bg-red-100 rounded">
            {{ errors.general }}
        </div>

        <div class="grid gap-4 text-black">

            <div>
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.email }"
                />
                <InputError :message="errors.email" />
            </div>
            <div>
                <Label for="password">Password (leave blank to keep unchanged)</Label>
                <Input
                    id="password"
                    type="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    placeholder="New password"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.password_confirmation }"
                />
                <InputError :message="errors.password_confirmation" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <Button
                type="button"
                @click="props.closeModal"
                class="bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                class="bg-blue-600 text-white hover:bg-blue-700"
                :disabled="form.processing"
            >
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                Update User
            </Button>
        </div>
    </form>
</template>