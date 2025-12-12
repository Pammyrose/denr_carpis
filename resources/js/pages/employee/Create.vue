
<script setup lang="ts">
import { ref, watch, onMounted, nextTick, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Modal from '@/pages/employee/Modal.vue';
import OrgUnitForm from '@/pages/employee/OrgUnitForm.vue';
import SalaryGradeForm from '@/pages/employee/SalaryGrade.vue';
import AssignmentPlaces from '@/pages/employee/AssignmentPlaces.vue';
import PositionForm from '@/pages/employee/PositionForm.vue';

const props = defineProps<{
    orgUnits?: { value: string; label: string }[];
    positions?: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }[];
    assignmentPlaces?: { value: string; label: string }[];
    empStatuses?: { value: string; label: string }[];
    error?: string;
    close: () => void;
}>();

const emit = defineEmits(['close']);

const orgUnitsLocal = ref(props.orgUnits || []);
const assignmentOptions = ref(props.assignmentPlaces || []);
const positionsLocal = ref(props.positions || []);
const employeeStatuses = ref(props.empStatuses || []);

onMounted(() => {
    console.log('Create.vue: Initial props:', {
        orgUnits: props.orgUnits,
        positions: props.positions,
        assignmentPlaces: props.assignmentPlaces,
        empStatuses: props.empStatuses,
    });
});

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: '',
    email: '',
    contact_no: '',
    password: '',
    password_confirmation: '',
    emp_status: '',
    position_name: '',
    assignment_name: '',
    div_sec_unit: '',
    salary_grade: '',
    date_of_birth: '',
    tin_no: '',
    date_appointment: '',
    date_last_promotion: '',
    civil_service: '',
    education: '',
    
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>(''); // Controls alert visibility and type
const showModal = ref(false);
const modalContent = ref<'orgUnit' | 'salaryGrade' | 'assignmentPlace' | 'position' | null>(null);

const salaryGradeOptions = ref<{ value: string; label: string }[]>([{ value: '', label: 'Select Salary Grade' }]);

// Watch for position_name changes to update salary grade
watch(
    () => form.position_name,
    (newPositionName) => {
        console.log('Create.vue: Position changed:', newPositionName);
        const selectedPosition = positionsLocal.value.find((p) => p.value === newPositionName);
        salaryGradeOptions.value = selectedPosition?.salary_grade
            ? [{ value: selectedPosition.salary_grade, label: selectedPosition.salary_grade }]
            : [{ value: '', label: 'Select Salary Grade' }];
        form.salary_grade = selectedPosition?.salary_grade || '';
    }
);

// Debug watchers for form fields
watch(
    () => form.position_name,
    (newValue) => {
        console.log('form.position_name updated to:', newValue, 'Positions available:', positionsLocal.value);
    }
);

watch(
    () => form.assignment_name,
    (newValue) => {
        console.log('form.assignment_name updated to:', newValue, 'Assignments available:', assignmentOptions.value);
    }
);

const validate = () => {
    errors.value = {};
    if (!form.first_name) errors.value.first_name = 'First name is required';
    if (!form.last_name) errors.value.last_name = 'Last name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    if (!form.emp_status) errors.value.emp_status = 'Employee status is required';
    if (!form.contact_no) errors.value.contact_no = 'Contact No is required';
    if (!form.position_name) errors.value.position_name = 'Position is required';
    if (!form.assignment_name) errors.value.assignment_name = 'Assignment is required';
    if (!form.div_sec_unit) errors.value.div_sec_unit = 'Division Section Unit is required';
    if (!form.password) errors.value.password = 'Password is required';
    if (!form.password_confirmation) errors.value.password_confirmation = 'Password confirmation is required';
    if (form.password !== form.password_confirmation)
        errors.value.password_confirmation = 'Passwords do not match';
    if (!form.date_of_birth) errors.value.date_of_birth = 'Date of birth is required';
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    console.log('Create.vue: Submitting form with data:', form.data());
    alertMessage.value = ''; // Reset alert
    alertType.value = '';
    if (!validate()) {
        console.log('Create.vue: Validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }
    form.post(route('employee.store'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Create.vue: Form submitted successfully');
            alertMessage.value = 'Employee created successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                props.close();
            }, 2000); // Delay to show alert before closing
        },
        onError: (serverErrors) => {
            console.log('Create.vue: Server errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create employee';
            alertType.value = 'error';
        },
    });
};

