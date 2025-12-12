<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

interface Employee {
    id: number;
    full_name: string;
}

interface Props {
    closeModal: () => void;
    employees: Employee[];
    qrCode?: string;
    asset?: {
        id: string;
        name: string;
        category: string;
        location: string;
        purchase_date: string;
        value: number;
        condition: string;
        assigned_to: number | null;
        status: string;
        property_no: string;
        serial_no: string;
        serviceable: string;
        unserviceable: string;
        coa_representative: string;
        coa_date: string;
        assigned_date: string;
        image?: string;
        unit_qty?: string;
    };
    message?: string;
}

const props = defineProps<Props>();
const alertMessage = ref(props.message || '');
const alertType = ref<'success' | 'error' | ''>(props.message ? 'success' : '');

const form = useForm({
    name: '',
    category: '',
    location: '',
    purchase_date: '',
    value: '',
    condition: '',
    assigned: null,
    status: '',
    property_no: '',
    serial_no: '',
    serviceable: '',
    unserviceable: '',
    coa_representative: '',
    coa_date: '',
    assigned_date: '',
    image: null,
    unit_qty: '',
});

const errors = ref<{ [key: string]: string }>({});

const validate = () => {
    errors.value = {};
    if (!form.name) errors.value.name = 'Name is required';
    if (!form.category) errors.value.category = 'Category is required';
    if (!form.location) errors.value.location = 'Location is required';
    if (!form.purchase_date) errors.value.purchase_date = 'Purchase date is required';
    if (!form.value && form.value !== 0) errors.value.value = 'Value is required';
    if (!form.condition) errors.value.condition = 'Condition is required';
    if (!form.status) errors.value.status = 'Status is required';
    if (!form.property_no) errors.value.property_no = 'Property number is required';
    if (!form.serial_no) errors.value.serial_no = 'Serial number is required';
    if (!form.serviceable) errors.value.serviceable = 'Serviceable status is required';
    if (!form.unserviceable) errors.value.unserviceable = 'Unserviceable status is required';
    if (!form.coa_representative) errors.value.coa_representative = 'COA representative is required';
    if (!form.coa_date) errors.value.coa_date = 'COA date is required';
    if (!form.unit_qty) errors.value.unit_qty = 'Unit / Qty is required';
    if (!form.assigned_date) errors.value.assigned_date = 'Assigned date is required';
    if (form.assigned && !Number.isInteger(Number(form.assigned))) {
        errors.value.assigned = 'Assigned employee must be a valid ID';
    }
    if (form.image && !['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(form.image.type)) {
        errors.value.image = 'Image must be a valid image file (jpeg, png, jpg, gif)';
    }
    if (form.image && form.image.size > 2048 * 1024) {
        errors.value.image = 'Image size must not exceed 2MB';
    }
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    alertMessage.value = '';
    alertType.value = '';
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }

    console.log('Submitting form with data:', form.data());

    form.post(route('inventory.store'), {
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
            console.log('Starting form submission');
        },
        onSuccess: () => {
            alertMessage.value = 'Asset created successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                alertMessage.value = '';
                alertType.value = '';
                props.closeModal();
                router.visit(route('inventory.index'), {
                    method: 'get',
                    preserveState: false,
                });
            }, 3000);
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create asset';
            alertType.value = 'error';
        },
        onFinish: () => {
            console.log('Request finished, errors:', errors.value);
        },
    });
};

