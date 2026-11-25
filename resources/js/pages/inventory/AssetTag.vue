<template>
    <div class="flex min-h-screen items-center justify-center">
        <div v-if="asset" class="mx-auto max-w-5xl border-20 border-red-700 bg-white shadow-lg">
            <!-- Top Text with Green Background -->
            <div class="text-s bg-red-700 py-2 text-center font-bold tracking-wide text-white uppercase">"COA" CIRCULAR NO. 80-124 JAN. 18, 1980</div>
            <div class="p-2">
                <!-- Header: COA Circular and Title -->
                <div class="mb-8 flex items-center justify-start text-center">
                    <img src="/rnp.png" alt="Logo" class="ml-5 h-20 w-20" @error="imageError" @load="imageLoaded" />
                    <div class="flex-1 text-center">
                        <h1 class="text-lg font-bold tracking-wide text-green-800">Republic of the Philippines</h1>
                        <p class="text-base font-bold text-gray-900">GOVERNMENT PROPERTY</p>
                        <span class="border-b border-black px-11">{{ asset.location }}</span>
                        <p class="text-xs">Office / Location</p>
                    </div>
                    <div class="-mt-14">
                        No.<span class="border-b border-black px-2">{{ asset.id }}</span>
                        <p class="text-xs">Inventory Tag</p>
                    </div>
                </div>

                <!-- Main Fields Grid -->
                <div class="grid grid-cols-1">
                    <div class="flex justify-start">
                        <span class="font-semibold">Asset:</span>
                        <span class="border-b border-black px-55">{{ asset.name }}</span>
                    </div>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-10">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">Property No.</span>
                            <span class="border-b border-black px-11">{{ asset.property_no }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Serviceable</span>
                            <span class="border-b border-black px-12">{{ asset.serviceable }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Unit / Quantity</span>
                            <span class="border-b border-black px-12">{{ asset.unit_qty }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">Serial No</span>
                            <span class="border-b border-black px-12">{{ asset.serial_no }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Unserviceable</span>
                            <span class="border-b border-black px-6">{{ asset.unserviceable }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Acquisition Cost:</span>
                            <span class="border-b border-black px-6">₱{{ asset.value.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Assigned To and Status (Additional Info Below Tag) -->
                <div class="flex justify-between pt-4 pr-10 pl-10">
                    <div>
                        <span class="border-b border-black px-12">{{ asset.coa_date }}</span>
                        <p class="text-center text-xs">Date: (Acquired)</p>
                    </div>
                    <div>
                        <span class="border-b border-black px-12">{{ asset.assigned_date }}</span>
                        <p class="text-center text-xs">Date: (Acquired)</p>
                    </div>
                </div>
                <div class="flex justify-between gap-10 pt-4 pr-10 pl-10">
                    <div>
                        <span class="border-b border-black">{{ asset.coa_representative }}</span>
                        <p class="text-center text-xs">COA Representative</p>
                    </div>
                    <div>
                        <span class="border-b border-black">{{ asset.assigned_to }}</span>
                        <p class="text-center text-xs">Property Custodian</p>
                    </div>
                </div>
            </div>
            <!-- Bottom Text with Green Background -->
            <div class="pl-2 text-center text-xs tracking-wide text-black">See device for actual COA sticker</div>
            <div class="text-s mt-4 bg-red-700 pt-2 text-center font-bold tracking-wide text-white uppercase">
                TAMPERING THIS STICKER IS PUNISHABLE BY LAW
            </div>
        </div>

        <div v-else-if="error" class="text-center text-red-600">
            {{ error }}
        </div>

        <div v-else class="text-center text-gray-500">No asset data available.</div>
    </div>
</template>

<script setup lang="ts">
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
    status: string;
    property_no: string;
    serial_no: string;
    serviceable: string | number;
    unserviceable: string | number;
    unit_qty: string | number;
    coa_date: string;
    assigned_date: string;
    coa_representative: string;
    qr_code?: string;
}

defineProps<{
    asset?: Asset | null;
    error?: string | null;
}>();

const imageLoadError = ref(false);

const imageError = () => {
    imageLoadError.value = true;
    console.error('Logo image failed to load. Ensure denr_logo.png is in the /public folder at the project root.');
};

const imageLoaded = () => {
    console.log('Logo image loaded successfully.');
};
</script>
