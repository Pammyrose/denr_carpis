<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

interface InventoryItem {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: number;
    condition: string;
    property_no: string;
    serial_no: string;
    serviceable: number;
    unserviceable: number;
    coa_representative: string;
    coa_date: string;
    assigned_to: number | null;
    full_name: string;
    assigned_date: string;
    unit_qty: number;
    status: string;
    return_date: string;
}

interface Employee {
    id: number;
    full_name: string;
}

const props = defineProps<{
    item: InventoryItem;
    employees: Employee[];
}>();

const form = useForm({
    name: props.item.name?.trim() || 'Unknown Item',
    category: props.item.category?.trim() || 'Uncategorized',
    location: props.item.location?.trim() || 'Unknown Location',
    purchase_date: props.item.purchase_date || '',
    value: props.item.value || 0,
    condition: props.item.condition || 'New',
    property_no: props.item.property_no?.trim() || '',
    serial_no: props.item.serial_no?.trim() || '',
    serviceable: props.item.serviceable || 0,
    unserviceable: props.item.unserviceable || 0,
    coa_representative: props.item.coa_representative?.trim() || '',
    coa_date: props.item.coa_date || '',
    assigned_to: props.item.assigned_to ? Number(props.item.assigned_to) : null,
    assigned_date: props.item.assigned_date || '',
    unit_qty: props.item.unit_qty || 1,
    status: props.item.status || 'Good',
    return_date: props.item.return_date || '',
});

const errors = ref<{ [key: string]: string }>(props.errors ? Object.fromEntries(
    Object.entries(props.errors).map(([key, value]) => [key, value.join('; ')])
) : {});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

onMounted(() => {
    console.log('Edit.vue props on mount:', {
        item: props.item,
        employees: props.employees,
        errors: props.errors,
    });
    console.log('Initial form data:', form.data());
    console.log('Current full_name:', props.item.full_name);

    if (!props.item || !props.item.id) {
        alertMessage.value = 'Error: No item data provided.';
        alertType.value = 'error';
    }

    if (props.errors && Object.keys(props.errors).length > 0) {
        alertMessage.value = 'Please correct the form errors.';
        alertType.value = 'error';
    }
});

const submit = () => {
    console.log('Submit button clicked');
    console.log('Form data before submission:', form.data());

    form.put(route('inventory.update', { inventory: props.item.id }), {
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
            console.log('Starting form submission');
        },
        onSuccess: () => {
            console.log('Form submitted successfully');
            alertMessage.value = 'Inventory item updated successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                router.visit(route('inventory.index'), {
                    method: 'get',
                    preserveState: false,
                    onSuccess: () => {
                        console.log('Redirected to inventory.index');
                    },
                });
            }, 1500);
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = Object.fromEntries(
                Object.entries(serverErrors).map(([key, value]) => [key, Array.isArray(value) ? value.join('; ') : value])
            );
            alertMessage.value = 'Failed to update: ' + (serverErrors.general || Object.values(serverErrors).join('; '));
            alertType.value = 'error';
        },
        onFinish: () => {
            console.log('Request finished, errors:', errors.value);
        },
    });
};
</script>