const openModal = (content: 'orgUnit' | 'salaryGrade' | 'assignmentPlace' | 'position') => {
    modalContent.value = content;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    modalContent.value = null;
    console.log('Create.vue: Modal closed, current form state:', form.data());
};

const addOrgUnit = async (newOrgUnit: { value: string; label: string }) => {
    if (!orgUnitsLocal.value.find((unit) => unit.value === newOrgUnit.value)) {
        orgUnitsLocal.value = [...orgUnitsLocal.value, newOrgUnit];
    }
    form.div_sec_unit = newOrgUnit.value;
    console.log('Added org unit:', newOrgUnit, 'form.div_sec_unit:', form.div_sec_unit, 'orgUnitsLocal:', orgUnitsLocal.value);
    await nextTick();
    const orgUnitSelect = document.getElementById('div_sec_unit') as HTMLSelectElement;
    if (orgUnitSelect) orgUnitSelect.focus();
    closeModal();
};

const addSalaryGrade = async (updatedPosition: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }) => {
    const positionIndex = positionsLocal.value.findIndex((p) => p.value === updatedPosition.value);
    if (positionIndex >= 0) {
        positionsLocal.value = [
            ...positionsLocal.value.slice(0, positionIndex),
            updatedPosition,
            ...positionsLocal.value.slice(positionIndex + 1),
        ];
    } else {
        positionsLocal.value = [...positionsLocal.value, updatedPosition];
    }
    if (form.position_name === updatedPosition.value) {
        form.salary_grade = updatedPosition?.salary_grade || '';
    }
    console.log('Updated position:', updatedPosition, 'positionsLocal:', positionsLocal.value, 'form.salary_grade:', form.salary_grade);
    await nextTick();
    closeModal();
};

const addAssignmentPlace = async (newAssignmentPlace: { value: string; label: string }) => {
    if (!assignmentOptions.value.find((opt) => opt.value === newAssignmentPlace.value)) {
        assignmentOptions.value = [...assignmentOptions.value, newAssignmentPlace];
    }
    form.assignment_name = newAssignmentPlace.value;
    console.log('Added assignment place:', newAssignmentPlace, 'form.assignment_name:', form.assignment_name, 'assignmentOptions:', assignmentOptions.value);
    await nextTick();
    const assignmentSelect = document.getElementById('assignment_name') as HTMLSelectElement;
    if (assignmentSelect) assignmentSelect.focus();
    closeModal();
};

const addPosition = async (newPosition: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }) => {
    if (!positionsLocal.value.find((pos) => pos.value === newPosition.value)) {
        positionsLocal.value = [...positionsLocal.value, newPosition];
    }
    form.position_name = '';
    form.position_name = newPosition.value;
    console.log('Added position:', newPosition, 'form.position_name:', form.position_name, 'positionsLocal:', positionsLocal.value);
    await nextTick();
    const positionSelect = document.getElementById('position_name') as HTMLSelectElement;
    if (positionSelect) positionSelect.focus();
    closeModal();
};

const openSalaryGradeModal = () => {
    if (!form.position_name) {
        errors.value.position_name = 'Please select a position first';
        alertMessage.value = errors.value.position_name;
        alertType.value = 'error';
        return;
    }
    openModal('salaryGrade');
};

const addAssignment = () => {
    openModal('assignmentPlace');
};

const vacantPositions = computed(() => {
    return positionsLocal.value.filter(pos => 
        !pos.label.includes('(Occupied)') && (!pos.status || pos.status !== 'Occupied')
    );
});
</script>

