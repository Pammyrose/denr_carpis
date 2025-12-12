<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

const emit = defineEmits(['add-org-unit', 'close']);

const orgUnitForm = useForm({
    org_code: '',
    name: '',
    description: '',
});

const orgUnitErrors = ref<{ [key: string]: string }>({});

const validateOrgUnit = () => {
    orgUnitErrors.value = {};
    if (!orgUnitForm.org_code) orgUnitErrors.value.org_code = 'Organization code is required';
    if (!orgUnitForm.name) orgUnitErrors.value.name = 'Organization name is required';
    return Object.keys(orgUnitErrors.value).length === 0;
};

const submitOrgUnit = () => {
    if (!validateOrgUnit()) {
        console.log('Client-side validation failed for org unit:', orgUnitErrors.value);
        return;
    }

    orgUnitForm.post(route('employee.storeOrgUnit'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Org unit submission successful');
            const newOrgUnit = page.props.flash.orgUnit || { value: orgUnitForm.org_code, label: orgUnitForm.name };
            emit('add-org-unit', newOrgUnit);
            orgUnitForm.reset();
            emit('close');
        },
        onError: (serverErrors) => {
            console.log('Server validation errors for org unit:', serverErrors);
            orgUnitErrors.value = serverErrors;
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitOrgUnit" class="space-y-6 text-black">
        <div>
            <Label for="org_code">Organization Code</Label>
            <Input
                id="org_code"
                v-model="orgUnitForm.org_code"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="orgUnitErrors.org_code" class="text-red-600 text-sm">{{ orgUnitErrors.org_code }}</span>
        </div>
        <div>
            <Label for="name">Organization Name</Label>
            <Input
                id="name"
                v-model="orgUnitForm.name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="orgUnitErrors.name" class="text-red-600 text-sm">{{ orgUnitErrors.name }}</span>
        </div>
        <div>
            <Label for="description">Description</Label>
            <Input
                id="description"
                v-model="orgUnitForm.description"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="orgUnitErrors.description" class="text-red-600 text-sm">{{ orgUnitErrors.description }}</span>
        </div>
        <div class="flex justify-end gap-3">
            <Button
                type="button"
                @click="emit('close')"
                class="bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                class="bg-blue-600 text-white hover:bg-blue-700"
                :disabled="orgUnitForm.processing"
            >
                Submit
            </Button>
        </div>
    </form>
</template>

<style scoped>
/* Form styling */
</style>