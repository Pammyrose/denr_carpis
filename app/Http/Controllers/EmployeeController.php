<?php

namespace App\Http\Controllers;

use App\Models\AssignmentPlace;
use App\Models\Employee;
use App\Models\EmployeeImport;
use App\Models\EmployeePositionHistory;
use App\Models\EmpStatus;
use App\Models\Inventory;
use App\Models\OrgUnit;
use App\Models\OtherInfo;
use App\Models\Position; // Add import for OtherInfo
use App\Models\User;
use App\Services\EmployeeExcelImporter;
use App\Services\OrganizationalScope;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeController extends Controller
{
    public function __construct(private readonly OrganizationalScope $organizationalScope) {}

    private function getDropdownData()
    {
        $user = auth()->user();
        $orgUnitQuery = OrgUnit::query();
        $positionQuery = Position::query();
        $assignmentQuery = AssignmentPlace::query();
        $allowedOrgCodes = $this->organizationalScope->allowedOrgCodes($user);
        $allowedAssignments = $this->organizationalScope->allowedAssignments($user);

        if ($allowedOrgCodes !== null) {
            $orgUnitQuery->whereIn('org_code', $allowedOrgCodes);
            $this->organizationalScope->applyToPositions($positionQuery, $user);
        }
        if ($allowedAssignments !== null) {
            $assignmentQuery->whereIn('name', $allowedAssignments);
        }

        $dropdowns = [
            'orgUnits' => $orgUnitQuery->get()->map(fn ($o) => [
                'value' => (string) $o->name,
                'label' => $o->name,
                'org_code' => (string) $o->org_code,
            ]),
            'positions' => $positionQuery->get()->map(fn ($p) => [
                'value' => (string) $p->name,
                'label' => $p->name.($p->status === 'Occupied' ? ' (Occupied)' : ''),
                'salary_grade' => $p->salary_grade,
                'item_code' => $p->item_code,
                'org_code' => $p->org_code ?? '',
                'desc' => $p->desc,
                'status' => $p->status,
            ]),
            'assignmentPlaces' => $assignmentQuery->get()->map(fn ($a) => [
                'value' => (string) $a->name,
                'label' => $a->name,
            ]),
            'empStatuses' => EmpStatus::orderBy('name')
                ->get()
                ->map(fn ($s) => [
                    'value' => $s->name,
                    'label' => $s->name,               // you can also use $s->description if you want a tooltip
                ]),
        ];

        Log::info('EmployeeController: getDropdownData', [
            'orgUnits_count' => count($dropdowns['orgUnits']),
            'positions_count' => count($dropdowns['positions']),
            'assignmentPlaces_count' => count($dropdowns['assignmentPlaces']),
            'empStatuses_count' => count($dropdowns['empStatuses']),
        ]);

        return $dropdowns;
    }

    public function store(Request $request)
    {
        try {
            // Check if the request is for archiving
            if ($request->has('archive_employee_id')) {
                $employeeQuery = Employee::query();
                $this->organizationalScope->applyToEmployees($employeeQuery, $request->user());
                $employee = $employeeQuery->findOrFail($request->archive_employee_id);
                DB::beginTransaction();

                $otherInfo = $employee->otherInfo;
                $user = $employee->user; // Load the related user if exists
                $archivedAt = now();
                $employeeName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name} {$employee->suffix}");
                $position = null;

                if ($employee->position_name) {
                    // Prefer an occupied row so duplicated position titles still free
                    // one real occupied plantilla item, then retain that exact row ID.
                    $position = Position::where('name', $employee->position_name)
                        ->where('status', 'Occupied')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();

                    $position ??= Position::where('name', $employee->position_name)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                // Prepare archived employee data
                $archivedData = [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'middle_name' => $employee->middle_name,
                    'last_name' => $employee->last_name,
                    'suffix' => $employee->suffix,
                    'sex' => $employee->sex,
                    'email' => $employee->email,
                    'contact_no' => $employee->contact_no,
                    'emp_status' => $employee->emp_status,
                    'position_id' => $employee->position_id ?? $employee->position_name,
                    'position_record_id' => $position?->id,
                    'position_item_code' => $position?->item_code,
                    'assignment_id' => $employee->assignment_id ?? $employee->assignment_name,
                    'div_sec_unit' => $employee->org_unit_id ?? $employee->div_sec_unit,
                    'archived_at' => $archivedAt->toDateTimeString(),

                    'date_of_birth' => $otherInfo?->date_of_birth,
                    'tin_no' => $otherInfo?->tin_no,
                    'date_appointment' => $otherInfo?->date_appointment,
                    'date_last_promotion' => $otherInfo?->date_last_promotion,
                    'civil_service' => $otherInfo?->civil_service,
                    'education' => $otherInfo?->education,

                    // Preserve user info for possible restoration
                    'user_exists' => $user ? true : false,
                    'user_is_admin' => $user?->is_admin ?? false,
                ];

                // Load existing archived employees from storage
                $filePath = 'archived_employees.json';
                $archivedEmployees = Storage::disk('local')->exists($filePath)
                    ? json_decode(Storage::disk('local')->get($filePath), true)
                    : [];
                $archivedEmployees[$employee->id] = $archivedData;

                // === FREE THE POSITION WHEN ARCHIVING ===
                if ($employee->position_name) {
                    EmployeePositionHistory::create([
                        'employee_id' => $employee->id,
                        'employee_email' => $employee->email,
                        'employee_name' => $employeeName,
                        'position_id' => $position?->id,
                        'item_code' => $position?->item_code,
                        'position_name' => $employee->position_name,
                        'assignment_name' => $employee->assignment_name,
                        'div_sec_unit' => $employee->div_sec_unit,
                        'event' => 'archived',
                        'status_from' => $position?->status,
                        'status_to' => $position ? 'Vacant' : null,
                        'recorded_at' => $archivedAt,
                    ]);

                    $position?->update(['status' => 'Vacant']);

                    Log::info('Position freed and history recorded during archiving', [
                        'position_name' => $employee->position_name,
                        'position_id' => $position?->id,
                        'item_code' => $position?->item_code,
                        'employee_id' => $employee->id,
                    ]);
                }

                // Preserve name in assigned assets
                $assets = Inventory::where('assigned', $employee->id)->get();
                foreach ($assets as $asset) {
                    $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name} {$employee->suffix}");
                    $asset->update(['assigned_to_name' => $fullName]);
                }
                Log::info('EmployeeController: Preserved employee name in assigned assets', [
                    'employee_id' => $employee->id,
                    'asset_count' => $assets->count(),
                ]);

                // Delete related records
                if ($otherInfo) {
                    $otherInfo->delete();
                }

                // NEW: Delete the associated User account from the users table
                if ($user) {
                    $user->delete();
                    Log::info('EmployeeController: Deleted User account during archiving', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }

                // Delete the employee record
                $employee->delete();

                // Save only after all database operations have succeeded. A
                // failed file write will still roll the database transaction back.
                if (! Storage::disk('local')->put($filePath, json_encode($archivedEmployees, JSON_PRETTY_PRINT))) {
                    throw new \RuntimeException('Failed to save the employee archive file.');
                }

                Log::info('EmployeeController: Archiving employee to storage (with user info)', $archivedData);

                DB::commit();

                Log::info('EmployeeController: Employee archived via store', [
                    'employee_id' => $employee->id,
                ]);

                return redirect()->route('employee.index')->with('message', 'Employee archived successfully');
            }

            // =====================================================================
            // Existing employee creation logic (WITH POSITION STATUS UPDATE)
            // =====================================================================
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'suffix' => 'nullable|string|max:50',
                'sex' => 'required|string|in:Male,Female',
                'contact_no' => 'required|digits_between:10,15|unique:employees,contact_no',
                'email' => 'required|email|unique:employees,email|max:255|unique:users,email',
                'emp_status' => 'required|exists:emp_status,name',
                'position_name' => [
                    'required',
                    Rule::exists('positions', 'name')->where(
                        fn ($query) => $query->whereRaw('LOWER(status) = ?', ['vacant'])
                    ),
                ],
                'assignment_name' => 'required|exists:assignments,name',
                'div_sec_unit' => 'required|exists:div_sec_units,name',
                'password' => [
                    'required',
                    'string',
                    'min:16',
                    'confirmed',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*?&]/',
                ],
                'date_of_birth' => 'required|date',
                'tin_no' => 'nullable|string|max:255',
                'date_appointment' => 'nullable|date',
                'date_last_promotion' => 'nullable|date',
                'civil_service' => 'nullable|string|max:255',
                'education' => 'nullable|string|max:1000',
            ]);

            $this->ensureAssignmentIsAllowed($request, $validated['assignment_name']);

            DB::beginTransaction();

            // Create User
            $user = User::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => false,
            ]);

            // Prepare employee data
            $employeeData = [
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'],
                'sex' => $validated['sex'],
                'contact_no' => $validated['contact_no'],
                'email' => $validated['email'],
                'emp_status' => $validated['emp_status'],
                'position_name' => $validated['position_name'],
                'assignment_name' => $validated['assignment_name'],
                'div_sec_unit' => $validated['div_sec_unit'],
            ];

            // Prepare other info data
            $otherInfoData = [
                'date_of_birth' => $validated['date_of_birth'],
                'tin_no' => $validated['tin_no'],
                'date_appointment' => $validated['date_appointment'],
                'date_last_promotion' => $validated['date_last_promotion'],
                'civil_service' => $validated['civil_service'],
                'education' => $validated['education'],
            ];

            // Create Employee and OtherInfo
            $employee = Employee::create($employeeData);
            $employee->otherInfo()->create($otherInfoData);

            // MARK POSITION AS OCCUPIED AFTER CREATING EMPLOYEE
            Position::where('name', $validated['position_name'])
                ->update(['status' => 'Occupied']);

            Log::info('Position marked as Occupied', [
                'position_name' => $validated['position_name'],
                'employee_id' => $employee->id,
            ]);

            DB::commit();

            Log::info('EmployeeController: Employee and OtherInfo created', [
                'email' => $validated['email'],
                'employee_id' => $employee->id,
            ]);

            return redirect()->route('employee.index')->with('message', 'Employee created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('EmployeeController: Validation failed', [
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to process request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Failed to process request: '.$e->getMessage()])->withInput();
        }
    }

    public function index(Request $request)
    {
        try {
            // Capture selected filters
            $selectedDivision = $request->input('div_sec_unit');
            $selectedAssignment = $request->input('assignment_name');
            $selectedPosition = $request->input('position_name');

            // Base query with relations
            $query = Employee::with(['position', 'assignment', 'orgUnit', 'otherInfo', 'positionHistories.position']);
            $this->organizationalScope->applyToEmployees($query, $request->user());

            // Apply filters if selected
            if (! empty($selectedDivision)) {
                $query->where('div_sec_unit', $selectedDivision);
            }

            if (! empty($selectedAssignment)) {
                $query->where('assignment_name', $selectedAssignment);
            }

            if (! empty($selectedPosition)) {
                $query->where('position_name', $selectedPosition);
            }

            // Fetch employees and map data
            $employees = $query->get()->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name ?? 'N/A',
                    'middle_name' => $employee->middle_name ?? '',
                    'last_name' => $employee->last_name ?? 'N/A',
                    'suffix' => $employee->suffix ?? '',
                    'sex' => $employee->sex ?? 'N/A',
                    'contact_no' => $employee->contact_no ?? 'N/A',
                    'email' => $employee->email ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',

                    'position_name' => $employee->position_name ? (string) $employee->position_name : null,
                    'position_label' => $employee->position ? $employee->position->name : 'N/A',

                    'assignment_name' => $employee->assignment_name ? (string) $employee->assignment_name : null,
                    'assignment_label' => $employee->assignment ? $employee->assignment->name : 'N/A',

                    'div_sec_unit' => $employee->div_sec_unit ? (string) $employee->div_sec_unit : null,
                    'div_sec_unit_label' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A',

                    'date_of_birth' => $employee->otherInfo->date_of_birth ?? null,
                    'tin_no' => $employee->otherInfo->tin_no ?? null,
                    'date_appointment' => $employee->otherInfo->date_appointment ?? null,
                    'date_last_promotion' => $employee->otherInfo->date_last_promotion ?? null,
                    'civil_service' => $employee->otherInfo->civil_service ?? null,
                    'education' => $employee->otherInfo->education ?? null,
                    'position_history' => $this->formatPositionHistories($employee->positionHistories),
                ];
            })->toArray();

            $currentEmployee = auth()->user()?->employee;

            // Unique dropdown values
            $divisionQuery = Employee::select('div_sec_unit');
            $this->organizationalScope->applyToEmployees($divisionQuery, $request->user());
            $divisions = $divisionQuery
                ->distinct()
                ->orderBy('div_sec_unit')
                ->get();

            $employeeFilterOptionsQuery = Employee::query();
            $this->organizationalScope->applyToEmployees($employeeFilterOptionsQuery, $request->user());
            $employeeFilterOptions = $employeeFilterOptionsQuery
                ->select('assignment_name', 'div_sec_unit', 'position_name')
                ->whereNotNull('assignment_name')
                ->where('assignment_name', '!=', '')
                ->distinct()
                ->get();

            $divisionOptionsByAssignment = $employeeFilterOptions
                ->groupBy('assignment_name')
                ->map(function ($rows) {
                    return $rows->pluck('div_sec_unit')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                })
                ->toArray();

            $positionOptionsByAssignment = $employeeFilterOptions
                ->groupBy('assignment_name')
                ->map(function ($rows) {
                    return $rows->pluck('position_name')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                })
                ->toArray();

            $positionOptionsByAssignmentAndDivision = $employeeFilterOptions
                ->filter(fn ($row) => filled($row->div_sec_unit))
                ->groupBy('assignment_name')
                ->map(function ($assignmentRows) {
                    return $assignmentRows
                        ->groupBy('div_sec_unit')
                        ->map(function ($divisionRows) {
                            return $divisionRows->pluck('position_name')
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();
                        });
                })
                ->toArray();

            // List of Assignments
            $assignmentQuery = AssignmentPlace::select('name');
            $allowedAssignments = $this->organizationalScope->allowedAssignments($request->user());
            if ($allowedAssignments !== null) {
                $assignmentQuery->whereIn('name', $allowedAssignments);
            }
            $assignments = $assignmentQuery
                ->orderBy('name')
                ->get()
                ->pluck('name')
                ->toArray();

            return Inertia::render('employee/Index', [
                'assignments' => $assignments,
                'employees' => $employees,
                'divisions' => $divisions,
                'divisionOptionsByAssignment' => $divisionOptionsByAssignment,
                'positionOptionsByAssignment' => $positionOptionsByAssignment,
                'positionOptionsByAssignmentAndDivision' => $positionOptionsByAssignmentAndDivision,
                'selectedDivision' => $selectedDivision,
                'selectedAssignment' => $selectedAssignment,
                'selectedPosition' => $selectedPosition,
                'dropdowns' => $this->getDropdownData(),
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController Error', ['error' => $e->getMessage()]);

            return Inertia::render('employee/Index', [
                'assignments' => [],
                'employees' => [],
                'divisions' => [],
                'divisionOptionsByAssignment' => [],
                'positionOptionsByAssignment' => [],
                'positionOptionsByAssignmentAndDivision' => [],
                'dropdowns' => $this->getDropdownData(),
                'error' => 'Failed to load employees.',
            ]);
        }
    }

    public function view($id)
    {
        try {
            $employeeQuery = Employee::with(['otherInfo', 'assets', 'position', 'assignment', 'orgUnit', 'positionHistories.position']);
            $this->organizationalScope->applyToEmployees($employeeQuery, auth()->user());
            $employee = $employeeQuery->findOrFail($id);
            $dropdowns = $this->getDropdownData();

            return Inertia::render('employee/View', [
                'employee' => [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name ?? 'N/A',
                    'middle_name' => $employee->middle_name ?? '',
                    'last_name' => $employee->last_name ?? 'N/A',
                    'suffix' => $employee->suffix ?? '',
                    'sex' => $employee->sex ?? 'N/A',
                    'email' => $employee->email ?? 'N/A',
                    'contact_no' => $employee->contact_no ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',
                    'position_name' => $employee->position ? $employee->position->name : 'N/A', // Use name from positions table
                    'assignment_name' => $employee->assignment ? $employee->assignment->name : 'N/A', // Use name from assignment_places table
                    'div_sec_unit' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A', // Use name from org_units table
                    'date_of_birth' => $employee->otherInfo ? $employee->otherInfo->date_of_birth : null,
                    'tin_no' => $employee->otherInfo ? $employee->otherInfo->tin_no : null,
                    'date_appointment' => $employee->otherInfo ? $employee->otherInfo->date_appointment : null,
                    'date_last_promotion' => $employee->otherInfo ? $employee->otherInfo->date_last_promotion : null,
                    'civil_service' => $employee->otherInfo ? $employee->otherInfo->civil_service : null,
                    'education' => $employee->otherInfo ? $employee->otherInfo->education : null,
                    'position_history' => $this->formatPositionHistories($employee->positionHistories),
                    'assets' => $employee->assets ? $employee->assets->map(function ($asset) {
                        return [
                            'id' => $asset->id,
                            'name' => $asset->name ?? 'N/A',
                            'category' => $asset->category ?? 'N/A',
                            'location' => $asset->location ?? 'N/A',
                            'purchase_date' => $asset->purchase_date ?? 'N/A',
                        ];
                    })->toArray() : [],
                ],
                'dropdowns' => $dropdowns,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to fetch employee', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('employee/View', [
                'employee' => null,
                'dropdowns' => $this->getDropdownData(),
                'error' => 'Failed to load employee. Please try again later.',
            ]);
        }
    }

    public function edit(Employee $employee)
    {
        abort_unless($this->organizationalScope->canAccessEmployee(auth()->user(), $employee), 403);

        $employeeQuery = Employee::query();
        $this->organizationalScope->applyToEmployees($employeeQuery, auth()->user());
        $employees = $employeeQuery->get()->map(function ($e) {
            $fullName = trim("{$e->first_name} {$e->middle_name} {$e->last_name}");

            return [
                'id' => $e->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('EmployeeController@edit: Employees for Edit Form', [
            'employees' => $employees,
            'employee_id' => $employee->id,
        ]);

        return Inertia::render('employee/Index', [
            'employee' => [
                'id' => $employee->id,
                'first_name' => $employee->first_name ?? 'N/A',
                'middle_name' => $employee->middle_name ?? '',
                'last_name' => $employee->last_name ?? 'N/A',
                'suffix' => $employee->suffix ?? '',
                'sex' => $employee->sex ?? 'N/A',
                'email' => $employee->email ?? 'N/A',
                'contact_no' => $employee->contact_no ?? '',
                'emp_status' => $employee->emp_status ?? 'N/A',
                'position_id' => $employee->position_id ? (string) $employee->position_id : null,
                'assignment_id' => $employee->assignment_id ? (string) $employee->assignment_id : null,
                'org_unit_id' => $employee->org_unit_id ? (string) $employee->org_unit_id : null,
                'date_of_birth' => $employee->otherInfo ? $employee->otherInfo->date_of_birth : null,
                'tin_no' => $employee->otherInfo ? $employee->otherInfo->tin_no : null,
                'date_appointment' => $employee->otherInfo ? $employee->otherInfo->date_appointment : null,
                'date_last_promotion' => $employee->otherInfo ? $employee->otherInfo->date_last_promotion : null,
                'civil_service' => $employee->otherInfo ? $employee->otherInfo->civil_service : null,
                'education' => $employee->otherInfo ? $employee->otherInfo->education : null,
            ],
            'employees' => $employees,
            'dropdowns' => $this->getDropdownData(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless($this->organizationalScope->canAccessEmployee($request->user(), $employee), 403);

        Log::info('EmployeeController@update: Incoming request data', $request->all());

        // Validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'sex' => 'required|string|in:Male,Female',
            'contact_no' => [
                'required',
                'digits_between:10,15',
                Rule::unique('employees', 'contact_no')->ignore($employee->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id),
                // Ignore the current user's record if it exists
                Rule::unique('users', 'email')->ignore($employee->user?->id),
            ],
            'emp_status' => 'required|exists:emp_status,name',
            'position_id' => [
                'required',
                Rule::exists('positions', 'name')->where(function ($query) use ($employee) {
                    $query->whereRaw('LOWER(status) = ?', ['vacant']);

                    if ($employee->position_name) {
                        $query->orWhere('name', $employee->position_name);
                    }
                }),
            ],
            'assignment_id' => 'required|exists:assignments,name',
            'org_unit_id' => 'required|exists:div_sec_units,name',
            'password' => 'nullable|string|min:16|confirmed|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
            'date_of_birth' => 'required|date',
            'tin_no' => 'nullable|string|max:255',
            'date_appointment' => 'nullable|date',
            'date_last_promotion' => 'nullable|date',
            'civil_service' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:1000',
        ];

        $validated = $request->validate($rules);
        $this->ensureAssignmentIsAllowed($request, $validated['assignment_id']);

        try {
            DB::beginTransaction();

            // === Update User record (email + password) ===
            $user = $employee->user; // Assuming you have a relationship: belongsTo(User::class)

            if (! $user) {
                // If no user exists (shouldn't happen normally), create one
                $user = User::create([
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password'] ?? Str::random(16)),
                    'is_admin' => false,
                ]);
                // You might want to link them via foreign key if needed
            } else {
                $userData = [];

                // Always update email if changed
                if ($user->email !== $validated['email']) {
                    $userData['email'] = $validated['email'];
                }

                // Update password only if provided
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                if (! empty($userData)) {
                    $user->update($userData);
                    Log::info('User record updated during employee update', [
                        'user_id' => $user->id,
                        'email' => $validated['email'],
                        'password_changed' => $request->filled('password'),
                    ]);
                }
            }

            // === Update Employee record ===
            $oldPositionName = $employee->position_name;
            $newPositionName = $validated['position_id'];

            $employee->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'],
                'sex' => $validated['sex'],
                'contact_no' => $validated['contact_no'],
                'email' => $validated['email'],
                'emp_status' => $validated['emp_status'],
                'position_name' => $newPositionName,
                'assignment_name' => $validated['assignment_id'],
                'div_sec_unit' => $validated['org_unit_id'],
            ]);

            // === Update OtherInfo ===
            $employee->otherInfo()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'date_of_birth' => $validated['date_of_birth'],
                    'tin_no' => $validated['tin_no'],
                    'date_appointment' => $validated['date_appointment'],
                    'date_last_promotion' => $validated['date_last_promotion'],
                    'civil_service' => $validated['civil_service'],
                    'education' => $validated['education'],
                ]
            );

            // === Handle Position Status Change ===
            if ($oldPositionName && $oldPositionName !== $newPositionName) {
                Position::where('name', $oldPositionName)
                    ->limit(1)
                    ->update(['status' => 'Vacant']);

                Log::info('Freed previous position', [
                    'position' => $oldPositionName,
                    'employee_id' => $employee->id,
                ]);
            }

            // Mark new/current position as Occupied
            Position::where('name', $newPositionName)
                ->limit(1)
                ->update(['status' => 'Occupied']);

            Log::info('Marked current position as Occupied', [
                'position' => $newPositionName,
                'employee_id' => $employee->id,
            ]);

            DB::commit();

            return redirect()
                ->route('employee.index')
                ->with('message', 'Employee updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController@update failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['general' => 'Failed to update employee: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Employee $employee)
    {
        abort_unless($this->organizationalScope->canAccessEmployee(auth()->user(), $employee), 403);

        try {
            Log::info('EmployeeController: Deleting employee', ['employee_id' => $employee->id]);
            $employee->delete();
            Log::info('EmployeeController: Employee deleted', ['employee_id' => $employee->id]);

            return redirect()->route('employee.index')->with('message', 'Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to delete employee', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to delete employee: '.$e->getMessage()]);
        }
    }

    public function archived()
    {
        try {
            $currentEmployee = auth()->user()?->employee;

            // Retrieve archived employees from storage
            $filePath = 'archived_employees.json';
            $archivedEmployees = Storage::disk('local')->exists($filePath)
                ? json_decode(Storage::disk('local')->get($filePath), true)
                : [];
            $formattedEmployees = array_values($archivedEmployees);
            $allowedAssignments = $this->organizationalScope->allowedAssignments(auth()->user());
            if ($allowedAssignments !== null) {
                $formattedEmployees = array_values(array_filter(
                    $formattedEmployees,
                    fn (array $employee) => in_array($employee['assignment_id'] ?? null, $allowedAssignments, true)
                ));
            }

            $historyByEmployee = EmployeePositionHistory::query()
                ->with('position')
                ->whereIn('employee_id', array_column($formattedEmployees, 'id'))
                ->orderByDesc('recorded_at')
                ->get()
                ->groupBy('employee_id');

            foreach ($formattedEmployees as &$archivedEmployee) {
                $archivedEmployee['archived_at'] = $this->formatDateTimeForDisplay(
                    $archivedEmployee['archived_at'] ?? null
                );
                $archivedEmployee['position_history'] = $this->formatPositionHistories(
                    $historyByEmployee->get($archivedEmployee['id'], collect())
                );

                // Older JSON archives predate the history table. Keep their
                // archived position visible as a clearly identified legacy event.
                if ($archivedEmployee['position_history'] === [] && ! empty($archivedEmployee['position_id'])) {
                    $archivedEmployee['position_history'][] = [
                        'id' => null,
                        'event' => 'archived_legacy',
                        'position_name' => $archivedEmployee['position_id'],
                        'item_code' => $archivedEmployee['position_item_code'] ?? null,
                        'assignment_name' => $archivedEmployee['assignment_id'] ?? null,
                        'div_sec_unit' => $archivedEmployee['div_sec_unit'] ?? null,
                        'status_from' => null,
                        'status_to' => null,
                        'recorded_at' => $archivedEmployee['archived_at'] ?? null,
                    ];
                }
            }
            unset($archivedEmployee);

            $vacantPositionQuery = Position::query()
                ->where('status', 'Vacant');
            $this->organizationalScope->applyToPositions($vacantPositionQuery, auth()->user());
            $vacantPositions = $vacantPositionQuery
                ->orderBy('name')
                ->orderBy('item_code')
                ->get()
                ->map(fn (Position $position) => [
                    'id' => $position->id,
                    'item_code' => $position->item_code,
                    'name' => $position->name,
                    'description' => $position->desc,
                    'salary_grade' => $position->salary_grade,
                ])
                ->values();

            Log::info('EmployeeController: Fetched archived employees from storage', [
                'employee_count' => count($formattedEmployees),
            ]);

            return Inertia::render('employee/Archived', [
                'archivedEmployees' => $formattedEmployees,
                'vacantPositions' => $vacantPositions,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to fetch archived employees', [
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('employee/Archived', [
                'archivedEmployees' => [],
                'vacantPositions' => [],
                'error' => 'Failed to load archived employees. Please try again later.',
            ]);
        }
    }

    public function unarchive(Request $request, $archivedEmployeeId)
    {
        try {
            DB::beginTransaction();

            $filePath = 'archived_employees.json';
            $archivedEmployees = Storage::disk('local')->exists($filePath)
                ? json_decode(Storage::disk('local')->get($filePath), true)
                : [];
            $archivedEmployee = $archivedEmployees[$archivedEmployeeId] ?? null;

            if (! $archivedEmployee) {
                throw new \Exception('Archived employee not found.');
            }

            $this->ensureAssignmentIsAllowed($request, $archivedEmployee['assignment_id'] ?? null);

            // Prevent duplicate email in active employees
            if (Employee::where('email', $archivedEmployee['email'])->exists()) {
                throw new \Exception('An active employee with this email already exists.');
            }

            $formerPositionName = $archivedEmployee['position_id'] ?? null;
            $selectedPositionId = $request->integer('position_record_id') ?: null;
            $positionName = $formerPositionName;
            $position = null;

            if ($selectedPositionId) {
                $positionQuery = Position::whereKey($selectedPositionId);
                $this->organizationalScope->applyToPositions($positionQuery, $request->user());
                $position = $positionQuery->lockForUpdate()->first();

                if (! $position) {
                    throw new \Exception('The selected position does not exist or is outside your office scope.');
                }

                if (strcasecmp($position->status, 'Vacant') !== 0) {
                    throw new \Exception('The selected position is no longer vacant. Please choose another position.');
                }

                $positionName = $position->name;
            } elseif ($positionName) {
                if (! empty($archivedEmployee['position_record_id'])) {
                    $position = Position::whereKey($archivedEmployee['position_record_id'])
                        ->lockForUpdate()
                        ->first();
                } else {
                    // Backward compatibility for archives created before exact
                    // position row IDs were stored.
                    $position = Position::where('name', $positionName)
                        ->where('status', 'Vacant')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                if (! $position || $position->name !== $positionName) {
                    throw new \Exception('The employee\'s former position no longer exists.');
                }

                if (strcasecmp($position->status, 'Vacant') !== 0) {
                    throw new \Exception('The employee\'s former position is already occupied. Choose another vacant position when restoring this employee.');
                }
            }

            $restoredPlacement = $this->positionPlacement(
                $position,
                $archivedEmployee['assignment_id'] ?? null,
                $archivedEmployee['div_sec_unit'] ?? null,
            );

            // === Restore or create User account with preserved admin status ===
            $user = User::where('email', $archivedEmployee['email'])->first();

            if (! $user) {
                $user = User::create([
                    'email' => $archivedEmployee['email'],
                    'password' => Hash::make('TempPass123!@#'), // Strong temporary password
                    'is_admin' => $archivedEmployee['user_is_admin'] ?? false,
                ]);
                Log::info('Created new User during unarchive', [
                    'email' => $archivedEmployee['email'],
                    'is_admin' => $archivedEmployee['user_is_admin'] ?? false,
                ]);
            } else {
                // Restore original admin privilege if different
                if (($archivedEmployee['user_is_admin'] ?? false) !== $user->is_admin) {
                    $user->update(['is_admin' => $archivedEmployee['user_is_admin'] ?? false]);
                    Log::info('Restored admin status during unarchive', [
                        'email' => $archivedEmployee['email'],
                    ]);
                }
            }

            // === Restore Employee ===
            $employee = new Employee;
            $employee->forceFill([
                'id' => $archivedEmployee['id'],
                'first_name' => $archivedEmployee['first_name'],
                'middle_name' => $archivedEmployee['middle_name'],
                'last_name' => $archivedEmployee['last_name'],
                'suffix' => $archivedEmployee['suffix'],
                'sex' => $archivedEmployee['sex'],
                'contact_no' => $archivedEmployee['contact_no'],
                'email' => $archivedEmployee['email'],
                'emp_status' => $archivedEmployee['emp_status'],
                'position_name' => $positionName,
                'assignment_name' => $restoredPlacement['assignment_name'],
                'div_sec_unit' => $restoredPlacement['div_sec_unit'],
            ]);
            $employee->save();

            // Occupy the selected vacant position and retain the assignment event.
            if ($position) {
                $restoredAt = now();
                $restoredToAlternatePosition = ! empty($archivedEmployee['position_record_id'])
                    ? (int) $archivedEmployee['position_record_id'] !== (int) $position->id
                    : $formerPositionName !== $position->name;
                $position->update(['status' => 'Occupied']);

                EmployeePositionHistory::create([
                    'employee_id' => $employee->id,
                    'employee_email' => $employee->email,
                    'employee_name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name} {$employee->suffix}"),
                    'position_id' => $position->id,
                    'item_code' => $position->item_code,
                    'position_name' => $position->name,
                    'assignment_name' => $employee->assignment_name,
                    'div_sec_unit' => $employee->div_sec_unit,
                    'event' => $restoredToAlternatePosition ? 'restored_reassigned' : 'restored',
                    'status_from' => 'Vacant',
                    'status_to' => 'Occupied',
                    'recorded_at' => $restoredAt,
                ]);

                Log::info('Position marked as Occupied and history recorded on unarchive', [
                    'position_name' => $positionName,
                    'position_id' => $position->id,
                    'item_code' => $position->item_code,
                    'employee_id' => $employee->id,
                    'alternate_position' => $restoredToAlternatePosition,
                ]);
            }

            // === Restore OtherInfo ===
            $otherInfoData = [
                'employee_id' => $employee->id,
                'date_of_birth' => $archivedEmployee['date_of_birth'] ?? null,
                'tin_no' => $archivedEmployee['tin_no'] ?? null,
                'date_appointment' => $archivedEmployee['date_appointment'] ?? null,
                'date_last_promotion' => $archivedEmployee['date_last_promotion'] ?? null,
                'civil_service' => $archivedEmployee['civil_service'] ?? null,
                'education' => $archivedEmployee['education'] ?? null,
            ];

            // Only create if there's actual data
            if (collect($otherInfoData)->filter()->isNotEmpty()) {
                $employee->otherInfo()->create($otherInfoData);
            }

            // === Remove from archive file ===
            unset($archivedEmployees[$archivedEmployeeId]);
            if (! Storage::disk('local')->put($filePath, json_encode($archivedEmployees, JSON_PRETTY_PRINT))) {
                throw new \RuntimeException('Failed to update the employee archive file.');
            }

            DB::commit();

            return redirect()->route('employee.index')
                ->with('message', "Employee '{$employee->first_name} {$employee->last_name}' (ID: {$employee->id}) successfully unarchived.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to unarchive employee', [
                'archived_id' => $archivedEmployeeId,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Unarchive failed: '.$e->getMessage()]);
        }
    }

    public function storeOrgUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_code' => 'required|string|max:255|unique:div_sec_units,org_code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: OrgUnit validation failed', [
                'errors' => $validator->errors(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            OrgUnit::create([
                'org_code' => $request->org_code,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            Log::info('EmployeeController: OrgUnit created', [
                'org_code' => $request->org_code,
                'name' => $request->name,
            ]);

            return redirect()->route('employee.index')->with('message', 'Organizational unit created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create org unit', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to create organizational unit: '.$e->getMessage()])->withInput();
        }
    }

    public function position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:255|unique:positions,item_code',
            'name' => 'required|string|max:255',
            'desc' => 'required|string|max:1000',
            'salary_grade' => 'required|integer|between:1,33',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: Position validation failed', [
                'errors' => $validator->errors(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $position = Position::create([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'desc' => $request->desc,
                'salary_grade' => $request->salary_grade,
            ]);

            DB::commit();

            Log::info('EmployeeController: Position created', [
                'item_code' => $request->item_code,
                'name' => $request->name,
            ]);

            return back()->with([
                'message' => 'Position created successfully',
                'position' => [
                    'value' => (string) $position->name,
                    'label' => $position->name,
                    'item_code' => $position->item_code,
                    'salary_grade' => $position->salary_grade,
                    'org_code' => $position->org_code ?? '',
                    'status' => $position->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create position', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to create position: '.$e->getMessage()])->withInput();
        }
    }

    public function storeSalaryGrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|exists:positions,item_code',
            'salary_grade' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: SalaryGrade validation failed', [
                'errors' => $validator->errors(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $position = Position::where('item_code', $request->item_code)->firstOrFail();
            $position->update([
                'salary_grade' => $request->salary_grade,
            ]);

            DB::commit();

            Log::info('EmployeeController: SalaryGrade updated', [
                'item_code' => $request->item_code,
                'salary_grade' => $request->salary_grade,
            ]);

            return redirect()->route('employee.index')->with([
                'message' => 'Salary grade updated successfully',
                'position' => [
                    'value' => (string) $position->name,
                    'label' => $position->name,
                    'salary_grade' => $position->salary_grade,
                    'item_code' => $position->item_code,
                    'org_code' => $position->org_code ?? '',
                    'status' => $position->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to update salary grade', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to update salary grade: '.$e->getMessage()])->withInput();
        }
    }

    public function storeAssignmentPlace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: AssignmentPlace validation failed', [
                'errors' => $validator->errors(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $assignmentPlace = AssignmentPlace::create([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);

            DB::commit();

            Log::info('EmployeeController: AssignmentPlace created', [
                'name' => $request->name,
            ]);

            return redirect()->route('employee.index')->with([
                'message' => 'Assignment place created successfully',
                'assignmentPlace' => [
                    'value' => (string) $assignmentPlace->id,
                    'label' => $assignmentPlace->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create assignment place', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to create assignment place: '.$e->getMessage()])->withInput();
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            'employee-imports',
            Str::uuid().'.'.$extension,
            'local'
        );

        if (! $storedPath) {
            return response()->json([
                'message' => 'The Excel file could not be saved for processing.',
            ], 500);
        }

        $employeeImport = EmployeeImport::create([
            'user_id' => $request->user()->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'status' => 'queued',
        ]);

        return response()->json([
            'message' => 'Excel file uploaded. Import processing is ready.',
            'import' => $this->employeeImportPayload($employeeImport),
        ], 202);
    }

    public function employeeImportStatus(Request $request, EmployeeImport $employeeImport)
    {
        abort_unless($employeeImport->user_id === $request->user()->id, 404);

        return response()->json([
            'import' => $this->employeeImportPayload($employeeImport->fresh()),
        ]);
    }

    public function processEmployeeImport(
        Request $request,
        EmployeeImport $employeeImport,
        EmployeeExcelImporter $importer
    ) {
        abort_unless($employeeImport->user_id === $request->user()->id, 404);

        if (in_array($employeeImport->status, ['completed', 'failed'], true)) {
            return response()->json([
                'import' => $this->employeeImportPayload($employeeImport),
            ]);
        }

        // Let an already-running legacy queue job finish without competing with
        // browser-driven batches for the same workbook.
        if (
            $employeeImport->status === 'processing'
            && DB::table('jobs')->whereNotNull('reserved_at')->exists()
        ) {
            return response()->json([
                'message' => 'The background worker is currently processing this import.',
                'import' => $this->employeeImportPayload($employeeImport->fresh()),
            ], 409);
        }

        $lock = Cache::lock("employee-import:{$employeeImport->id}", 120);
        if (! $lock->get()) {
            return response()->json([
                'message' => 'Another import batch is still processing.',
                'import' => $this->employeeImportPayload($employeeImport->fresh()),
            ], 409);
        }

        try {
            if (! Storage::disk('local')->exists($employeeImport->stored_path)) {
                throw new \RuntimeException('The uploaded Excel file is no longer available.');
            }

            if ($employeeImport->status === 'queued') {
                $employeeImport->update([
                    'status' => 'processing',
                    'started_at' => now(),
                ]);
            }

            $result = $importer->importChunk(
                Storage::disk('local')->path($employeeImport->stored_path),
                $employeeImport->processed_rows,
                50
            );

            $processedRows = min(
                $result['total'],
                $employeeImport->processed_rows + $result['processed']
            );
            $completed = $processedRows >= $result['total'];
            $errors = array_slice(array_merge(
                $employeeImport->errors ?? [],
                $result['errors']
            ), 0, 200);

            $employeeImport->update([
                'status' => $completed ? 'completed' : 'processing',
                'total_rows' => $result['total'],
                'processed_rows' => $processedRows,
                'created_count' => $employeeImport->created_count + $result['created'],
                'updated_count' => $employeeImport->updated_count + $result['updated'],
                'failed_count' => $employeeImport->failed_count + $result['failed'],
                'errors' => $errors,
                'finished_at' => $completed ? now() : null,
            ]);

            if ($completed) {
                Storage::disk('local')->delete($employeeImport->stored_path);
            }

            return response()->json([
                'import' => $this->employeeImportPayload($employeeImport->fresh()),
            ]);
        } catch (\Throwable $exception) {
            $errors = $employeeImport->errors ?? [];
            $errors[] = 'Import stopped: '.$exception->getMessage();
            $employeeImport->update([
                'status' => 'failed',
                'errors' => array_slice($errors, 0, 200),
                'finished_at' => now(),
            ]);

            Log::error('Browser-driven employee import failed', [
                'employee_import_id' => $employeeImport->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'import' => $this->employeeImportPayload($employeeImport->fresh()),
            ], 422);
        } finally {
            $lock->release();
        }
    }

    private function employeeImportPayload(EmployeeImport $employeeImport): array
    {
        $percentage = $employeeImport->total_rows > 0
            ? (int) round(($employeeImport->processed_rows / $employeeImport->total_rows) * 100)
            : 0;

        return [
            'id' => $employeeImport->id,
            'filename' => $employeeImport->original_filename,
            'status' => $employeeImport->status,
            'total_rows' => $employeeImport->total_rows,
            'processed_rows' => $employeeImport->processed_rows,
            'created_count' => $employeeImport->created_count,
            'updated_count' => $employeeImport->updated_count,
            'failed_count' => $employeeImport->failed_count,
            'percentage' => min(100, $percentage),
            'errors' => $employeeImport->errors ?? [],
        ];
    }

    public function importExcelSynchronously(Request $request)
    {
        // Large imports can exceed the normal per-request PHP time limit.
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('excel_file');
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(false);
            }
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            // Header
            $header = array_shift($rows);
            $header = array_map('trim', $header);

            // Column aliases
            $columnMap = [
                'fullname' => ['fullname', 'full name', 'name', 'employee name'],
                'sex' => ['sex', 'gender'],
                'email' => ['email', 'email address'],
                'password' => ['password', 'pwd'],
                'contact_no' => ['contact_no', 'contact', 'phone', 'mobile'],
                'emp_status' => ['emp_status', 'status'],
                'item_code' => ['item_code', 'item code', 'plantilla'],
                'position_name' => ['position_name', 'position'],
                'salary_grade' => ['salary_grade', 'sg'],
                'assignment_name' => ['assignment_name', 'assignment'],
                'div_sec_unit' => ['div_sec_unit', 'division', 'unit'],
                'date_of_birth' => ['date_of_birth', 'dob'],
                'tin_no' => ['tin_no', 'tin'],
                'date_appointment' => ['date_appointment'],
                'date_last_promotion' => ['date_last_promotion'],
                'civil_service' => ['civil_service', 'eligibility'],
                'education' => ['education', 'degree'],
            ];

            // Map header columns
            $colIndex = [];
            foreach ($columnMap as $key => $aliases) {
                foreach ($header as $letter => $value) {
                    if (! $value) {
                        continue;
                    }
                    if (in_array(strtolower(trim($value)), $aliases)) {
                        $colIndex[$key] = $letter;
                        break;
                    }
                }
            }

            DB::beginTransaction();

            $created = 0;
            $updated = 0;
            $errors = [];

            foreach ($rows as $rowIdx => $row) {
                $rowNumber = $rowIdx + 2;

                try {
                    // Formatting can make empty Excel rows appear populated.
                    $hasData = count(array_filter(
                        $row,
                        static fn ($value) => $value !== null && trim((string) $value) !== ''
                    )) > 0;

                    if (! $hasData) {
                        continue;
                    }

                    // FULL NAME — if missing, auto-fill
                    $fullName = trim($row[$colIndex['fullname']] ?? '') ?: "Unknown {$rowNumber}";
                    [$firstName, $middleName, $lastName, $suffix] = $this->splitFullName($fullName);

                    // EMAIL + CONTACT
                    $email = trim($row[$colIndex['email'] ?? ''] ?? '');
                    $contactNo = trim($row[$colIndex['contact_no'] ?? ''] ?? '');

                    if (empty($email) && empty($contactNo)) {
                        $email = strtolower(Str::random(10)).'@auto.local';
                    } elseif (empty($email)) {
                        $email = strtolower(Str::random(10)).'@auto.local';
                    }

                    // SEX
                    $sexRaw = strtoupper(trim($row[$colIndex['sex'] ?? ''] ?? 'F'));
                    $sex = in_array($sexRaw, ['M', 'MALE', '1']) ? 'Male' : 'Female';

                    // ITEM CODE — auto generate if missing
                    $itemCode = trim($row[$colIndex['item_code']] ?? '');
                    if (! $itemCode) {
                        $itemCode = 'NO-CODE-'.$rowNumber;
                    }

                    // POSITION
                    $position = Position::updateOrCreate(
                        ['item_code' => $itemCode],
                        [
                            'name' => trim($row[$colIndex['position_name'] ?? ''] ?? "{$itemCode} Position"),
                            'salary_grade' => trim($row[$colIndex['salary_grade'] ?? ''] ?? null),
                            'status' => 'Occupied',
                        ]
                    );

                    // Assignment + Org Unit
                    $assignmentName = trim($row[$colIndex['assignment_name'] ?? ''] ?? 'UNASSIGNED');
                    $assignment = AssignmentPlace::firstOrCreate(['name' => $assignmentName]);

                    $orgUnitName = trim($row[$colIndex['div_sec_unit'] ?? ''] ?? 'UNASSIGNED');
                    $orgUnit = OrgUnit::firstOrCreate(['name' => $orgUnitName]);

                    // Employee Data
                    $employeeData = [
                        'first_name' => $firstName,
                        'middle_name' => $middleName ?: null,
                        'last_name' => $lastName,
                        'suffix' => $suffix ?: null,
                        'sex' => $sex,
                        'email' => $email,
                        'contact_no' => $contactNo ?: null,
                        'emp_status' => trim($row[$colIndex['emp_status'] ?? ''] ?? 'Regular'),
                        'position_name' => $position->name,
                        'assignment_name' => $assignment->name,
                        'div_sec_unit' => $orgUnit->name,
                    ];

                    // OTHER INFO
                    $otherInfoData = [
                        'date_of_birth' => $this->excelDateToYmd($row[$colIndex['date_of_birth'] ?? ''] ?? null),
                        'tin_no' => trim($row[$colIndex['tin_no'] ?? ''] ?? null),
                        'date_appointment' => $this->excelDateToYmd($row[$colIndex['date_appointment'] ?? ''] ?? null),
                        'date_last_promotion' => $this->excelDateToYmd($row[$colIndex['date_last_promotion'] ?? ''] ?? null),
                        'civil_service' => trim($row[$colIndex['civil_service'] ?? ''] ?? null),
                        'education' => trim($row[$colIndex['education'] ?? ''] ?? null),
                    ];

                    // PASSWORD from Excel or auto-generate
                    $rawPassword = trim($row[$colIndex['password'] ?? ''] ?? '');

                    // Find existing employee by email or contact_no
                    $employee = Employee::where('email', $email)
                        ->orWhere(function ($query) use ($contactNo) {
                            if ($contactNo) {
                                $query->where('contact_no', $contactNo);
                            }
                        })
                        ->first();

                    if ($employee) {
                        $user = $employee->user;
                        // === UPDATE EXISTING EMPLOYEE ===
                        $employee->update($employeeData);
                        $employee->otherInfo()->updateOrCreate(['employee_id' => $employee->id], $otherInfoData);

                        // Update associated User: email AND password
                        if ($user) {
                            $userData = ['email' => $email];
                            if ($rawPassword !== '') {
                                $userData['password'] = Hash::make($rawPassword);
                            }
                            $user->update($userData);
                        } else {
                            // Employee exists but no user account → create one
                            $newPassword = $rawPassword !== ''
                                ? $rawPassword
                                : Str::random(18).'@A1';
                            User::create([
                                'email' => $email,
                                'password' => Hash::make($newPassword),
                                'is_admin' => false,
                            ]);
                            // Optionally link: $employee->user_id = $user->id; $employee->save();
                        }

                        $updated++;
                    } else {
                        // === CREATE NEW EMPLOYEE + USER ===
                        $newPassword = $rawPassword !== ''
                            ? $rawPassword
                            : Str::random(18).'@A1';
                        $user = User::create([
                            'email' => $email,
                            'password' => Hash::make($newPassword),
                            'is_admin' => false,
                        ]);

                        $employee = Employee::create($employeeData);
                        $employee->otherInfo()->create($otherInfoData);

                        // Optional: link user to employee if you have user_id column
                        // $employee->user_id = $user->id;
                        // $employee->save();

                        $created++;
                    }

                } catch (\Throwable $e) {
                    $errors[] = "Row {$rowNumber}: ".$e->getMessage();
                }
            }

            DB::commit();

            $message = "Import completed: {$created} created, {$updated} updated";
            if ($errors) {
                $message .= ' — '.count($errors).' row(s) had errors';
            }

            return redirect()->route('employee.index')
                ->with('message', $message)
                ->with('import_errors', $errors ?: null);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withErrors(['excel_file' => 'Import failed: '.$e->getMessage()]);
        }
    }

    public function downloadExcelTemplate(Request $request)
    {
        $filters = $request->validate([
            'assignment_name' => ['nullable', 'string', 'max:255'],
            'div_sec_unit' => ['nullable', 'string', 'max:255'],
            'position_name' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $headers = [
            'fullname',
            'sex',
            'email',
            'password',
            'contact_no',
            'emp_status',
            'item_code',
            'position_name',
            'salary_grade',
            'assignment_name',
            'div_sec_unit',
            'date_of_birth',
            'tin_no',
            'date_appointment',
            'date_last_promotion',
            'civil_service',
            'education',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');

        $employeeQuery = Employee::with(['otherInfo', 'position']);

        foreach (['assignment_name', 'div_sec_unit', 'position_name'] as $field) {
            if (filled($filters[$field] ?? null)) {
                $employeeQuery->where($field, $filters[$field]);
            }
        }

        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $employees = $employeeQuery
            ->orderBy('last_name', $sortDirection)
            ->orderBy('first_name', $sortDirection)
            ->get();

        if (filled($filters['search'] ?? null)) {
            $search = Str::lower(trim($filters['search']));

            $employees = $employees->filter(function ($employee) use ($search) {
                $fullName = Str::lower(trim(implode(' ', array_filter([
                    $employee->first_name,
                    $employee->middle_name,
                    $employee->last_name,
                    $employee->suffix,
                ], fn ($value) => filled($value)))));

                return Str::contains($fullName, $search)
                    || Str::contains(Str::lower((string) $employee->position_name), $search)
                    || Str::contains(Str::lower((string) $employee->emp_status), $search)
                    || Str::contains(Str::lower((string) $employee->assignment_name), $search)
                    || Str::contains(Str::lower((string) $employee->div_sec_unit), $search);
            });
        }

        $rows = $employees->map(function ($employee) {
            $lastName = trim((string) ($employee->last_name ?? ''));
            $nameTail = trim(implode(' ', array_filter([
                $employee->first_name,
                $employee->middle_name,
                $employee->suffix,
            ], fn ($value) => filled($value))));

            $fullName = $lastName;
            if ($lastName !== '' && $nameTail !== '') {
                $fullName = $lastName.', '.$nameTail;
            } elseif ($nameTail !== '') {
                $fullName = $nameTail;
            }

            return [
                $fullName,
                $employee->sex,
                $employee->email,
                '',
                $employee->contact_no,
                $employee->emp_status,
                $employee->position?->item_code,
                $employee->position_name,
                $employee->position?->salary_grade,
                $employee->assignment_name,
                $employee->div_sec_unit,
                $employee->otherInfo?->date_of_birth,
                $employee->otherInfo?->tin_no,
                $employee->otherInfo?->date_appointment,
                $employee->otherInfo?->date_last_promotion,
                $employee->otherInfo?->civil_service,
                $employee->otherInfo?->education,
            ];
        })->all();

        if (! empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'carpis-denr_employee_list.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function formatPositionHistories(iterable $histories): array
    {
        return collect($histories)->map(function (EmployeePositionHistory $history) {
            $placement = $this->positionPlacement(
                $history->position,
                $history->assignment_name,
                $history->div_sec_unit,
            );

            return [
                'id' => $history->id,
                'event' => $history->event,
                'position_name' => $history->position_name,
                'item_code' => $history->item_code,
                'assignment_name' => $placement['assignment_name'],
                'div_sec_unit' => $placement['div_sec_unit'],
                'status_from' => $history->status_from,
                'status_to' => $history->status_to,
                'recorded_at' => $this->formatDateTimeForDisplay($history->recorded_at),
            ];
        })->values()->all();
    }

    private function formatDateTimeForDisplay(CarbonInterface|string|null $dateTime): ?string
    {
        if (! $dateTime) {
            return null;
        }

        $utcDateTime = $dateTime instanceof CarbonInterface
            ? $dateTime->copy()
            : Carbon::parse($dateTime, 'UTC');

        return $utcDateTime
            ->setTimezone(config('app.display_timezone', 'Asia/Manila'))
            ->toDateTimeString();
    }

    private function positionPlacement(?Position $position, ?string $fallbackAssignment, ?string $fallbackDivision): array
    {
        if (! $position) {
            return [
                'assignment_name' => $fallbackAssignment,
                'div_sec_unit' => $fallbackDivision,
            ];
        }

        $description = trim((string) $position->desc);
        $orgCode = trim((string) $position->org_code);
        $assignment = $fallbackAssignment;
        $division = $description !== '' ? $description : $fallbackDivision;

        foreach (config('organizational_scope.offices', []) as $officeName => $officeConfig) {
            $assignments = $officeConfig['assignments'] ?? [$officeName];
            $orgCodes = $officeConfig['org_codes'] ?? [];
            $descriptions = $officeConfig['position_descriptions'] ?? $assignments;

            if ($description !== '' && in_array($description, $assignments, true)) {
                $assignment = $description;
                break;
            }

            if (($orgCode !== '' && in_array($orgCode, $orgCodes, true))
                || ($description !== '' && in_array($description, $descriptions, true))) {
                $assignment = $officeName;
                break;
            }
        }

        return [
            'assignment_name' => $assignment,
            'div_sec_unit' => $division,
        ];
    }

    private function splitFullName(string $fullName): array
    {
        $fullName = trim($fullName);
        if (empty($fullName)) {
            return ['', null, '', null];
        }

        // Known suffixes
        $suffixes = ['Jr', 'JR', 'Sr', 'SR', 'II', 'III', 'IV', 'V', 'VI', 'PhD', 'MD', 'CPA', 'RN'];
        $suffixMap = [];
        foreach ($suffixes as $s) {
            $clean = strtoupper(rtrim($s, '.'));
            $suffixMap[$clean] = $clean;
            $withDot = $clean.'.';
            $suffixMap[$withDot] = $clean;
        }

        $suffix = null;

        // Remove suffix from end
        $parts = preg_split('/\s+/', $fullName);
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $cleanWord = strtoupper(rtrim($parts[$i], '.'));
            if (isset($suffixMap[$cleanWord])) {
                $suffix = $suffixMap[$cleanWord];
                unset($parts[$i]);
                break;
            }
        }

        $fullName = trim(implode(' ', $parts));

        // Normalize comma: exactly one space after
        $fullName = preg_replace('/\s*,\s*/', ', ', $fullName);

        // Compound middle surnames (common in Filipino names)
        $compoundSurnames = [
            'de la cruz', 'de los santos', 'de la peña', 'de la rosa', 'de los reyes',
            'del rosario', 'san juan', 'san jose', 'santa maria', 'san pedro',
            'san miguel', 'del mundo', 'de vera', 'de guzman', 'de jesus',
            'de castro', 'de leon', 'delos reyes', 'del pilar', 'van der sar',
            'da vinci', 'dos santos', 'ben david', 'dela fuente', 'de la torre',
        ];

        // Map 1: exact lowercase phrase → UPPERCASE version
        $exactCompoundMap = [];
        // Map 2: spaceless lowercase → UPPERCASE version (for "DELAFUENTE")
        $spacelessCompoundMap = [];
        foreach ($compoundSurnames as $cs) {
            $exactCompoundMap[strtolower($cs)] = strtoupper($cs);
            $spacelessCompoundMap[str_replace(' ', '', strtolower($cs))] = strtoupper($cs);
        }

        // === Main target format: "LASTNAME, FIRST [SECOND] [MIDDLE...]" ===
        if (preg_match('/^([^,]+),\s+(.+)$/', $fullName, $matches)) {
            $lastName = strtoupper(trim($matches[1]));
            $remaining = trim($matches[2]);
            $remainingParts = preg_split('/\s+/', $remaining);

            if (empty($remainingParts)) {
                return ['', null, $lastName, $suffix];
            }

            $wordCount = count($remainingParts);

            // Convert to upper for output, keep original for matching
            $upperParts = array_map('strtoupper', $remainingParts);

            if ($wordCount === 1) {
                return [$upperParts[0], null, $lastName, $suffix];
            }

            if ($wordCount === 2) {
                // Check if second part is compound
                $keyExact = strtolower($remainingParts[1]);
                $keySpaceless = str_replace(' ', '', $keyExact);

                if (isset($exactCompoundMap[$keyExact])) {
                    return [$upperParts[0], $exactCompoundMap[$keyExact], $lastName, $suffix];
                }
                if (isset($spacelessCompoundMap[$keySpaceless])) {
                    return [$upperParts[0], $spacelessCompoundMap[$keySpaceless], $lastName, $suffix];
                }

                // Normal first + middle
                return [$upperParts[0], $upperParts[1], $lastName, $suffix];
            }

            // === 3 or more words: first 2 = first name, rest = middle ===
            $firstName = $upperParts[0].' '.$upperParts[1];
            $middleCandidates = array_slice($remainingParts, 2); // original case for matching
            $middleUpper = array_slice($upperParts, 2);

            $middleName = null;

            // Try longest match first
            for ($len = min(4, count($middleCandidates)); $len >= 2; $len--) {
                $slice = array_slice($middleCandidates, 0, $len);

                // First: try exact spaced match
                $exactKey = strtolower(implode(' ', $slice));
                if (isset($exactCompoundMap[$exactKey])) {
                    $middleName = $exactCompoundMap[$exactKey];
                    // Append extra words if any
                    if ($len < count($middleCandidates)) {
                        $middleName .= ' '.implode(' ', array_slice($middleUpper, $len));
                    }
                    break;
                }

                // Second: try spaceless match
                $spacelessKey = str_replace(' ', '', strtolower(implode(' ', $slice)));
                if (isset($spacelessCompoundMap[$spacelessKey])) {
                    $middleName = $spacelessCompoundMap[$spacelessKey];
                    if ($len < count($middleCandidates)) {
                        $middleName .= ' '.implode(' ', array_slice($middleUpper, $len));
                    }
                    break;
                }
            }

            // If no compound matched, just join all remaining as middle
            if ($middleName === null) {
                $middleName = implode(' ', $middleUpper);
            }

            return [$firstName, $middleName, $lastName, $suffix];
        }

        // === Fallback: no comma or malformed ===
        $parts = preg_split('/\s+/', $fullName);
        if (empty($parts)) {
            return ['', null, '', $suffix];
        }

        // Check for compound last name (using both maps)
        $lastName = '';
        $lastNameWordCount = 1;

        if (count($parts) >= 3) {
            $slice = array_slice($parts, -3);
            $exactKey = strtolower(implode(' ', $slice));
            $spacelessKey = str_replace(' ', '', $exactKey);
            if (isset($exactCompoundMap[$exactKey])) {
                $lastName = $exactCompoundMap[$exactKey];
                $lastNameWordCount = 3;
            } elseif (isset($spacelessCompoundMap[$spacelessKey])) {
                $lastName = $spacelessCompoundMap[$spacelessKey];
                $lastNameWordCount = 3;
            }
        }

        if ($lastName === '' && count($parts) >= 2) {
            $slice = array_slice($parts, -2);
            $exactKey = strtolower(implode(' ', $slice));
            $spacelessKey = str_replace(' ', '', $exactKey);
            if (isset($exactCompoundMap[$exactKey])) {
                $lastName = $exactCompoundMap[$exactKey];
                $lastNameWordCount = 2;
            } elseif (isset($spacelessCompoundMap[$spacelessKey])) {
                $lastName = $spacelessCompoundMap[$spacelessKey];
                $lastNameWordCount = 2;
            }
        }

        if ($lastName === '') {
            $lastName = strtoupper(array_pop($parts));
        } else {
            for ($i = 0; $i < $lastNameWordCount; $i++) {
                array_pop($parts);
            }
        }

        $firstName = strtoupper(implode(' ', $parts));
        $middleName = null;

        return [$firstName, $middleName, $lastName, $suffix];
    }

    // Helper to avoid duplication
    private function detectCompoundSurname(string $name, array $exactMap, array $spacelessMap): string
    {
        $lower = strtolower($name);
        $spaceless = str_replace(' ', '', $lower);

        if (isset($exactMap[$lower])) {
            return $exactMap[$lower];
        }
        if (isset($spacelessMap[$spaceless])) {
            return $spacelessMap[$spaceless];
        }

        return strtoupper($name); // no match → return as-is
    }

    private function ensureAssignmentIsAllowed(Request $request, ?string $assignment): void
    {
        $allowedAssignments = $this->organizationalScope->allowedAssignments($request->user());

        if ($allowedAssignments !== null && ! in_array($assignment, $allowedAssignments, true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'assignment_name' => 'The selected assignment is outside your assigned office scope.',
            ]);
        }
    }

    private function excelDateToYmd($excelDate): ?string
    {
        if (empty($excelDate)) {
            return null;
        }

        // Handle numeric Excel serial dates
        if (is_numeric($excelDate)) {
            try {
                $excelDate = (float) $excelDate;

                $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excelDate);
                $date = date('Y-m-d', $timestamp);  // CHANGED TO YYYY-MM-DD

                return $date;
            } catch (\Exception $e) {
                Log::error('Failed to convert Excel serial date', [
                    'value' => $excelDate,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return null;
            }
        }

        // Handle text-based dates (e.g., MM/DD/YY, MM-DD-YY, YYYY-MM-DD)
        try {
            // Normalize common date formats
            $excelDate = trim($excelDate);
            // Replace common separators
            $excelDate = preg_replace('/[\/.-]/', '-', $excelDate);

            // Try common formats, prioritizing MM-DD-YY/YYYY
            $dateTime = \DateTime::createFromFormat('m-d-y', $excelDate) ?: // MM-DD-YY
                      \DateTime::createFromFormat('m-d-Y', $excelDate) ?: // MM-DD-YYYY
                      \DateTime::createFromFormat('Y-m-d', $excelDate) ?: // YYYY-MM-DD
                      \DateTime::createFromFormat('d-m-Y', $excelDate);     // DD-MM-YYYY (fallback)

            if ($dateTime === false) {
                Log::warning('Invalid text date format', ['value' => $excelDate]);

                return null;
            }

            // Handle two-digit years
            $year = (int) $dateTime->format('Y');
            if ($year < 100) {
                $year += ($year < 70) ? 2000 : 1900;
                $dateTime->setDate($year, (int) $dateTime->format('m'), (int) $dateTime->format('d'));
            }

            $date = $dateTime->format('Y-m-d');  // CHANGED TO YYYY-MM-DD

            return $date;
        } catch (\Exception $e) {
            Log::error('Failed to convert text date', [
                'value' => $excelDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