<template>
    <div class="relative max-w-6xl mx-auto p-6">
        <div
            v-if="alertMessage && alertType"
            class="mb-4 p-4 rounded-r-md border-l-4"
            :class="{
                'bg-green-100 border-green-400 text-green-700': alertType === 'success',
                'bg-red-100 border-red-400 text-red-700': alertType === 'error'
            }"
            role="alert"
        >
            <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
            <p>{{ alertMessage }}</p>
            <ul v-if="Object.keys(errors).length > 0" class="mt-2 list-disc list-inside">
                <li v-for="(error, field) in errors" :key="field" class="capitalize">{{ field.replace('_', ' ')}}: {{ error }}</li>
            </ul>
        </div>
        <pre v-if="Object.keys(errors).length > 0" class="text-red-600">Errors: {{ JSON.stringify(errors, null, 2) }}</pre>

        <form @submit.prevent="submit" class="space-y-4">
            <h2 class="text-lg font-semibold text-black">Update Inventory Item</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="name" class="text-xs md:text-sm">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.name }"
                    />
                    <span v-if="errors.name" class="text-red-600 text-sm">{{ errors.name }}</span>
                </div>
                <div>
                    <Label for="category" class="text-xs md:text-sm">Category</Label>
                    <Input
                        id="category"
                        v-model="form.category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.category }"
                    />
                    <span v-if="errors.category" class="text-red-600 text-sm">{{ errors.category }}</span>
                </div>
                <div>
                    <Label for="location" class="text-xs md:text-sm">Location</Label>
                    <select
                        id="location"
                        v-model="form.location"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Location</option>
                        <option value="PMD">PMD</option>
                        <option value="Finance">Finance</option>
                        <option value="Admin">Admin</option>
                        <option value="Legal">Legal</option>
                        <option value="CDD">CDD</option>
                        <option value="SAM">SAM</option>
                        <option value="LPD">LPD</option>
                        <option value="Enforcement">Enforcement</option>
                        <option value="Technical">Technical</option>
                        <option value="MSD">MSD</option>
                    </select>
                    <span v-if="errors.location" class="text-red-600 text-sm">{{ errors.location }}</span>
                </div>
                <div>
                    <Label for="purchase_date" class="text-xs md:text-sm">Purchase Date</Label>
                    <Input
                        id="purchase_date"
                        v-model="form.purchase_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.purchase_date }"
                    />
                    <span v-if="errors.purchase_date" class="text-red-600 text-sm">{{ errors.purchase_date }}</span>
                </div>
                <div>
                    <Label for="value" class="text-xs md:text-sm">Value</Label>
                    <Input
                        id="value"
                        v-model.number="form.value"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.value }"
                    />
                    <span v-if="errors.value" class="text-red-600 text-sm">{{ errors.value }}</span>
                </div>
                <div>
                    <Label for="condition" class="text-xs md:text-sm">Condition</Label>
                    <select
                        id="condition"
                        v-model="form.condition"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Condition</option>
                        <option value="New">New</option>
                        <option value="Old">Old</option>
                    </select>
                    <span v-if="errors.condition" class="text-red-600 text-sm">{{ errors.condition }}</span>
                </div>
                <div>
                                           <!-- Mobile: Emp Status -->
    <Label for="property_no" class="text-xs md:hidden">Property No</Label>

    <!-- Desktop (md+): Employee Status -->
    <Label for="property_no" class="hidden md:block md:text-sm">Property Number</Label>
                    <Input
                        id="property_no"
                        v-model="form.property_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.property_no }"
                    />
                    <span v-if="errors.property_no" class="text-red-600 text-sm">{{ errors.property_no }}</span>
                </div>
                <div>
                    <Label for="serial_no" class="text-xs md:text-sm">Serial Number</Label>
                    <Input
                        id="serial_no"
                        v-model="form.serial_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.serial_no }"
                    />
                    <span v-if="errors.serial_no" class="text-red-600 text-sm">{{ errors.serial_no }}</span>
                </div>
                <div>
                    <Label for="serviceable" class="text-xs md:text-sm">Serviceable</Label>
                    <Input
                        id="serviceable"
                        v-model.number="form.serviceable"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.serviceable }"
                    />
                    <span v-if="errors.serviceable" class="text-red-600 text-sm">{{ errors.serviceable }}</span>
                </div>
                <div>
                    <Label for="unserviceable" class="text-xs md:text-sm">Unserviceable</Label>
                    <Input
                        id="unserviceable"
                        v-model.number="form.unserviceable"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.unserviceable }"
                    />
                    <span v-if="errors.unserviceable" class="text-red-600 text-sm">{{ errors.unserviceable }}</span>
                </div>
                <div>
              

                                                               <!-- Mobile -->
    <Label for="coa_representative" class="text-xs md:hidden">COA Rep</Label>

    <!-- Desktop (md+) -->
    <Label for="coa_representative" class="hidden md:block md:text-sm">COA Representative</Label>
                    <Input
                        id="coa_representative"
                        v-model="form.coa_representative"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.coa_representative }"
                    />
                    <span v-if="errors.coa_representative" class="text-red-600 text-sm">{{ errors.coa_representative }}</span>
                </div>
                <div>
                    <Label for="coa_date" class="text-xs md:text-sm">COA Date</Label>

                    
                    <Input
                        id="coa_date"
                        v-model="form.coa_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.coa_date }"
                    />
                    <span v-if="errors.coa_date" class="text-red-600 text-sm">{{ errors.coa_date }}</span>
                </div>
                <div>
                    <Label for="assigned_to" class="text-xs md:text-sm">Assigned To</Label>
                    <select
                        id="assigned_to"
                        v-model="form.assigned_to"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option :value="null">Unassigned</option>
                        <option v-for="employee in props.employees" :key="employee.id" :value="employee.id">
                            {{ employee.full_name }}
                        </option>
                    </select>
                    <span v-if="errors.assigned_to" class="text-red-600 text-sm">{{ errors.assigned_to }}</span>
                </div>
                <div>
                    <Label for="assigned_date" class="text-xs md:text-sm">Assigned Date</Label>
                    <Input
                        id="assigned_date"
                        v-model="form.assigned_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.assigned_date }"
                    />
                    <span v-if="errors.assigned_date" class="text-red-600 text-sm">{{ errors.assigned_date }}</span>
                </div>
                <div>
                    <Label for="unit_qty" class="text-xs md:text-sm">Unit Quantity</Label>
                    <Input
                        id="unit_qty"
                        v-model.number="form.unit_qty"
                        type="number"
                        min="1"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.unit_qty }"
                    />
                    <span v-if="errors.unit_qty" class="text-red-600 text-sm">{{ errors.unit_qty }}</span>
                </div>
                <div>
                    <Label for="status" class="text-xs md:text-sm">Status</Label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.status }"
                    >
                        <option value="Good">Good</option>
                        <option value="Check">Check</option>
                        <option value="Repair">Repair</option>
                        <option value="Upgrade">Upgrade</option>
                    </select>
                    <span v-if="errors.status" class="text-red-600 text-sm">{{ errors.status }}</span>
                </div>
                <div>
                    <Label for="return_date" class="text-xs md:text-sm">Return Date</Label>
                    <Input
                        id="return_date"
                        v-model="form.return_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.return_date }"
                    />
                    <span v-if="errors.return_date" class="text-red-600 text-sm">{{ errors.return_date }}</span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    @click="router.visit(route('inventory.index'))"
                    class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ form.processing ? 'Updating...' : 'Update' }}
                </Button>
            </div>
        </form>
    </div>
</template>
<style>
@media (max-width: 768px) {
 

form{
        max-height: 90vh;
    overflow-y: auto;
    padding-right: 0.5rem;
}


}
</style>