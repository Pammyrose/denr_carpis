<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Bar, Pie } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale);

const { props } = usePage();
const dashboardData = props.dashboardData as {
    employees: number;
    regularEmployees: number;
    filledPositions: number;
    vacantPositions: number;

    employeesByUnit: { name: string; value: number }[];
    employeesByGender: { name: string; value: number }[];

    filledPositionsByUnit: { name: string; value: number }[];
    vacantPositionsByUnit: { name: string; value: number }[];
    regularEmployeesByUnit: { name: string; value: number }[];

    // NEW: Accurate gender per unit from backend
    genderByUnitCategories: string[];
    genderByUnitSeries: { name: string; data: number[] }[];

    // NEW: Assignment-based
    assignments: string[];
    regularEmployeesByAssignment: Record<string, number>;
    filledPositionsByAssignment: Record<string, number>;
    filledPositionsByUnitByAssignment: Record<string, { name: string; value: number }[]>;
    genderByAssignmentCategories: string[];
    genderByAssignmentSeries: { name: string; data: number[] }[];
    genderByUnitByAssignment: Record<string, Record<string, { male: number; female: number }>>;
    regularEmployeesByUnitByAssignment: Record<string, { name: string; value: number }[]>;
    employeesByUnitByAssignment: Record<string, { name: string; value: number }[]>;
};

const assignments = computed(() => dashboardData.assignments ?? []);

// Filters
const assignmentFilter = ref<string>('');
const divisionFilter = ref<string>('');
const hiddenDivisions = ref<string[]>([]);
const isDarkMode = ref(false);

let darkModeObserver: MutationObserver | null = null;

