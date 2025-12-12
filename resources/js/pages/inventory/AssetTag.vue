<template>
    <div class="flex justify-center items-center min-h-screen">
    <div v-if="asset" class="max-w-5xl mx-auto  bg-white border-20 border-red-700 shadow-lg">
        <!-- Top Text with Green Background -->
        <div class="text-center py-2 bg-red-700 text-white font-bold uppercase tracking-wide text-s">
            "COA" CIRCULAR NO. 80-124 JAN. 18, 1980
        </div>
<div class="p-2">
        <!-- Header: COA Circular and Title -->
        <div class="text-center mb-8 flex items-center justify-start">
            <img src="/rnp.png" alt="Logo" class="w-20 h-20 ml-5" @error="imageError" @load="imageLoaded" />
            <div class="flex-1 text-center">
                <h1 class="text-lg font-bold text-green-800 tracking-wide">Republic of the Philippines</h1>
                <p class="text-base font-bold text-gray-900">GOVERNMENT PROPERTY</p>
                <span class="border-b px-11 border-black">{{ asset.location }}</span>
                <p class="text-xs">Office / Location</p>
            </div>
            <div class="-mt-14">
                                No.<span class="border-b px-2 border-black">{{ asset.id }}</span>
                <p class="text-xs">Inventory Tag</p>
            
            </div>
        </div>

        <!-- Main Fields Grid -->
         <div class="grid grid-cols-1">
                <div class="flex justify-start">
                    <span class="font-semibold">Asset:</span>
                    <span class="border-b px-55 border-black">{{ asset.name }}</span>
                </div>
         </div>
        <div class="grid grid-cols-2 gap-10 mt-2">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold">Property No.</span>
                    <span class="border-b px-11 border-black">{{ asset.property_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Serviceable</span>
                    <span class="border-b px-12 border-black">{{ asset.serviceable }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Unit / Quantity</span>
                    <span class="border-b px-12 border-black">{{ asset.unit_qty }}</span>
                </div>

            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold">Serial No</span>
                    <span class="border-b px-12 border-black">{{ asset.serial_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Unserviceable</span>
                    <span class="border-b px-6 border-black">{{ asset.unserviceable }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Acquisition Cost:</span>
                    <span class="border-b px-6 border-black">₱{{ asset.value.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                </div>
            </div>
        </div>



        <!-- Assigned To and Status (Additional Info Below Tag) -->
        <div class="flex justify-between pl-10 pr-10 pt-4">
            <div>
                <span class="border-b px-12 border-black">{{ asset.coa_date }}</span>
                <p class="text-xs text-center">Date: (Acquired)</p>
            </div>
            <div>
                <span class="border-b px-12 border-black">{{ asset.assigned_date }}</span>
                <p class="text-xs text-center">Date: (Acquired)</p>
            </div>

        </div>
                <div class="flex justify-between pl-10 pr-10 pt-4 gap-10">
            <div>
                <span class="border-b border-black">{{ asset.coa_representative }}</span>
                <p class="text-xs text-center">COA Representative</p>
            </div>
            <div>
                <span class="border-b border-black">{{ asset.assigned_to }}</span>
                <p class="text-xs text-center">Property Custodian</p>
            </div>

        </div>
        
        </div>
                <!-- Bottom Text with Green Background -->
                         <div class="pl-2  text-black text-xs tracking-wide text-center">
            See device for actual COA sticker
        </div>
        <div class="mt-4 pt-2 bg-red-700 text-white text-s font-bold uppercase tracking-wide text-center">
            TAMPERING THIS STICKER IS PUNISHABLE BY LAW
        </div>
    </div>

    <div v-else-if="error" class="text-center text-red-600">
        {{ error }}
    </div>

    <div v-else class="text-center text-gray-500">
        No asset data available.
    </div>
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