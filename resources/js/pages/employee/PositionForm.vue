<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

const emit = defineEmits(['store-position', 'close']);

const positionForm = useForm({
    item_code: '',
    name: '',
    desc: '',
    salary_grade: '',
});

const positionErrors = ref<{ [key: string]: string }>({});

const validatePosition = () => {
    positionErrors.value = {};
    if (!positionForm.item_code) positionErrors.value.item_code = 'Item code is required';
    if (!positionForm.name) positionErrors.value.name = 'Position name is required';
    return Object.keys(positionErrors.value).length === 0;
};

const submitPosition = () => {
    console.log('PositionForm.vue: Attempting to submit position:', positionForm.data());
    if (!validatePosition()) {
        console.log('PositionForm.vue: Client-side validation failed:', positionErrors.value);
        return;
    }

    positionForm.post(route('employee.position'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('PositionForm.vue: Position submission successful, flash data:', page.props.flash);
            const newPosition = page.props.flash.position || {
                value: positionForm.item_code,
                label: positionForm.name,
                item_code: positionForm.item_code,
                salary_grade: positionForm.salary_grade,
                org_code: '',
            };
            console.log('PositionForm.vue: Emitting newPosition:', newPosition);
            emit('store-position', newPosition);
            positionForm.reset();
            emit('close');
        },
        onError: (serverErrors) => {
            console.log('PositionForm.vue: Server validation errors:', serverErrors);
            positionErrors.value = serverErrors;
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitPosition" class="space-y-6 text-black">
        <div v-if="Object.keys(positionErrors).length" class="text-red-600 text-sm mb-4">
            <p>Please correct the following errors:</p>
            <p v-for="(error, field) in positionErrors" :key="field">{{ error }}</p>
        </div>
        <div>
            <Label for="item_code">Item Code</Label>
            <Input
                id="item_code"
                v-model="positionForm.item_code"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="positionErrors.item_code" class="text-red-600 text-sm">{{ positionErrors.item_code }}</span>
        </div>
        <div>
            <Label for="name">Position Name</Label>
            <Input
                id="name"
                v-model="positionForm.name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="positionErrors.name" class="text-red-600 text-sm">{{ positionErrors.name }}</span>
        </div>
        <div>
            <Label for="desc">Description</Label>
            <Input
                id="desc"
                v-model="positionForm.desc"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="positionErrors.desc" class="text-red-600 text-sm">{{ positionErrors.desc }}</span>
        </div>
        <div>
            <Label for="salary_grade">Salary Grade</Label>
            <Input
                id="salary_grade"
                v-model="positionForm.salary_grade"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="positionErrors.salary_grade" class="text-red-600 text-sm">{{ positionErrors.salary_grade }}</span>
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
                :disabled="positionForm.processing"
            >
                Submit
            </Button>
        </div>
    </form>
</template>

<style scoped>
/* Form styling */
</style>