onMounted(() => {
    const html = document.documentElement;
    isDarkMode.value = html.classList.contains('dark');

    darkModeObserver = new MutationObserver(() => {
        isDarkMode.value = html.classList.contains('dark');
    });

    darkModeObserver.observe(html, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
    darkModeObserver?.disconnect();
});

// Determine filtered units for Employees by Division pie chart
const filteredUnits = computed(() => {
    let units: { name: string; value: number }[] = [];

    if (assignmentFilter.value && divisionFilter.value) {
        // Intersection: only units in selected assignment AND matching selected division
        const byAssignment = dashboardData.employeesByUnitByAssignment[assignmentFilter.value] || [];
        units = byAssignment.filter((u) => u.name === divisionFilter.value);
    } else if (assignmentFilter.value) {
        // Only assignment selected
        units = dashboardData.employeesByUnitByAssignment[assignmentFilter.value] || [];
    } else if (divisionFilter.value) {
        // Only division selected
        const match = dashboardData.employeesByUnit.find((u) => u.name === divisionFilter.value);
        units = match ? [match] : [];
    } else {
        // No filter
        units = dashboardData.employeesByUnit;
    }

    return units;
});

// Divisions can also be toggled from the pie chart legend. Keep that
// visibility state available to the other dashboard calculations.
const visibleUnits = computed(() => filteredUnits.value.filter((unit) => !hiddenDivisions.value.includes(unit.name)));

// Total employees under current filters
const currentTotalEmployees = computed(() => {
    return visibleUnits.value.reduce((sum, u) => sum + u.value, 0);
});

// Cards
const selectedEmployees = computed(() => currentTotalEmployees.value);

const totalForVisibleDivisions = (
    totalsByDivision: { name: string; value: number }[],
    totalsByAssignment?: Record<string, { name: string; value: number }[]>,
) => {
    const scopedTotals = assignmentFilter.value && totalsByAssignment ? (totalsByAssignment[assignmentFilter.value] ?? []) : totalsByDivision;

    return visibleUnits.value.reduce((total, unit) => total + (scopedTotals.find((item) => item.name === unit.name)?.value ?? 0), 0);
};

const selectedRegular = computed(() =>
    totalForVisibleDivisions(dashboardData.regularEmployeesByUnit, dashboardData.regularEmployeesByUnitByAssignment),
);

const selectedFilled = computed(() => totalForVisibleDivisions(dashboardData.filledPositionsByUnit, dashboardData.filledPositionsByUnitByAssignment));

const selectedVacant = computed(() => {
    const visibleVacancies = totalForVisibleDivisions(dashboardData.vacantPositionsByUnit);

    if (assignmentFilter.value || divisionFilter.value) return visibleVacancies;

    // Preserve the exact organization-wide total at the All/All view. Legend
    // clicks subtract only vacancies belonging to the hidden divisions.
    const vacanciesInDisplayedDivisions = filteredUnits.value.reduce(
        (total, unit) => total + (dashboardData.vacantPositionsByUnit.find((item) => item.name === unit.name)?.value ?? 0),
        0,
    );

    return dashboardData.vacantPositions - (vacanciesInDisplayedDivisions - visibleVacancies);
});

// Unique divisions – now dynamic based on selected assignment
const uniqueSortedDivisions = computed(() => {
    let divisionNames: string[] = [];

    if (assignmentFilter.value) {
        // Show only divisions that exist within the selected assignment
        const unitsInAssignment = dashboardData.employeesByUnitByAssignment[assignmentFilter.value] || [];
        divisionNames = unitsInAssignment.map((u) => u.name).filter(Boolean);
    } else {
        // No assignment selected → show all divisions in the organization
        divisionNames = dashboardData.employeesByUnit.map((u) => u.name).filter(Boolean);
    }

    return Array.from(new Set(divisionNames)).sort();
});

const genderForDivision = (division: string) => {
    if (assignmentFilter.value) {
        return dashboardData.genderByUnitByAssignment?.[assignmentFilter.value]?.[division] ?? { male: 0, female: 0 };
    }

    const index = dashboardData.genderByUnitCategories.indexOf(division);

    return {
        male: index === -1 ? 0 : (dashboardData.genderByUnitSeries.find((series) => series.name === 'Male')?.data[index] ?? 0),
        female: index === -1 ? 0 : (dashboardData.genderByUnitSeries.find((series) => series.name === 'Female')?.data[index] ?? 0),
    };
};

// Gender data uses the same currently visible divisions as the pie chart.
const filteredGenderData = computed(() => {
    let male = 0;
    let female = 0;

    visibleUnits.value.forEach((unit) => {
        const counts = genderForDivision(unit.name);
        male += counts.male;
        female += counts.female;
    });

    return [
        { name: 'Male', value: male },
        { name: 'Female', value: female },
    ];
});

// Color function — 100% unchanged
const getUnitColor = (unit: string): string => {
    const palette: Record<string, string> = {
        'PLANNING AND MANAGEMENT DIVISION': '#3b82f6',
        'LICENSES, PATENTS AND DEEDS DIVISION': '#10b981',
        'FINANCE DIVISION': '#f59e0b',
        'ADMINISTRATIVE DIVISION': '#ef4444',
        'LEGAL DIVISION': '#8b5cf6',
        'CONSERVATION AND DEVELOPMENT DIVISION': '#ec4899',
        'SURVEYS AND MAPPING DIVISION': '#14b8a6',
        'ENFORCEMENT DIVISION': '#f97316',
        'OFFICE OF THE REGIONAL DIRECTOR': '#06b6d4',

        // === (PENRO & CENRO) ===
        'PENRO Abra': '#60a5fa', // blue-400
        'MSD-PENRO Abra': '#f9a8d4', // pink-300
        'TSD-PENRO Abra': '#34d399', // emerald-400

        'PENRO Apayao': '#34d399', // emerald-400
        'PENRO Benguet': '#fbbf24', // amber-400
        'PENRO Ifugao': '#f87171', // red-400
        'PENRO Kalinga': '#a78bfa', // violet-400
        'PENRO MT.Province': '#f472b6', // pink-400
        'CENRO Bangued': '#22d3ee', // cyan-400
        'CENRO Lagangilang': '#86efac', // green-300
        'CENRO Conner': '#c084fc', // purple-400
        'CENRO Calanasan': '#fdba74', // orange-300
        'CENRO Baguio': '#818cf8', // indigo-400
        'CENRO Buguias': '#f87171', // red-400 (re-used but distinct context)
        'CENRO Alfonso Lista': '#a7f3d0', // emerald-200
        'CENRO Lamut': '#fcd34d', // yellow-300
        'CENRO Tabuk': '#d8b4fe', // purple-300
        'CENRO Pinukpuk': '#fb923c', // orange-400
        'CENRO Paracelis': '#6ee7b7', // emerald-300
        'CENRO Sabangan': '#f9a8d4', // pink-300
        CTI: '#60a5fa', // blue-400
    };
    return palette[unit] || `hsl(${Math.random() * 360}, 70%, 60%)`;
};

// Employees by Unit Pie Chart
const chartDataEmployeesByUnit = computed(() => ({
    labels: filteredUnits.value.map((i) => i.name || 'Unknown'),
    datasets: [
        {
            label: 'Employees',
            data: filteredUnits.value.map((i) => i.value),
            backgroundColor: filteredUnits.value.map((i) => getUnitColor(i.name)),
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 12,
        },
    ],
}));

const chartOptionsEmployeesByUnit = computed(() => {
    const count = filteredUnits.value.length;

    let fontSize: number;
    let boxSize: number;
    let itemPadding: number;
    let legendPadding: number;
    let rightPadding: number;

    if (count === 1) {
        // ── Very prominent when only one division is selected ──
        fontSize = 24; // or try 22–24 if your container allows
        boxSize = 28;
        itemPadding = 12;
        legendPadding = 24;
        rightPadding = 50; // give pie more space because legend text is large
    } else if (count <= 10) {
        // ── Normal / readable range (2–10 items) ──
        fontSize = 20;
        boxSize = 20;
        itemPadding = 0;
        legendPadding = 0;
        rightPadding = 0;
    } else {
        // ── Many items (11+) → compact ──
        fontSize = 11; // or 10–12
        boxSize = 10;
        itemPadding = 7;
        legendPadding = 8;
        rightPadding = 0; // less space needed
    }

    return {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                position: 'right' as const,

                onClick: (_event: unknown, legendItem: { index?: number }, legend: any) => {
                    const index = legendItem.index;
                    if (index === undefined) return;

                    const division = filteredUnits.value[index]?.name;
                    if (!division) return;

                    legend.chart.toggleDataVisibility(index);
                    legend.chart.update();

                    hiddenDivisions.value = legend.chart.getDataVisibility(index)
                        ? hiddenDivisions.value.filter((name) => name !== division)
                        : [...new Set([...hiddenDivisions.value, division])];
                },

                labels: {
                    font: {
                        size: fontSize,
                        // weight: count === 1 ? '600' : 'normal',   // optional
                        family: 'Inter, system-ui, sans-serif',
                    },
                    padding: itemPadding, // space between legend items
                    boxWidth: boxSize,
                    boxHeight: boxSize,
                    usePointStyle: false,
                    color: isDarkMode.value ? '#fff' : '#000',
                },

                padding: legendPadding, // padding around the whole legend
            },

            tooltip: {
                callbacks: {
                    label: (ctx: any) => {
                        const total = currentTotalEmployees.value;
                        const percent = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                        return `${ctx.label}: ${ctx.raw} (${percent}%)`;
                    },
                },
            },
        },

        layout: {
            padding: {
                right: rightPadding,
            },
        },
    };
});

