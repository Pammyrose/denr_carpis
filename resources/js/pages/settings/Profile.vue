<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    user: User;
    employee: any; // Will be Employee type
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/public/settings/profile',
    },
];

// Initialize form with employee data
const form = useForm({
    email: props.user.email,
    first_name: props.employee?.first_name ?? '',
    middle_name: props.employee?.middle_name ?? '',
    last_name: props.employee?.last_name ?? '',
    suffix: props.employee?.suffix ?? '',
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your personal details and email address"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- First Name -->
                    <div class="grid gap-2">
                        <Label for="first_name">First Name</Label>
                        <Input
                            id="first_name"
                            class="mt-1 block w-full"
                            v-model="form.first_name"
                            required
                            autocomplete="given-name"
                            placeholder="Juan"
                        />
                        <InputError class="mt-2" :message="form.errors.first_name" />
                    </div>

                    <!-- Middle Name -->
                    <div class="grid gap-2">
                        <Label for="middle_name">Middle Name</Label>
                        <Input
                            id="middle_name"
                            class="mt-1 block w-full"
                            v-model="form.middle_name"
                            autocomplete="additional-name"
                            placeholder="Agustin"
                        />
                        <InputError class="mt-2" :message="form.errors.middle_name" />
                    </div>

                    <!-- Last Name -->
                    <div class="grid gap-2">
                        <Label for="last_name">Last Name</Label>
                        <Input
                            id="last_name"
                            class="mt-1 block w-full"
                            v-model="form.last_name"
                            required
                            autocomplete="family-name"
                            placeholder="Dela Cruz"
                        />
                        <InputError class="mt-2" :message="form.errors.last_name" />
                    </div>

                    <!-- Suffix -->
                    <div class="grid gap-2">
                        <Label for="suffix">Suffix</Label>
                        <Input
                            id="suffix"
                            class="mt-1 block w-full"
                            v-model="form.suffix"
                            autocomplete="honorific-suffix"
                            placeholder="Jr."
                        />
                        <InputError class="mt-2" :message="form.errors.suffix" />
                    </div>

                    <!-- Email Address -->
                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="juan@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Email Verification -->
                    <div v-if="mustVerifyEmail && !props.user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <!-- Delete Account Section -->
            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>