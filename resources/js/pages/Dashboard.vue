<script setup lang="ts">
import { computed, ref } from 'vue'
import { Pie, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

ChartJS.register(Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale)

const { props } = usePage()
const dashboardData = props.dashboardData as {
  employees: number
  regularEmployees: number
  filledPositions: number
  vacantPositions: number

  employeesByUnit: { name: string; value: number }[]
  employeesByGender: { name: string; value: number }[]

  filledPositionsByUnit: { name: string; value: number }[]
  vacantPositionsByUnit: { name: string; value: number }[]

  // NEW: Accurate gender per unit from backend
  genderByUnitCategories: string[]
  genderByUnitSeries: { name: string; data: number[] }[]
}

const divisionFilter = ref<string>('')

// Unique divisions — same as before
const uniqueSortedDivisions = computed(() => {
  const filled = dashboardData.filledPositionsByUnit.map(i => i.name)
  const vacant = dashboardData.vacantPositionsByUnit.map(i => i.name)
  return Array.from(new Set([...filled, ...vacant]))
    .filter(Boolean)
    .sort()
})

// Same logic as your original code
const selectedDivisionFilled = computed(() => {
  if (!divisionFilter.value) return dashboardData.filledPositions
  return dashboardData.filledPositionsByUnit.find(u => u.name === divisionFilter.value)?.value ?? 0
})

const selectedDivisionVacant = computed(() => {
  if (!divisionFilter.value) return dashboardData.vacantPositions
  return dashboardData.vacantPositionsByUnit.find(u => u.name === divisionFilter.value)?.value ?? 0
})

const selectedDivisionEmployees = computed(() => {
  if (!divisionFilter.value) return dashboardData.employees
  return dashboardData.employeesByUnit.find(u => u.name === divisionFilter.value)?.value ?? 0
})

const filteredUnits = computed(() => {
  if (!divisionFilter.value) return dashboardData.employeesByUnit
  const match = dashboardData.employeesByUnit.find(u => u.name === divisionFilter.value)
  return match ? [match] : []
})

const currentTotalEmployees = computed(() => {
  return divisionFilter.value ? selectedDivisionEmployees.value : dashboardData.employees
})

// REAL gender per division — no fake proportions!
const filteredGenderData = computed(() => {
  if (!divisionFilter.value) {
    // Show global when no filter
    return dashboardData.employeesByGender
  }

  const idx = dashboardData.genderByUnitCategories.indexOf(divisionFilter.value)
  if (idx === -1) {
    return [{ name: 'Male', value: 0 }, { name: 'Female', value: 0 }]
  }

  const male = dashboardData.genderByUnitSeries.find(s => s.name === 'Male')?.data[idx] ?? 0
  const female = dashboardData.genderByUnitSeries.find(s => s.name === 'Female')?.data[idx] ?? 0

  return [
    { name: 'Male', value: male },
    { name: 'Female', value: female }
  ]
})

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
    PMD: '#3b82f6', LPDD: '#10b981', Finance: '#f59e0b', Admin: '#ef4444',
    Legal: '#8b5cf6', CDD: '#ec4899', SMD: '#14b8a6', Enforcement: '#f97316', ORD: '#06b6d4',
  }
  return palette[unit] || `hsl(${Math.random() * 360}, 70%, 60%)`
}

// Employees by Unit Pie Chart — 100% unchanged
const chartDataEmployeesByUnit = computed(() => ({
  labels: filteredUnits.value.map(i => i.name || 'Unknown'),
  datasets: [{
    label: 'Employees',
    data: filteredUnits.value.map(i => i.value),
    backgroundColor: filteredUnits.value.map(i => getUnitColor(i.name)),
    borderColor: '#fff',
    borderWidth: 3,
    hoverOffset: 12,
  }]
}))

const chartOptionsEmployeesByUnit = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'right' as const },
    tooltip: {
      callbacks: {
        label: (ctx: any) => {
          const total = currentTotalEmployees.value
          const percent = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0
          return `${ctx.label}: ${ctx.raw} (${percent}%)`
        }
      }
    }
  }
}))