// Gender Bar Chart
const chartDataGender = computed(() => ({
    labels: ['Male', 'Female'],
    datasets: [
        {
            label: 'Number of Employees',
            data: [
                filteredGenderData.value.find((g) => g.name === 'Male')?.value || 0,
                filteredGenderData.value.find((g) => g.name === 'Female')?.value || 0,
            ],
            backgroundColor: ['#2563eb', '#ec4899'],
            borderRadius: 12,
            borderSkipped: false,
            barThickness: 80,
            maxBarThickness: 100,
        },
    ],
}));

const chartOptionsGender = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: any) => `${ctx.raw} employees`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { color: isDarkMode.value ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.05)' },
            ticks: {
                font: { size: 14 },
                stepSize: 10,
                color: isDarkMode.value ? '#fff' : '#000',
            },
        },
        x: {
            grid: { display: false },
            ticks: {
                font: { size: 16, weight: 'bold' as const },
                color: isDarkMode.value ? '#fff' : '#000',
            },
        },
    },
}));

// Auto-select division when assignment has exactly one division
watch(assignmentFilter, (newAssignment) => {
    if (newAssignment) {
        const unitsInAssignment = dashboardData.employeesByUnitByAssignment[newAssignment] || [];
        const validUnits = unitsInAssignment.map((u) => u.name).filter(Boolean);

        if (validUnits.length === 1) {
            divisionFilter.value = validUnits[0];
        } else {
            divisionFilter.value = '';
        }
    } else {
        // When "All Assignment Places" is selected, clear the division filter
        divisionFilter.value = '';
    }
});

