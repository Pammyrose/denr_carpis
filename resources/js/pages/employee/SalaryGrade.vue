
<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import { PropType } from 'vue';

const props = defineProps<{
    positions: { value: string; label: string; salary_grade: string | null }[]; // Maps to item_code, name, salary_grade
    preselectedItemCode?: string; // Preselected item_code from Create.vue
}>();

const emit = defineEmits(['store-salary-grade', 'close']);

const salaryGradeForm = useForm({
    item_code: props.preselectedItemCode || '', // Initialize with preselected item_code
    salary_grade: '',
});

const salaryGradeErrors = ref<{ [key: string]: string }>({});

// Predefined salary grade options (adjust as needed based on your requirements)
const salaryGradeOptions = ref([
    { value: '', label: 'Select Salary Grade' },
    { value: 'SG-1', label: 'SG-1' },
    { value: 'SG-2', label: 'SG-2' },
    { value: 'SG-3', label: 'SG-3' },
    { value: 'SG-4', label: 'SG-4' },
    { value: 'SG-5', label: 'SG-5' },
    { value: 'SG-6', label: 'SG-6' },
    { value: 'SG-7', label: 'SG-7' },
    { value: 'SG-8', label: 'SG-8' },
    { value: 'SG-9', label: 'SG-9' },
    { value: 'SG-10', label: 'SG-10' },
]);

// Watch for changes in preselectedItemCode to update item_code
watch(() => props.preselectedItemCode, (newItemCode) => {
    salaryGradeForm.item_code = newItemCode || '';
    // Set salary_grade based on the selected position
    const selectedPosition = props.positions.find(p => p.value === newItemCode);
    salaryGradeForm.salary_grade = selectedPosition?.salary_grade || '';
});

// Watch for changes in item_code to update salary_grade
watch(() => salaryGradeForm.item_code, (newItemCode) => {
    const selectedPosition = props.positions.find(p => p.value === newItemCode);
    salaryGradeForm.salary_grade = selectedPosition?.salary_grade || '';
});

const validateSalaryGrade = () => {
    salaryGradeErrors.value = {};
    if (!salaryGradeForm.item_code) salaryGradeErrors.value.item_code = 'Position is required';
    if (!salaryGradeForm.salary_grade) salaryGradeErrors.value.salary_grade = 'Salary grade is required';
    return Object.keys(salaryGradeErrors.value).length === 0;
};

const submitSalaryGrade = () => {
    if (!validateSalaryGrade()) {
        console.log('Client-side validation failed for salary grade:', salaryGradeErrors.value);
        return;
    }

    salaryGradeForm.post(route('employee.salaryGrade'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Salary grade submission successful');
            const updatedPosition = page.props.flash.position || {
                value: salaryGradeForm.item_code, // Maps to item_code
                label: props.positions.find(p => p.value === salaryGradeForm.item_code)?.label || '',
                salary_grade: salaryGradeForm.salary_grade, // Maps to salary_grade
            };
            emit('store-salary-grade', updatedPosition);
            salaryGradeForm.reset();
            emit('close');
        },
        onError: (serverErrors) => {
            console.log('Server validation errors for salary grade:', serverErrors);
            salaryGradeErrors.value = serverErrors;
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitSalaryGrade" class="space-y-6 text-black">
        <div>
            <Label for="item_code">Position</Label>
            <select
                id="item_code"
                v-model="salaryGradeForm.item_code"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="" disabled>Select Position</option>
                <option
                    v-for="position in props.positions"
                    :key="position.value"
                    :value="position.value"
                    :class="{ 'bg-blue-100': position.value === salaryGradeForm.item_code }"
                >
                    {{ position.label }}
                </option>
            </select>
            <span v-if="salaryGradeErrors.item_code" class="text-red-600 text-sm">{{ salaryGradeErrors.item_code }}</span>
        </div>
        <div>
            <Label for="salary_grade">Salary Grade</Label>
            <select
                id="salary_grade"
                v-model="salaryGradeForm.salary_grade"
                class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option v-for="option in salaryGradeOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <span v-if="salaryGradeErrors.salary_grade" class="text-red-600 text-sm">{{ salaryGradeErrors.salary_grade }}</span>
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
                :disabled="salaryGradeForm.processing"
            >
                Submit
            </Button>
        </div>
    </form>
</template>

<style scoped>
.bg-blue-100 {
    background-color: #dbeafe; /* Highlight preselected position */
}
</style>
