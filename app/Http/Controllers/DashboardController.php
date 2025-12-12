<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Inventory;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic counts
        $employeeCount     = Employee::count();
        $userCount         = User::count();
        $inventoryCount    = Inventory::count();

        $regularEmployees  = Employee::where('emp_status', 'Regular')->count();
        $retiredEmployees  = Employee::where('emp_status', 'Retired')->count();

        // Total Filled & Vacant Positions
        $filledPositions = DB::table('positions')
            ->whereRaw("LOWER(status) = 'occupied'")
            ->count();

        $vacantPositions = DB::table('positions')
            ->whereRaw("LOWER(status) = 'vacant'")
            ->count();

        // 1. Employees by Unit (from employees table)
        $employeesByUnit = Employee::select('div_sec_unit')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->groupBy('div_sec_unit')
            ->orderByDesc('value')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->div_sec_unit,
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 2. Vacant Positions by Unit
        $vacantPositionsByUnit = DB::table('positions')
            ->select('desc')
            ->selectRaw('COUNT(*) as value')
            ->whereRaw("LOWER(status) = 'vacant'")
            ->whereNotNull('desc')
            ->where('desc', '!=', '')
            ->groupBy('desc')
            ->orderByDesc('value')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->desc ?: 'Unknown Unit',
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 3. Filled Positions by Unit
        $filledPositionsByUnit = DB::table('positions')
            ->select('desc')
            ->selectRaw('COUNT(*) as value')
            ->whereRaw("LOWER(status) = 'occupied'")
            ->whereNotNull('desc')
            ->where('desc', '!=', '')
            ->groupBy('desc')
            ->orderByDesc('value')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->desc ?: 'Unknown Unit',
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 4. CORRECT: Gender Distribution PER Division/Section/Unit
        $genderByUnitRaw = Employee::select(
                'div_sec_unit',
                DB::raw('SUM(CASE WHEN LOWER(sex) = "male" THEN 1 ELSE 0 END) as male_count'),
                DB::raw('SUM(CASE WHEN LOWER(sex) = "female" THEN 1 ELSE 0 END) as female_count'),
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->groupBy('div_sec_unit')
            ->orderByDesc('total')
            ->get();

        $genderByUnit = $genderByUnitRaw->map(function ($item) {
            return [
                'name'   => $item->div_sec_unit ?: 'Unknown Unit',
                'male'   => (int) $item->male_count,
                'female' => (int) $item->female_count,
                'total'  => (int) $item->total,
            ];
        })->values()->toArray();

        // Optional: Format for stacked bar chart (Chart.js / ApexCharts)
        $genderByUnitCategories = $genderByUnitRaw->pluck('div_sec_unit')->map(fn($unit) => $unit ?: 'Unknown Unit')->toArray();

        $genderByUnitSeries = [
            [
                'name' => 'Male',
                'data' => $genderByUnitRaw->pluck('male_count')->map(fn($v) => (int) $v)->toArray(),
            ],
            [
                'name' => 'Female',
                'data' => $genderByUnitRaw->pluck('female_count')->map(fn($v) => (int) $v)->toArray(),
            ],
        ];

        // Global Gender (for pie chart or cards)
        $employeesByGender = [
            ['name' => 'Male',   'value' => $genderByUnitRaw->sum('male_count')],
            ['name' => 'Female', 'value' => $genderByUnitRaw->sum('female_count')],
        ];

        // Assets by Location
        try {
            $assetsByLocation = DB::table('assets')
                ->select('location', DB::raw('SUM(unit_qty) as value'))
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->groupBy('location')
                ->get()
                ->map(fn($item) => [
                    'name'  => $item->location,
                    'value' => (int) $item->value
                ])
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching assets by location: ' . $e->getMessage());
            $assetsByLocation = [];
        }

        // Old Assigned Assets (>5 years + status = Check)
        $fiveYearsAgo = Carbon::now()->subYears(5)->endOfYear()->toDateString();

        $assignedAssetsTable = DB::table('assets')
            ->join('employees', 'assets.assigned', '=', 'employees.id')
            ->select(
                DB::raw("CONCAT(employees.first_name, ' ', COALESCE(employees.middle_name, ''), ' ', employees.last_name) as full_name"),
                'assets.assigned_date',
                'assets.status',
                'assets.id'
            )
            ->whereNotNull('assets.assigned')
            ->whereNotNull('assets.assigned_date')
            ->where('assets.assigned_date', '<=', $fiveYearsAgo)
            ->where('assets.status', 'Check')
            ->get()
            ->map(fn($item) => [
                'id'            => $item->id,
                'name'          => trim(preg_replace('/\s+/', ' ', $item->full_name)),
                'assigned_date' => Carbon::parse($item->assigned_date)->format('m-d-Y'),
                'status'        => ucfirst(strtolower($item->status)),
            ])
            ->toArray();

        $currentEmployee = auth()->user()?->employee?->load('position');

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'employees'             => $employeeCount,
                'users'                 => $userCount,
                'assignedAssets'        => Inventory::whereNotNull('assigned')->count(),
                'assets'                => $inventoryCount,

                'regularEmployees'      => $regularEmployees,
                'retiredEmployees'      => $retiredEmployees,
                'filledPositions'       => $filledPositions,
                'vacantPositions'       => $vacantPositions,

                'employeesByUnit'       => $employeesByUnit,
                'employeesByGender'     => $employeesByGender, // Global pie chart
                'genderByUnit'          => $genderByUnit,         // Table or detailed view
                'genderByUnitCategories'=> $genderByUnitCategories, // For stacked bar
                'genderByUnitSeries'    => $genderByUnitSeries,     // For stacked bar

                'assetsByLocation'      => $assetsByLocation,

                'vacantPositionsByUnit' => $vacantPositionsByUnit,
                'filledPositionsByUnit' => $filledPositionsByUnit,

                'assignedAssetsTable'   => $assignedAssetsTable,

                // Sample data (replace with real query if needed)
                'purchasedAssets' => [
                    ['month' => 'Jan', 'count' => 10],
                    ['month' => 'Feb', 'count' => 15],
                    ['month' => 'Mar', 'count' => 8],
                    ['month' => 'Apr', 'count' => 20],
                    ['month' => 'May', 'count' => 12],
                    ['month' => 'Jun', 'count' => 18],
                ],
            ],

            'employee' => $currentEmployee,
        ]);
    }
}