// A filter change creates a new pie-chart context, so all of its divisions
// should start visible again.
watch([assignmentFilter, divisionFilter], () => {
    hiddenDivisions.value = [];
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-10 p-6">
            <!-- Filters -->
            <div class="flex flex-wrap justify-center gap-4">
                <div class="w-full max-w-md">
                    <select
                        v-model="assignmentFilter"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-5 py-3 text-lg font-medium text-gray-700 shadow transition focus:border-blue-500 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">All Assignment</option>
                        <option v-for="assignment in assignments" :key="assignment" :value="assignment">
                            {{ assignment }}
                        </option>
                    </select>
                </div>
                <div class="w-full max-w-md">
                    <select
                        v-model="divisionFilter"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-5 py-3 text-lg font-medium text-gray-700 shadow transition focus:border-blue-500 focus:ring-4 focus:ring-blue-300 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">All</option>
                        <option v-for="division in uniqueSortedDivisions" :key="division" :value="division">
                            {{ division }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <Link :href="route('employee.index')" class="group">
                    <div class="transform rounded-2xl bg-red-500 p-6 text-center text-white shadow-lg transition duration-300 group-hover:scale-105">
                        <h3 class="text-lg font-medium">Employees</h3>
                        <p class="mt-3 text-5xl font-bold">{{ selectedEmployees }}</p>
                    </div>
                </Link>

                <div
                    class="transform cursor-default rounded-2xl bg-emerald-500 p-6 text-center text-white shadow-lg transition duration-300 hover:scale-105"
                >
                    <h3 class="text-lg font-medium">Regular</h3>
                    <p class="mt-3 text-5xl font-bold">{{ selectedRegular }}</p>
                </div>

                <div
                    class="transform cursor-default rounded-2xl bg-amber-500 p-6 text-center text-white shadow-lg transition duration-300 hover:scale-105"
                >
                    <h3 class="text-lg font-medium">Vacant</h3>
                    <p class="mt-3 text-5xl font-bold">{{ selectedVacant }}</p>
                </div>

                <div
                    class="transform cursor-default rounded-2xl bg-blue-600 p-6 text-center text-white shadow-lg transition duration-300 hover:scale-105"
                >
                    <h3 class="text-lg font-medium">Filled</h3>
                    <p class="mt-3 text-5xl font-bold">{{ selectedFilled }}</p>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <!-- Pie Chart – Employees by Division -->
                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-8 text-center text-2xl font-bold text-gray-800 dark:text-white">Employees by Division</h3>
                    <div class="h-96">
                        <Pie
                            v-if="filteredUnits.length > 0"
                            :key="`${assignmentFilter}|${divisionFilter}`"
                            :data="chartDataEmployeesByUnit"
                            :options="chartOptionsEmployeesByUnit"
                        />
                        <div v-else class="flex h-full items-center justify-center text-lg text-gray-500">No division data</div>
                    </div>
                </div>

                <!-- Gender Bar Chart -->
                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-8 text-center text-2xl font-bold text-gray-800 dark:text-white">Employees by Gender</h3>
                    <div class="h-96">
                        <Bar :data="chartDataGender" :options="chartOptionsGender" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
