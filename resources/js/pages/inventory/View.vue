<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Asset {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: number;
    condition: string;
    assigned_to: string;
    employee_id: number | null;
    status: string;
    qr_code: string | null;
    image: string | null;
    property_no: string;
    serial_no: string;
    serviceable: string;
    unserviceable: string;
    unit_qty: number;
    coa_representative: string;
    coa_date: string;
    assigned_date: string;
}

const props = defineProps<{
    asset?: Asset;
}>();

console.log('View.vue props:', props.asset); // Debug the asset prop

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/inventory' },
    { title: 'View Asset', href: '#' },
];

const downloadQrCode = () => {
    if (!props.asset?.qr_code) return;
    const link = document.createElement('a');
    link.href = props.asset.qr_code;
    link.download = `asset_${props.asset.id}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const imageError = ref(false);

const handleImageError = () => {
    console.error('Failed to load image:', props.asset?.image);
    imageError.value = true;
};

const handleImageLoad = () => {
    console.log('Image loaded successfully:', props.asset?.image);
    imageError.value = false;
};
</script>

<template>
    <Head :title="asset ? `Asset ${asset.id}` : 'Asset Not Found'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <h2 class="mb-4 text-2xl font-semibold text-gray-900">Asset Details</h2>
            <div v-if="asset" class="rounded-lg bg-white p-6 shadow-md">
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    <div>
                        <p>
                            <strong>Asset ID:</strong> <span class="md:text-md text-sm text-black">{{ asset.id }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Name:</strong> <span class="md:text-md text-sm text-black">{{ asset.name }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Category:</strong> <span class="md:text-md text-sm text-black">{{ asset.category }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Location:</strong> <span class="md:text-md text-sm text-black">{{ asset.location }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Purchase Date:</strong> <span class="md:text-md text-sm text-black">{{ asset.purchase_date }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Property No:</strong> <span class="md:text-md text-sm text-black">{{ asset.property_no }}</span>
                        </p>
                    </div>
                    <div>
                        <p>
                            <strong>Serial No:</strong> <span class="md:text-md text-sm text-black">{{ asset.serial_no }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Serviceable:</strong> <span class="md:text-md text-sm text-black">{{ asset.serviceable }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Value:</strong> <span class="md:text-md text-sm text-black">₱{{ asset.value.toFixed(2) }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Condition:</strong> <span class="md:text-md text-sm text-black">{{ asset.condition }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Assigned To:</strong> <span class="md:text-md text-sm text-black">{{ asset.assigned_to }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Status:</strong> <span class="md:text-md text-sm text-black">{{ asset.status }}</span>
                        </p>
                    </div>
                    <div>
                        <p>
                            <strong>Unserviceable:</strong> <span class="md:text-md text-sm text-black">{{ asset.unserviceable }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Unit Quantity:</strong> <span class="md:text-md text-sm text-black">{{ asset.unit_qty }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>COA Representative:</strong> <span class="md:text-md text-sm text-black">{{ asset.coa_representative }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>COA Date:</strong> <span class="md:text-md text-sm text-black">{{ asset.coa_date }}</span>
                        </p>
                        <p class="mt-3">
                            <strong>Assigned Date:</strong> <span class="md:text-md text-sm text-black">{{ asset.assigned_date }}</span>
                        </p>
                    </div>
                </div>
                <div class="grid_cols-1 mt-10 grid sm:grid-cols-1 md:grid-cols-2">
                    <div v-if="asset.qr_code" class="mt-20 text-center">
                        <h3 class="text-lg font-semibold">QR Code</h3>
                        <img :src="asset.qr_code" alt="Asset QR Code" class="mx-auto mt-2 h-72 w-72 object-contain sm:h-48 sm:w-48 md:h-72 md:w-72" />

                        <Button @click="downloadQrCode" class="mt-2 bg-blue-600 text-white hover:bg-blue-700"> Download QR Code </Button>
                    </div>
                    <div v-else class="mt-4 text-center text-red-600">
                        <p>Failed to generate QR code. Please try again.</p>
                    </div>
                    <div v-if="asset.image && !imageError" class="mt-4 text-center">
                        <h3 class="text-lg font-semibold">Asset Image</h3>
                        <img
                            :src="asset.image"
                            alt="Asset Image"
                            class="mx-auto mt-2 h-auto w-60 object-contain transition-all duration-300 sm:w-64 md:w-96 lg:w-[700px]"
                            @error="handleImageError"
                            @load="handleImageLoad"
                        />
                    </div>
                    <div v-else-if="asset.image && imageError" class="mt-4 text-center text-red-600">
                        <p>Failed to load image. Please check the image path or try again.</p>
                    </div>
                </div>
            </div>
            <div v-else class="relative rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Asset not found or failed to load.</span>
            </div>
        </div>
    </AppLayout>
</template>