const downloadQrCode = () => {
    if (!props.qrCode) return;
    const link = document.createElement('a');
    link.href = props.qrCode;
    link.download = `asset_${props.asset?.id || 'qr'}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const imagePreview = ref<string | null>(null);
const onImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.image = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(input.files[0]);
    }
};
</script>

<template>
    <div>
        <!-- Success Message and QR Code -->
        <div v-if="alertType === 'success' && props.qrCode && props.asset" class="space-y-4">
            <div
                class="bg-green-100 border-l-4 border-green-400 text-green-700 p-4 rounded-r-md"
                role="alert"
            >
                <p class="font-medium">Success</p>
                <p>{{ alertMessage }}</p>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-semibold">Asset QR Code</h3>
                <img :src="props.qrCode" alt="Asset QR Code" class="mx-auto mt-2" style="width: 200px; height: 200px;" />
                <Button @click="downloadQrCode" class="mt-2 bg-blue-600 text-white hover:bg-blue-700">
                    Download QR Code
                </Button>
            </div>
            <div v-if="props.asset.image" class="text-center">
                <h3 class="text-lg font-semibold">Asset Image</h3>
                <img :src="props.asset.image" alt="Asset Image" class="mx-auto mt-2" style="max-width: 300px;" />
            </div>
        </div>

        <!-- Create Form -->
        <form v-else @submit.prevent="submit" class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Create Asset</h2>

            <div
                v-if="alertType === 'error' && alertMessage"
                class="bg-red-100 border-l-4 border-red-400 text-red-700 p-4 rounded-r-md"
                role="alert"
            >
                <p class="font-medium">Error</p>
                <p>{{ alertMessage }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="name" class="text-xs md:text-sm">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.name" class="text-red-600 text-sm">{{ errors.name }}</span>
                </div>
                <div>
                    <Label for="category" class="text-xs md:text-sm">Category</Label>
                    <Input
                        id="category"
                        v-model="form.category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="purchase_date" class="text-xs md:text-sm">Purchase Date</Label>
                    <Input
                        id="purchase_date"
                        type="date"
                        v-model="form.purchase_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.purchase_date" class="text-red-600 text-sm">{{ errors.purchase_date }}</span>
                </div>
                <div>
                    <Label for="value" class="text-xs md:text-sm">Value</Label>
                    <Input
                        id="value"
                        type="number"
                        v-model.number="form.value"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
              

                        <!-- Mobile: Emp Status -->
    <Label for="property_no" class="text-xs md:hidden">Property No</Label>

    <!-- Desktop (md+): Employee Status -->
    <Label for="property_no" class="hidden md:block md:text-sm">Property Number</Label>
                    <Input
                        id="property_no"
                        v-model="form.property_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.property_no" class="text-red-600 text-sm">{{ errors.property_no }}</span>
                </div>
                <div>
                    <Label for="serial_no" class="text-xs md:text-sm">Serial Number</Label>
                    <Input
                        id="serial_no"
                        v-model="form.serial_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.serial_no" class="text-red-600 text-sm">{{ errors.serial_no }}</span>
                </div>
                <div>
                    <Label for="serviceable" class="text-xs md:text-sm">Serviceable</Label>
                    <Input
                        id="serviceable"
                        v-model="form.serviceable"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.serviceable" class="text-red-600 text-sm">{{ errors.serviceable }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="unserviceable" class="text-xs md:text-sm">Unserviceable</Label>
                    <Input
                        id="unserviceable"
                        v-model="form.unserviceable"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.unserviceable" class="text-red-600 text-sm">{{ errors.unserviceable }}</span>
                </div>
                <div>
                    <Label for="coa_representative" class="text-xs md:text-sm">COA Representative</Label>
                    <Input
                        id="coa_representative"
                        v-model="form.coa_representative"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.coa_representative" class="text-red-600 text-sm">{{ errors.coa_representative }}</span>
                </div>
                <div>
                    <Label for="coa_date" class="text-xs md:text-sm">COA Date</Label>
                    <Input
                        id="coa_date"
                        type="date"
                        v-model="form.coa_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.coa_date" class="text-red-600 text-sm">{{ errors.coa_date }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-black">

                <div>
                    <Label for="assigned" class="text-xs md:text-sm">Assigned To</Label>
                    <select
                        id="assigned"
                        v-model="form.assigned"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option :value="null">Select Employee</option>
                        <option
                            v-for="employee in employees"
                            :key="employee.id"
                            :value="employee.id"
                        >
                            {{ employee.full_name }}
                        </option>
                    </select>
                    <span v-if="errors.assigned" class="text-red-600 text-sm">{{ errors.assigned }}</span>
                </div>
                                <div>
                    <Label for="assigned_date" class="text-xs md:text-sm">Assigned Date</Label>
                    <Input
                        id="assigned_date"
                        type="date"
                        v-model="form.assigned_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.assigned_date" class="text-red-600 text-sm">{{ errors.assigned_date }}</span>
                </div>
                                                <div>
                    <Label for="unit_qty" class="text-xs md:text-sm">Unit / Qty</Label>
                    <Input
                        id="unit_qty"
                        type="number"
                        v-model="form.unit_qty"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.assigned_date" class="text-red-600 text-sm">{{ errors.unit_qty }}</span>
                </div>
                <div>
                    <Label for="status" class="text-xs md:text-sm">Status</Label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Status</option>
                        <option value="Good">Good</option>
                        <option value="Check">Check</option>
                        <option value="Repair">Repair</option>
                        <option value="Upgrade">Upgrade</option>
                    </select>
                    <span v-if="errors.status" class="text-red-600 text-sm">{{ errors.status }}</span>
                </div>
                                <div>
                    <Label for="image" class="text-xs md:text-sm">Image</Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="onImageChange"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.image" class="text-red-600 text-sm">{{ errors.image }}</span>
                    <img
                        v-if="imagePreview"
                        :src="imagePreview"
                        alt="Image Preview"
                        class="mt-2 max-w-full h-auto"
                        style="max-width: 200px;"
                    />
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
                >
                    Submit
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