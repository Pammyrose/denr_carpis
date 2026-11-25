<?php

namespace App\Http\Controllers;

use App\Models\AssignmentPlace;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\User;
use App\Services\OrganizationalScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly OrganizationalScope $organizationalScope) {}

    public function index()
    {
        $user = auth()->user();
        $employees = function () use ($user) {
            return $this->organizationalScope->applyToEmployees(Employee::query(), $user);
        };
        $employeeTable = function () use ($user) {
            return $this->organizationalScope->applyToEmployees(DB::table('employees'), $user, 'employees.assignment_name');
        };
        $assets = function () use ($user) {
            return $this->organizationalScope->applyToAssets(Inventory::query(), $user);
        };
        $assetTable = function () use ($user) {
            return $this->organizationalScope->applyToAssets(DB::table('assets'), $user, 'assets.assigned');
        };

        // Basic counts
        $employeeCount = $employees()->count();
        $userCount = $this->organizationalScope->applyToUsers(User::query(), $user)->count();
        $inventoryCount = $assets()->count();

        $regularEmployees = $employees()->where('emp_status', 'Regular')->count();
        $retiredEmployees = $employees()->where('emp_status', 'Retired')->count();

        // Total Filled & Vacant Positions
        // Count filled positions by counting employees that reference a positions row
        // which is marked as 'Occupied'. This keeps `filled` aligned with actual
        // employees assigned to occupied positions instead of raw positions rows.
        $filledPositions = $employeeTable()
            ->whereNotNull('position_name')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions.name = employees.position_name')
                    ->whereRaw("LOWER(positions.status) = 'occupied'");
            })->count();

        $vacantPositionQuery = DB::table('positions');
        $this->organizationalScope->applyToPositions($vacantPositionQuery, $user, 'positions.org_code');
        $vacantPositions = $vacantPositionQuery
            ->whereRaw("LOWER(status) = 'vacant'")
            ->count();

        // List of Assignments
        $assignmentQuery = AssignmentPlace::select('name');
        $allowedAssignments = $this->organizationalScope->allowedAssignments($user);
        if ($allowedAssignments !== null) {
            $assignmentQuery->whereIn('name', $allowedAssignments);
        }
        $assignments = $assignmentQuery
            ->orderBy('name')
            ->get()
            ->pluck('name')
            ->toArray();

        // 1. Employees by Unit (from employees table)
        $employeesByUnit = $employees()->select('div_sec_unit')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->groupBy('div_sec_unit')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->div_sec_unit,
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 2. Vacant Positions by Unit
        $vacantPositionsByUnitQuery = DB::table('positions')
            ->leftJoin('div_sec_units', 'positions.org_code', '=', 'div_sec_units.org_code')
            ->selectRaw('COALESCE(div_sec_units.name, positions.desc) as name');
        $this->organizationalScope->applyToPositions($vacantPositionsByUnitQuery, $user, 'positions.org_code');
        $vacantPositionsByUnit = $vacantPositionsByUnitQuery
            ->selectRaw('COUNT(*) as value')
            ->whereRaw("LOWER(positions.status) = 'vacant'")
            ->where(function ($query) {
                $query->whereNotNull('div_sec_units.name')
                    ->orWhereNotNull('positions.desc');
            })
            ->groupByRaw('COALESCE(div_sec_units.name, positions.desc)')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 3. Filled Positions by Unit (count employees with an occupied
        // position, grouped by the employee's own div_sec_unit)
        $filledPositionsByUnit = $employeeTable()
            ->select('employees.div_sec_unit as name')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('employees.div_sec_unit')
            ->where('employees.div_sec_unit', '!=', '')
            ->whereNotNull('employees.position_name')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions.name = employees.position_name')
                    ->whereRaw("LOWER(positions.status) = 'occupied'");
            })
            ->groupBy('employees.div_sec_unit')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // 4. CORRECT: Gender Distribution PER Division/Section/Unit
        $genderByUnitRaw = $employees()->select(
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
                'name' => $item->div_sec_unit ?: 'Unknown Unit',
                'male' => (int) $item->male_count,
                'female' => (int) $item->female_count,
                'total' => (int) $item->total,
            ];
        })->values()->toArray();

        // 5. Regular Employees by Unit
        $regularEmployeesByUnit = $employees()->select('div_sec_unit')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->where('emp_status', 'Regular')
            ->groupBy('div_sec_unit')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->div_sec_unit,
                'value' => (int) $item->value,
            ])
            ->values()
            ->toArray();

        // Optional: Format for stacked bar chart (Chart.js / ApexCharts)
        $genderByUnitCategories = $genderByUnitRaw->pluck('div_sec_unit')->map(fn ($unit) => $unit ?: 'Unknown Unit')->toArray();

        $genderByUnitSeries = [
            [
                'name' => 'Male',
                'data' => $genderByUnitRaw->pluck('male_count')->map(fn ($v) => (int) $v)->toArray(),
            ],
            [
                'name' => 'Female',
                'data' => $genderByUnitRaw->pluck('female_count')->map(fn ($v) => (int) $v)->toArray(),
            ],
        ];

        // Global Gender (for pie chart or cards)
        $employeesByGender = [
            ['name' => 'Male',   'value' => $genderByUnitRaw->sum('male_count')],
            ['name' => 'Female', 'value' => $genderByUnitRaw->sum('female_count')],
        ];

        // Assets by Location
        try {
            $assetsByLocation = $assetTable()
                ->select('location', DB::raw('SUM(unit_qty) as value'))
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->groupBy('location')
                ->get()
                ->map(fn ($item) => [
                    'name' => $item->location,
                    'value' => (int) $item->value,
                ])
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching assets by location: '.$e->getMessage());
            $assetsByLocation = [];
        }

        // Old Assigned Assets (>5 years + status = Check)
        $fiveYearsAgo = Carbon::now()->subYears(5)->endOfYear()->toDateString();

        $assignedAssetsTable = $assetTable()
            ->join('employees', 'assets.assigned', '=', 'employees.id')
            ->select(
                'employees.first_name',
                'employees.middle_name',
                'employees.last_name',
                'assets.assigned_date',
                'assets.status',
                'assets.id'
            )
            ->whereNotNull('assets.assigned')
            ->whereNotNull('assets.assigned_date')
            ->where('assets.assigned_date', '<=', $fiveYearsAgo)
            ->where('assets.status', 'Check')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => trim(implode(' ', array_filter([
                    $item->first_name,
                    $item->middle_name,
                    $item->last_name,
                ]))),
                'assigned_date' => Carbon::parse($item->assigned_date)->format('m-d-Y'),
                'status' => ucfirst(strtolower($item->status)),
            ])
            ->toArray();

        // ==================== ASSIGNMENT-BASED DATA (USING assignment_name) ====================

        // Regular Employees by Assignment
        $regularEmployeesByAssignment = $employees()->where('emp_status', 'Regular')
            ->select('assignment_name')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('assignment_name')
            ->where('assignment_name', '!=', '')
            ->groupBy('assignment_name')
            ->pluck('value', 'assignment_name')
            ->toArray();

        // Filled Positions by Assignment: count employees that have any occupied
        // position row for their position_name, grouped by assignment_name
        $filledPositionsByAssignment = $employeeTable()
            ->select('employees.assignment_name')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('employees.assignment_name')
            ->where('employees.assignment_name', '!=', '')
            ->whereNotNull('employees.position_name')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions.name = employees.position_name')
                    ->whereRaw("LOWER(positions.status) = 'occupied'");
            })
            ->groupBy('employees.assignment_name')
            ->pluck('value', 'assignment_name')
            ->toArray();

        // Filled Positions by Unit under each Assignment (occupied-only)
        $filledPositionsByUnitByAssignment = [];
        foreach ($assignments as $assignment) {
            $filledPositionsByUnitByAssignment[$assignment] = [];
        }

        $filledPositionsByUnitByAssignmentRaw = $employeeTable()
            ->select('employees.assignment_name')
            ->selectRaw('employees.div_sec_unit as unit_name')
            ->selectRaw('COUNT(*) as value')
            ->whereNotNull('employees.assignment_name')
            ->where('employees.assignment_name', '!=', '')
            ->whereNotNull('employees.div_sec_unit')
            ->where('employees.div_sec_unit', '!=', '')
            ->whereNotNull('employees.position_name')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions.name = employees.position_name')
                    ->whereRaw("LOWER(positions.status) = 'occupied'");
            })
            ->groupBy('employees.assignment_name', 'employees.div_sec_unit')
            ->orderByDesc('value')
            ->get();

        foreach ($filledPositionsByUnitByAssignmentRaw as $row) {
            $filledPositionsByUnitByAssignment[$row->assignment_name][] = [
                'name' => $row->unit_name,
                'value' => (int) $row->value,
            ];
        }

        // Gender by Assignment
        $genderByAssignmentRaw = $employees()->select(
            'assignment_name',
            DB::raw('SUM(CASE WHEN LOWER(sex) = "male" THEN 1 ELSE 0 END) as male_count'),
            DB::raw('SUM(CASE WHEN LOWER(sex) = "female" THEN 1 ELSE 0 END) as female_count')
        )
            ->whereNotNull('assignment_name')
            ->where('assignment_name', '!=', '')
            ->groupBy('assignment_name')
            ->get();

        $genderByAssignmentCategories = $genderByAssignmentRaw->pluck('assignment_name')->toArray();

        $genderByAssignmentSeries = [
            [
                'name' => 'Male',
                'data' => $genderByAssignmentRaw->pluck('male_count')->map(fn ($v) => (int) $v)->toArray(),
            ],
            [
                'name' => 'Female',
                'data' => $genderByAssignmentRaw->pluck('female_count')->map(fn ($v) => (int) $v)->toArray(),
            ],
        ];

        // Gender by Division under each Assignment. The dashboard needs this
        // intersection so the gender chart uses the same employees as the
        // Employees by Division chart when either filter changes.
        $genderByUnitByAssignment = [];
        foreach ($assignments as $assignment) {
            $genderByUnitByAssignment[$assignment] = [];
        }

        $genderByUnitByAssignmentRaw = $employees()->select(
            'assignment_name',
            'div_sec_unit',
            DB::raw('SUM(CASE WHEN LOWER(sex) = "male" THEN 1 ELSE 0 END) as male_count'),
            DB::raw('SUM(CASE WHEN LOWER(sex) = "female" THEN 1 ELSE 0 END) as female_count')
        )
            ->whereNotNull('assignment_name')
            ->where('assignment_name', '!=', '')
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->groupBy('assignment_name', 'div_sec_unit')
            ->get();

        foreach ($genderByUnitByAssignmentRaw as $row) {
            $genderByUnitByAssignment[$row->assignment_name][$row->div_sec_unit] = [
                'male' => (int) $row->male_count,
                'female' => (int) $row->female_count,
            ];
        }

        // Regular employees by Division under each Assignment.
        $regularEmployeesByUnitByAssignment = [];
        foreach ($assignments as $assignment) {
            $regularEmployeesByUnitByAssignment[$assignment] = [];
        }

        $regularEmployeesByUnitByAssignmentRaw = $employees()->select('assignment_name', 'div_sec_unit')
            ->selectRaw('COUNT(*) as value')
            ->where('emp_status', 'Regular')
            ->whereNotNull('assignment_name')
            ->where('assignment_name', '!=', '')
            ->whereNotNull('div_sec_unit')
            ->where('div_sec_unit', '!=', '')
            ->groupBy('assignment_name', 'div_sec_unit')
            ->get();

        foreach ($regularEmployeesByUnitByAssignmentRaw as $row) {
            $regularEmployeesByUnitByAssignment[$row->assignment_name][] = [
                'name' => $row->div_sec_unit,
                'value' => (int) $row->value,
            ];
        }

        // Employees by Unit grouped under each Assignment
        $employeesByUnitByAssignment = [];
        foreach ($assignments as $assignment) {
            $employeesByUnitByAssignment[$assignment] = $employees()->select('div_sec_unit')
                ->selectRaw('COUNT(*) as value')
                ->where('assignment_name', $assignment)
                ->whereNotNull('div_sec_unit')
                ->where('div_sec_unit', '!=', '')
                ->groupBy('div_sec_unit')
                ->orderByDesc('value')
                ->get()
                ->map(fn ($item) => [
                    'name' => $item->div_sec_unit,
                    'value' => (int) $item->value,
                ])
                ->values()
                ->toArray();
        }

        // ===================================================================

        $currentEmployee = auth()->user()?->employee?->load('position');

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'assignments' => $assignments,
                'employees' => $employeeCount,
                'users' => $userCount,
                'assignedAssets' => $assets()->whereNotNull('assigned')->count(),
                'assets' => $inventoryCount,

                'regularEmployees' => $regularEmployees,
                'retiredEmployees' => $retiredEmployees,
                'filledPositions' => $filledPositions,
                'vacantPositions' => $vacantPositions,

                'employeesByUnit' => $employeesByUnit,
                'employeesByGender' => $employeesByGender,
                'genderByUnit' => $genderByUnit,
                'genderByUnitCategories' => $genderByUnitCategories,
                'genderByUnitSeries' => $genderByUnitSeries,

                'assetsByLocation' => $assetsByLocation,

                'vacantPositionsByUnit' => $vacantPositionsByUnit,
                'filledPositionsByUnit' => $filledPositionsByUnit,
                'regularEmployeesByUnit' => $regularEmployeesByUnit,

                'assignedAssetsTable' => $assignedAssetsTable,

                'purchasedAssets' => [
                    ['month' => 'Jan', 'count' => 10],
                    ['month' => 'Feb', 'count' => 15],
                    ['month' => 'Mar', 'count' => 8],
                    ['month' => 'Apr', 'count' => 20],
                    ['month' => 'May', 'count' => 12],
                    ['month' => 'Jun', 'count' => 18],
                ],

                // Assignment-based data
                'regularEmployeesByAssignment' => $regularEmployeesByAssignment,
                'filledPositionsByAssignment' => array_map('intval', $filledPositionsByAssignment),
                'filledPositionsByUnitByAssignment' => $filledPositionsByUnitByAssignment,
                'genderByAssignmentCategories' => $genderByAssignmentCategories,
                'genderByAssignmentSeries' => $genderByAssignmentSeries,
                'genderByUnitByAssignment' => $genderByUnitByAssignment,
                'regularEmployeesByUnitByAssignment' => $regularEmployeesByUnitByAssignment,
                'employeesByUnitByAssignment' => $employeesByUnitByAssignment,
            ],

            'employee' => $currentEmployee,
        ]);
    }
}
