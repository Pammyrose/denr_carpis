<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Modal from '@/pages/employee/Modal.vue';
import OrgUnitForm from '@/pages/employee/OrgUnitForm.vue';
import SalaryGradeForm from '@/pages/employee/SalaryGrade.vue';
import AssignmentPlaces from '@/pages/employee/AssignmentPlaces.vue';
import PositionForm from '@/pages/employee/PositionForm.vue';

interface Employee {
    id: number;
    first_name: string;
    middle_name: string;
    last_name: string;
    suffix: string;
    sex: string;
    email: string;
    contact_no: string;
    emp_status: string;
    position_name?: string;
    assignment_name?: string;
    div_sec_unit?: string;
    date_of_birth?: string;
    tin_no?: string;
    date_appointment?: string;
    date_last_promotion?: string;
    civil_service?: string;
    education?: string;
}

const props = defineProps<{
    employee: Employee;
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

const originalContactNo = ref(props.employee.contact_no || '');

onMounted(() => {
    console.log('Edit.vue: Initial props:', {
        employee: props.employee,
        orgUnits: props.orgUnits,
        positions: props.positions,
        assignmentPlaces: props.assignmentPlaces,
        empStatuses: props.empStatuses,
    });
});

const form = useForm({
    first_name: props.employee.first_name || '',
    middle_name: props.employee.middle_name || '',
    last_name: props.employee.last_name || '',
    suffix: props.employee.suffix || '',
    sex: props.employee.sex || '',
    email: props.employee.email || '',
    contact_no: props.employee.contact_no || '',
    password: '',
    password_confirmation: '',
    emp_status: props.employee.emp_status || '',
    position_id: props.employee.position_name ? String(props.employee.position_name) : '',
    assignment_id: props.employee.assignment_name ? String(props.employee.assignment_name) : '',
    org_unit_id: props.employee.div_sec_unit ? String(props.employee.div_sec_unit) : '',
    salary_grade: '',
    date_of_birth: props.employee.date_of_birth || '',
    tin_no: props.employee.tin_no || '',
    date_appointment: props.employee.date_appointment || '',
    date_last_promotion: props.employee.date_last_promotion || '',
    civil_service: props.employee.civil_service || '',
    education: props.employee.education || '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>(''); 
const showModal = ref(false);
const modalContent = ref<'orgUnit' | 'salaryGrade' | 'assignmentPlace' | 'position' | null>(null);

const salaryGradeOptions = ref<{ value: string; label: string }[]>([{ value: '', label: 'Select Salary Grade' }]);

// Watch for position_id changes to update salary grade
watch(
    () => form.position_id,
    (newPositionId) => {
        console.log('Edit.vue: Position changed:', newPositionId);
        const selectedPosition = positionsLocal.value.find((p) => p.value === newPositionId);
        salaryGradeOptions.value = selectedPosition?.salary_grade
            ? [{ value: selectedPosition.salary_grade, label: selectedPosition.salary_grade }]
            : [{ value: '', label: 'Select Salary Grade' }];
        form.salary_grade = selectedPosition?.salary_grade || '';
    }
);

// Debug watchers for form fields
watch(
    () => form.position_id,
    (newValue) => {
        console.log('form.position_id updated to:', newValue, 'Positions available:', positionsLocal.value);
    }
);

watch(
    () => form.assignment_id,
    (newValue) => {
        console.log('form.assignment_id updated to:', newValue, 'Assignments available:', assignmentOptions.value);
    }
);

const validate = () => {
    errors.value = {};
    if (!form.first_name) errors.value.first_name = 'First name is required';
    if (!form.last_name) errors.value.last_name = 'Last name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.contact_no) errors.value.contact_no = 'Contact No is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    if (!form.emp_status) errors.value.emp_status = 'Employee status is required';
    if (!form.position_id) errors.value.position_id = 'Position is required';
    if (!form.assignment_id) errors.value.assignment_id = 'Assignment is required';
    if (!form.org_unit_id) errors.value.org_unit_id = 'Division Section Unit is required';
    if (form.password && form.password !== form.password_confirmation)
        errors.value.password_confirmation = 'Passwords do not match';
    if (!form.date_of_birth) errors.value.date_of_birth = 'Date of birth is required';
    if (!form.contact_no) {
        errors.value.contact_no = 'Contact No is required';
    } else if (form.contact_no !== originalContactNo.value) {
        if (!/^\d{10,15}$/.test(form.contact_no)) {
            errors.value.contact_no = 'Contact No must be 10 to 15 digits';
        }
    }
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    console.log('Edit.vue: Submitting form with data:', form.data());
    alertMessage.value = ''; 
    alertType.value = '';
    if (!validate()) {
        console.log('Edit.vue: Validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }
    form.put(route('employee.update', { id: props.employee.id }), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Edit.vue: Form submitted successfully');
            alertMessage.value = 'Employee updated successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                props.close();
            }, 2000); 
        },
        onError: (serverErrors) => {
            console.log('Edit.vue: Server errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to update employee';
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
    console.log('Edit.vue: Modal closed, current form state:', form.data());
};

const addOrgUnit = async (newOrgUnit: { value: string; label: string }) => {
    if (!orgUnitsLocal.value.find((unit) => unit.value === newOrgUnit.value)) {
        orgUnitsLocal.value = [...orgUnitsLocal.value, newOrgUnit];
    }
    form.org_unit_id = newOrgUnit.value;
    console.log('Added org unit:', newOrgUnit, 'form.org_unit_id:', form.org_unit_id, 'orgUnitsLocal:', orgUnitsLocal.value);
    await nextTick();
    const orgUnitSelect = document.getElementById('org_unit_id') as HTMLSelectElement;
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
    if (form.position_id === updatedPosition.value) {
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
    form.assignment_id = newAssignmentPlace.value;
    console.log('Added assignment place:', newAssignmentPlace, 'form.assignment_id:', form.assignment_id, 'assignmentOptions:', assignmentOptions.value);
    await nextTick();
    const assignmentSelect = document.getElementById('assignment_id') as HTMLSelectElement;
    if (assignmentSelect) assignmentSelect.focus();
    closeModal();
};

const addPosition = async (newPosition: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }) => {
    if (!positionsLocal.value.find((pos) => pos.value === newPosition.value)) {
        positionsLocal.value = [...positionsLocal.value, newPosition];
    }
    form.position_id = '';
    form.position_id = newPosition.value;
    console.log('Added position:', newPosition, 'form.position_id:', form.position_id, 'positionsLocal:', positionsLocal.value);
    await nextTick();
    const positionSelect = document.getElementById('position_id') as HTMLSelectElement;
    if (positionSelect) positionSelect.focus();
    closeModal();
};

const openSalaryGradeModal = () => {
    if (!form.position_id) {
        errors.value.position_id = 'Please select a position first';
        alertMessage.value = errors.value.position_id;
        alertType.value = 'error';
        return;
    }
    openModal('salaryGrade');
};

const addAssignment = () => {
    openModal('assignmentPlace');
};
</script>

<template>
    <div class="relative">
        <!-- Alert -->
        <div
            v-if="alertMessage"
            :class="{
                'bg-green-100 border-l-4 border-green-400 text-green-700': alertType === 'success',
                'bg-red-100 border-l-4 border-red-400 text-red-700': alertType === 'error',
            }"
            class="p-4 rounded-r-md mb-4"
            role="alert"
        >
            <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
            <p>{{ alertMessage }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Update Employee</h2>

            <!-- Name Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">
                <div>
                    <Label for="first_name" class="text-xs md:text-sm">First Name</Label>
                    <Input
                        id="first_name"
                        v-model="form.first_name"
                    />
                    <span v-if="errors.first_name" class="text-red-600 text-sm">{{ errors.first_name }}</span>
                </div>
                <div>
                    <Label for="middle_name" class="text-xs md:text-sm">Middle Name</Label>
                    <Input
                        id="middle_name"
                        v-model="form.middle_name"
                    />
                </div>
                <div>
                    <Label for="last_name" class="text-xs md:text-sm">Last Name</Label>
                    <Input
                        id="last_name"
                        v-model="form.last_name"
                       
                    />
                    <span v-if="errors.last_name" class="text-red-600 text-sm">{{ errors.last_name }}</span>
                </div>
                                <div>
                    <Label for="suffix" class="text-xs md:text-sm">Suffix</Label>
                    <Input
                        id="suffix"
                        v-model="form.suffix"
                      
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">

                <div>
                    <Label for="sex" class="text-xs md:text-sm">Sex</Label>
                    <select
                        id="sex"
                        v-model="form.sex"
                       
                    >
                        <option value="" disabled>Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <span v-if="errors.sex" class="text-red-600 text-sm">{{ errors.sex }}</span>
                </div>
                <div class="relative">
                    <Label for="org_unit_id" class="text-xs md:text-sm">Div Sec Unit</Label>
                    <select
                        id="org_unit_id"
                        v-model="form.org_unit_id"
                      
                        :key="orgUnitsLocal.length"
                    >
                        <option value="" disabled>Select Division Section Unit</option>
                        <option v-for="unit in orgUnitsLocal" :key="unit.value" :value="unit.value">
                            {{ unit.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openModal('orgUnit')" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.org_unit_id" class="text-red-600 text-sm">{{ errors.org_unit_id }}</span>
                </div>
                                <div class="relative">
                    <Label for="position_id" class="text-xs md:text-sm">Position</Label>
                    <select
                        id="position_id"
                        v-model="form.position_id"
                       
                        :key="positionsLocal.length"
                    >
                        <option value="" disabled>Select Position</option>
                        <option v-for="pos in positionsLocal" :key="pos.value" :value="pos.value">
                            {{ pos.item_code }} - {{ pos.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openModal('position')" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.position_id" class="text-red-600 text-sm">{{ errors.position_id }}</span>
                </div>
                <div class="relative">
                    <Label for="salary_grade" class="text-xs md:text-sm">Salary Grade</Label>
                    <select
                        id="salary_grade"
                        v-model="form.salary_grade"
                        disabled
                     
                    >
                        <option v-for="opt in salaryGradeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openSalaryGradeModal" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-black">

                <div>
                        <!-- Mobile: Emp Status -->
    <Label for="emp_status" class="text-xs md:hidden">Emp Status</Label>

    <!-- Desktop (md+): Employee Status -->
    <Label for="emp_status" class="hidden md:block md:text-sm">Employee Status</Label>

                    <select
                        id="emp_status"
                        v-model="form.emp_status"
                     
                    >
                        <option value="" disabled>Select Employee Status</option>
                        <option v-for="status in employeeStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                    <span v-if="errors.emp_status" class="text-red-600 text-sm">{{ errors.emp_status }}</span>
                </div>
                                <div class="relative">
                    <Label for="assignment_id" class="text-xs md:text-sm">Assignment</Label>
                    <select
                        id="assignment_id"
                        v-model="form.assignment_id"
                       
                        :key="assignmentOptions.length"
                    >
                        <option value="" disabled>Select Assignment</option>
                        <option v-for="opt in assignmentOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="addAssignment" class="absolute right-1 top-9 bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.assignment_id" class="text-red-600 text-sm">{{ errors.assignment_id }}</span>
                </div>
                <div>
                    <Label for="email" class="text-xs md:text-sm">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        
                    />
                    <span v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</span>
                </div>
                                                              <div>
                    <Label for="contact_no" class="text-xs md:text-sm">Contact No.</Label>
                    <Input id="contact_no" v-model="form.contact_no" />
                    <span v-if="errors.contact_no" class="text-red-600 text-sm">{{ errors.contact_no }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">

                <div>
                    <Label for="date_of_birth" class="text-xs md:text-sm">Date of Birth</Label>
                    <Input
                        id="date_of_birth"
                        type="date"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        v-model="form.date_of_birth"
                        
                    />
                    <span v-if="errors.date_of_birth" class="text-red-600 text-sm">{{ errors.date_of_birth }}</span>
                </div>
                                <div>
                    <Label for="tin_no" class="text-xs md:text-sm">TIN No</Label>
                    <Input
                        id="tin_no"
                        v-model="form.tin_no"
                       
                    />
                    <span v-if="errors.tin_no" class="text-red-600 text-sm">{{ errors.tin_no }}</span>
                </div>
                <div>
                    <Label for="civil_service" class="text-xs md:text-sm">Civil Service</Label>
                    <Input
                        id="civil_service"
                        v-model="form.civil_service"
                        
                    />
                    <span v-if="errors.civil_service" class="text-red-600 text-sm">{{ errors.civil_service }}</span>
                </div>
  
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-black">

                <div>
                    <Label for="date_appointment" class="text-xs md:text-sm">Date of Appointment</Label>
                    <Input
                        id="date_appointment"
                        type="date"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        v-model="form.date_appointment"
                       
                    />
                    <span v-if="errors.date_appointment" class="text-red-600 text-sm">{{ errors.date_appointment }}</span>
                </div>
                                <div>
                    <Label for="date_last_promotion" class="text-xs md:text-sm">Date of Last Promotion</Label>
                    <Input
                        id="date_last_promotion"
                        type="date"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        v-model="form.date_last_promotion"
                       
                    />
                    <span v-if="errors.date_last_promotion" class="text-red-600 text-sm">{{ errors.date_last_promotion }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 text-black">
              <div class="col-span-2">
                    <Label for="education" class="text-xs md:text-sm">Education</Label>
                    <textarea
                        id="education"
                        v-model="form.education"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    ></textarea>
                    <span v-if="errors.education" class="text-red-600 text-sm">{{ errors.education }}</span>
                </div>
  
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-black">
                <div>
                    <Label for="password" class="text-xs md:text-sm">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        
                    />
                    <span v-if="errors.password" class="text-red-600 text-sm">{{ errors.password }}</span>
                </div>
                <div>
                    <Label for="password_confirmation" class="text-xs md:text-sm">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                      
                    />
                    <span v-if="errors.password_confirmation" class="text-red-600 text-sm">{{ errors.password_confirmation }}</span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    @click="props.close"
                    class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    Update
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
                    :preselectedItemCode="form.position_id"
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