// Gender Bar Chart — SAME LOOK, NOW 100% ACCURATE DATA
const chartDataGender = computed(() => ({
  labels: ['Male', 'Female'],
  datasets: [{
    label: 'Number of Employees',
    data: [
      filteredGenderData.value.find(g => g.name === 'Male')?.value || 0,
      filteredGenderData.value.find(g => g.name === 'Female')?.value || 0
    ],
    backgroundColor: ['#2563eb', '#ec4899'],
    borderRadius: 12,
    borderSkipped: false,
    barThickness: 80,
    maxBarThickness: 100,
  }]
}))

const chartOptionsGender = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (ctx: any) => `${ctx.raw} employees`
      }
    }
  },
  scales: {
    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 14 }, stepSize: 10 } },
    x: { grid: { display: false }, ticks: { font: { size: 16, weight: 'bold' }, color: '#7c7c7c'} }
  }
}))
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout>
    <div class="p-6 space-y-10">

      <!-- Division Filter — EXACT SAME -->
      <div class="flex justify-center">
        <div class="w-full max-w-md">
          <select
            v-model="divisionFilter"
            class="w-full px-5 py-3 text-lg font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-xl shadow focus:outline-none focus:ring-4 focus:ring-blue-300 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition"
          >
            <option value="">All Divisions</option>
            <option v-for="division in uniqueSortedDivisions" :key="division" :value="division">
              {{ division }}
            </option>
          </select>
        </div>
      </div>

      <!-- Summary Cards — 100% IDENTICAL -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Link :href="route('employee.index')" class="group">
          <div class="bg-red-500 text-white rounded-2xl shadow-lg p-6 text-center transform group-hover:scale-105 transition duration-300">
            <h3 class="text-lg font-medium">Total Employees</h3>
            <p class="text-5xl font-bold mt-3">{{ selectedDivisionEmployees }}</p>
          </div>
        </Link>

        <div class="bg-emerald-500 text-white rounded-2xl shadow-lg p-6 text-center transform hover:scale-105 transition duration-300 cursor-default">
          <h3 class="text-lg font-medium">Regular</h3>
          <p class="text-5xl font-bold mt-3">{{ dashboardData.regularEmployees }}</p>
        </div>

        <div class="bg-amber-500 text-white rounded-2xl shadow-lg p-6 text-center transform hover:scale-105 transition duration-300 cursor-default">
          <h3 class="text-lg font-medium">Vacant</h3>
          <p class="text-5xl font-bold mt-3">{{ selectedDivisionVacant }}</p>
        </div>

        <div class="bg-blue-600 text-white rounded-2xl shadow-lg p-6 text-center transform hover:scale-105 transition duration-300 cursor-default">
          <h3 class="text-lg font-medium">Filled</h3>
          <p class="text-5xl font-bold mt-3">{{ selectedDivisionFilled }}</p>
        </div>
      </div>

      <!-- Charts Row — EXACT SAME LAYOUT -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        <!-- Pie Chart – Employees by Division (unchanged) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
          <h3 class="text-2xl font-bold text-center mb-8 text-gray-800 dark:text-white">
            Employees by Division
          </h3>
          <div class="h-96">
            <Pie
              v-if="filteredUnits.length > 0"
              :data="chartDataEmployeesByUnit"
              :options="chartOptionsEmployeesByUnit"
            />
            <div v-else class="h-full flex items-center justify-center text-gray-500 text-lg">
              No division data
            </div>
          </div>
        </div>

        <!-- Gender Bar Chart — SAME LOOK, NOW 100% ACCURATE -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-200 dark:border-gray-700">
          <h3 class="text-2xl font-bold text-center mb-8 text-gray-800 dark:text-white">
            Employees by Gender
          </h3>
          <div class="h-96">
            <Bar
              :data="chartDataGender"
              :options="chartOptionsGender"
            />
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>