<template>
    <div class="relative">
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

        <form @submit.prevent="submit" class="space-y-6 ">
            <h2 class="text-lg font-semibold text-gray-900">Create Employee</h2>
            <div v-if="Object.keys(errors).length" class="text-red-600 text-sm mb-4">
                <p v-for="(error, field) in errors" :key="field">{{ error }}</p>
            </div>

            <!-- Name Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">
                <div>
                    <Label for="first_name" class="text-xs md:text-sm">First Name</Label>
                    <Input id="first_name" v-model="form.first_name" />
                    <span v-if="errors.first_name" class="text-red-600 text-sm">{{ errors.first_name }}</span>
                </div>
                <div>
                    <Label for="middle_name" class="text-xs md:text-sm">Middle Name</Label>
                    <Input id="middle_name" v-model="form.middle_name" />
                </div>
                <div>
                    <Label for="last_name" class="text-xs md:text-sm">Last Name</Label>
                    <Input id="last_name" v-model="form.last_name" />
                    <span v-if="errors.last_name" class="text-red-600 text-sm">{{ errors.last_name }}</span>
                </div>
                <div>
                    <Label for="suffix" class="text-xs md:text-sm">Suffix</Label>
                    <Input id="suffix" v-model="form.suffix" />
                </div>
            </div>

            <!-- Org/Position/Salary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">
                <div>
                    <Label for="sex" class="text-xs md:text-sm">Sex</Label>
                    <select id="sex" v-model="form.sex" class="w-full p-2 border rounded">
                        <option value="" disabled>Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <span v-if="errors.sex" class="text-red-600 text-sm">{{ errors.sex }}</span>
                </div>
                <div class="relative">
                    <Label for="div_sec_unit" class="text-xs md:text-sm">Div Sec Unit</Label>
                    <select id="div_sec_unit" v-model="form.div_sec_unit" class="w-full p-2 border rounded" :key="orgUnitsLocal.length">
                        <option value="" disabled>Select Division Section Unit</option>
                        <option v-for="unit in orgUnitsLocal" :key="unit.value" :value="unit.value">
                            {{ unit.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openModal('orgUnit')" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.div_sec_unit" class="text-red-600 text-sm">{{ errors.div_sec_unit }}</span>
                </div>
                <div class="relative">
    <Label for="position_name" class="text-xs md:text-sm">Position <span class="text-gray-500 text-xs">(Vacant only)</span></Label>
    <select 
        id="position_name" 
        v-model="form.position_name" 
        class="w-full p-2 border rounded" 
        :key="vacantPositions.length"
    >
        <option value="" disabled>Select Position</option>
        <option v-for="pos in vacantPositions" :key="pos.value" :value="pos.value">
            {{ pos.item_code }} - {{ pos.label.replace(' (Occupied)', '') }}
        </option>
    </select>
    <Button 
        type="button" 
        @click="openModal('position')" 
        class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white"
    >
        +
    </Button>
    <span v-if="errors.position_name" class="text-red-600 text-sm">{{ errors.position_name }}</span>
</div>
                <div class="relative">
                    <Label for="salary_grade" class="text-xs md:text-sm">Salary Grade</Label>
                    <select id="salary_grade" v-model="form.salary_grade" disabled class="w-full p-2 border rounded">
                        <option v-for="opt in salaryGradeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openSalaryGradeModal" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                </div>
            </div>

            <!-- Status/Assignment/Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">
    <div>
    <!-- Mobile: Emp Status -->
    <Label for="emp_status" class="text-xs md:hidden">Emp Status</Label>

    <!-- Desktop (md+): Employee Status -->
    <Label for="emp_status" class="hidden md:block md:text-sm">Employee Status</Label>

    <select id="emp_status" v-model="form.emp_status" class="w-full p-2 border rounded">
        <option value="" disabled>Select Employee Status</option>
        <option v-for="status in employeeStatuses" :key="status.value" :value="status.value">
            {{ status.label }}
        </option>
    </select>

    <span v-if="errors.emp_status" class="text-red-600 text-sm">{{ errors.emp_status }}</span>
</div>

                <div class="relative">
                    <Label for="assignment_name" class="text-xs md:text-sm">Assignment</Label>
                    <select id="assignment_name" v-model="form.assignment_name" class="w-full p-2 border rounded" :key="assignmentOptions.length">
                        <option value="" disabled>Select Assignment</option>
                        <option v-for="opt in assignmentOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="addAssignment" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.assignment_name" class="text-red-600 text-sm">{{ errors.assignment_name }}</span>
                </div>
                <div>
                    <Label for="email" class="text-xs md:text-sm">Email</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <span v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</span>
                </div>
                                <div>
                    <Label for="contact_no" class="text-xs md:text-sm">Contact No.</Label>
                    <Input id="contact_no" v-model="form.contact_no" />
                    <span v-if="errors.contact_no" class="text-red-600 text-sm">{{ errors.contact_no }}</span>
                </div>
            </div>

            <!-- Additional Info Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="date_of_birth" class="text-xs md:text-sm">Date of Birth</Label>
                    <Input id="date_of_birth" type="date" class="rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" v-model="form.date_of_birth" />
                    <span v-if="errors.date_of_birth" class="text-red-600 text-sm">{{ errors.date_of_birth }}</span>
                </div>
                <div>
                    <Label for="tin_no" class="text-xs md:text-sm">TIN No</Label>
                    <Input id="tin_no" v-model="form.tin_no" />
                    <span v-if="errors.tin_no" class="text-red-600 text-sm">{{ errors.tin_no }}</span>
                </div>
                                <div>
                    <Label for="civil_service" class="text-xs md:text-sm">Civil Service</Label>
                    <Input id="civil_service" v-model="form.civil_service" />
                    <span v-if="errors.civil_service" class="text-red-600 text-sm">{{ errors.civil_service }}</span>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2  gap-4 text-black">               
                <div>
                    <Label for="date_appointment" class="text-xs md:text-sm">Date of Appointment</Label>
                    <Input id="date_appointment" type="date" class="rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" v-model="form.date_appointment" />
                    <span v-if="errors.date_appointment" class="text-red-600 text-sm">{{ errors.date_appointment }}</span>
                </div>
                <div>
                    <Label for="date_last_promotion" class="text-xs md:text-sm">Date of Last Promotion</Label>
                    <Input id="date_last_promotion" type="date" class="rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" v-model="form.date_last_promotion" />
                    <span v-if="errors.date_last_promotion" class="text-red-600 text-sm">{{ errors.date_last_promotion }}</span>
                </div>
</div><div class="gap-4 text-black">
                <div class="col-span-2">
                    <Label for="education" class="text-xs md:text-sm">Education</Label>
                    <textarea id="education" v-model="form.education" class="w-full p-2 border rounded-md"></textarea>
                    <span v-if="errors.education" class="text-red-600 text-sm">{{ errors.education }}</span>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-black">
                <div>
                    <Label for="password" class="text-xs md:text-sm">Password</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <span v-if="errors.password" class="text-red-600 text-sm">{{ errors.password }}</span>
                </div>
                <div>
                    <Label for="password_confirmation" class="text-xs md:text-sm">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                    <span v-if="errors.password_confirmation" class="text-red-600 text-sm">{{ errors.password_confirmation }}</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3">
                <Button type="button" @click="props.close" class="bg-gray-200 text-gray-800 hover:bg-gray-300">
                    Cancel
                </Button>
                <Button type="submit" class="bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
                    Submit
                </Button>
            </div>
        </form>

        <!-- Modal for Additional Forms -->
        <Modal v-if="showModal" :close="closeModal">
            <template #header>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{
                        modalContent === 'orgUnit' ? 'Add Division Section Unit' :
                        modalContent === 'salaryGrade' ? 'Update Salary Grade' :
                        modalContent === 'assignmentPlace' ? 'Add Assignment Place' :
                        'Add Position'
                    }}
                </h2>
            </template>
            <template #body>
                <OrgUnitForm v-if="modalContent === 'orgUnit'" @add-org-unit="addOrgUnit" @close="closeModal" />
                <SalaryGradeForm
                    v-if="modalContent === 'salaryGrade'"
                    :positions="positionsLocal"
                    :preselectedItemCode="form.position_name"
                    @store-salary-grade="addSalaryGrade"
                    @close="closeModal"
                />
                <AssignmentPlaces v-if="modalContent === 'assignmentPlace'" @add-assignment-place="addAssignmentPlace" @close="closeModal" />
                <PositionForm v-if="modalContent === 'position'" @add-position="addPosition" @close="closeModal" />
            </template>
        </Modal>
    </div>
</template>

<style scoped>
select, input, textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: #fff;
}
textarea {
    min-height: 100px;
}
.relative .absolute {
    top: 2.5rem;
    right: 0.05rem;
    transform: translateY(-50%);
    height: 2.5rem;
}

@media (max-width: 768px) {
    .relative .absolute {
        top: 2rem; /* adjust this value for mobile */
        height: 2rem;
    }

  

form{
        max-height: 90vh;
    overflow-y: auto;
    padding-right: 0.5rem;
}


}

</style>
