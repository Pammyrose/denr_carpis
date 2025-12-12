<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\OrgUnit;
use App\Models\Position;
use App\Models\AssignmentPlace;
use App\Models\ArchivedEmployee;
use App\Models\ArchivedPosition;
use App\Models\OtherInfo; // Add import for OtherInfo
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\EmpStatus;
use App\Models\Inventory;



class EmployeeController extends Controller
{
   private function getDropdownData()
    {
        $dropdowns = [
            'orgUnits' => OrgUnit::all()->map(fn ($o) => [
                'value' => (string)$o->name,
                'label' => $o->name,
            ]),
            'positions' => Position::all()->map(fn ($p) => [
                'value' => (string)$p->name,
                'label' => $p->name . ($p->status === 'Occupied' ? ' (Occupied)' : ''),
                'salary_grade' => $p->salary_grade,
                'item_code' => $p->item_code,
                'org_code' => $p->org_code ?? '',
            ]),
            'assignmentPlaces' => AssignmentPlace::all()->map(fn ($a) => [
                'value' => (string)$a->name,
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
            $employee = Employee::findOrFail($request->archive_employee_id);
            DB::beginTransaction();

            $otherInfo = $employee->otherInfo;

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
                'assignment_id' => $employee->assignment_id ?? $employee->assignment_name,
                'div_sec_unit' => $employee->org_unit_id ?? $employee->div_sec_unit,
                'archived_at' => now()->toDateTimeString(),

                'date_of_birth' => $otherInfo?->date_of_birth,
                'tin_no' => $otherInfo?->tin_no,
                'date_appointment' => $otherInfo?->date_appointment,
                'date_last_promotion' => $otherInfo?->date_last_promotion,
                'civil_service' => $otherInfo?->civil_service,
                'education' => $otherInfo?->education,
            ];

            // Load existing archived employees from storage
            $filePath = 'archived_employees.json';
            $archivedEmployees = Storage::disk('local')->exists($filePath)
                ? json_decode(Storage::disk('local')->get($filePath), true)
                : [];
            $archivedEmployees[$employee->id] = $archivedData;

            // Save to storage
            Storage::disk('local')->put($filePath, json_encode($archivedEmployees, JSON_PRETTY_PRINT));

            Log::info('EmployeeController: Archiving employee to storage', $archivedData);

            // === FREE THE POSITION WHEN ARCHIVING ===
if ($employee->position_name) {
    Position::where('name', $employee->position_name)
        ->update(['status' => 'Vacant']);   // ← changed from null to 'Vacant'
    Log::info('Position freed during archiving', ['position' => $employee->position_name]);
}

            // Delete the employee from the Employee table
            if ($otherInfo) {
                $otherInfo->delete();
            }

            $assets = Inventory::where('assigned', $employee->id)->get();
            foreach ($assets as $asset) {
                $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
                $asset->update(['assigned_to_name' => $fullName]);
            }
            Log::info('EmployeeController: Preserved employee name in assigned assets', [
                'employee_id' => $employee->id,
                'asset_count' => $assets->count(),
            ]);

            $employee->delete();
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
            'position_name' => 'required|exists:positions,name',
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
            'employee_id' => $employee->id
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
        return back()->withErrors(['general' => 'Failed to process request: ' . $e->getMessage()])->withInput();
    }
}

    public function index(Request $request)
{
    try {
        // Capture selected filter
        $selectedDivision = $request->input('div_sec_unit');

        // Base query with relations
        $query = Employee::with(['position', 'assignment', 'orgUnit', 'otherInfo']);

        // Apply filter if selected
        if (!empty($selectedDivision)) {
            $query->where('div_sec_unit', $selectedDivision);
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

                'position_name' => $employee->position_name ? (string)$employee->position_name : null,
                'position_label' => $employee->position ? $employee->position->name : 'N/A',

                'assignment_name' => $employee->assignment_name ? (string)$employee->assignment_name : null,
                'assignment_label' => $employee->assignment ? $employee->assignment->name : 'N/A',

                'div_sec_unit' => $employee->div_sec_unit ? (string)$employee->div_sec_unit : null,
                'div_sec_unit_label' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A',

                'date_of_birth' => $employee->otherInfo->date_of_birth ?? null,
                'tin_no' => $employee->otherInfo->tin_no ?? null,
                'date_appointment' => $employee->otherInfo->date_appointment ?? null,
                'date_last_promotion' => $employee->otherInfo->date_last_promotion ?? null,
                'civil_service' => $employee->otherInfo->civil_service ?? null,
                'education' => $employee->otherInfo->education ?? null,
            ];
        })->toArray();

        $currentEmployee = auth()->user()?->employee;

        // Unique dropdown values
        $divisions = Employee::select('div_sec_unit')
            ->distinct()
            ->orderBy('div_sec_unit')
            ->get();

        return Inertia::render('employee/Index', [
            'employees' => $employees,
            'divisions' => $divisions,
            'selectedDivision' => $selectedDivision,
            'dropdowns' => $this->getDropdownData(),
            'error' => null,
        ]);
    } catch (\Exception $e) {
        Log::error('EmployeeController Error', ['error' => $e->getMessage()]);

        return Inertia::render('employee/Index', [
            'employees' => [],
            'divisions' => [],
            'dropdowns' => $this->getDropdownData(),
            'error' => 'Failed to load employees.',
        ]);
    }
}



    public function view($id)
{
    try {
        $employee = Employee::with(['otherInfo', 'assets', 'position', 'assignment', 'orgUnit'])->findOrFail($id);
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
        $employees = Employee::all()->map(function ($e) {
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
                'position_id' => $employee->position_id ? (string)$employee->position_id : null,
                'assignment_id' => $employee->assignment_id ? (string)$employee->assignment_id : null,
                'org_unit_id' => $employee->org_unit_id ? (string)$employee->org_unit_id : null,
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
    Log::info('EmployeeController@update: Incoming request data: ' . json_encode($request->all()));

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'suffix' => 'nullable|string|max:50',
        'sex' => 'required|string|in:Male,Female',
        'email' => 'nullable|email|max:255',
        'contact_no' => [
            'required',
            'digits_between:10,15',
            Rule::unique('employees', 'contact_no')->ignore($employee->id),
        ],
        'emp_status' => 'required|exists:emp_status,name',
        'position_id' => 'required|exists:positions,name',
        'assignment_id' => 'required|exists:assignments,name',
        'org_unit_id' => 'required|exists:div_sec_units,name',
        'password' => [
            'nullable',
            'string',
            'min:16',
            'confirmed',
            'regex:/[a-z]/',      // Must contain lowercase
            'regex:/[A-Z]/',      // Must contain uppercase
            'regex:/[0-9]/',      // Must contain a number
            'regex:/[@$!%*?&]/',  // Must contain a special character
        ],
        'date_of_birth' => 'required|date',
        'tin_no' => 'nullable|string|max:255',
        'date_appointment' => 'nullable|date',
        'date_last_promotion' => 'nullable|date',
        'civil_service' => 'nullable|string|max:255',
        'education' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
        Log::warning('Validation failed in EmployeeController@update: ' . json_encode($validator->errors()));
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        // Update employee basic info
        $employeeData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'sex' => $request->sex,
            'email' => $request->has('email') ? $request->email : $employee->email,
            'contact_no' => $request->contact_no,
            'emp_status' => $request->emp_status,
            'position_name' => $request->position_id,
            'assignment_name' => $request->assignment_id,
            'div_sec_unit' => $request->org_unit_id,
        ];

        $employee->update($employeeData);

        // Update or create OtherInfo
        $otherInfoData = [
            'date_of_birth' => $request->date_of_birth,
            'tin_no' => $request->tin_no,
            'date_appointment' => $request->date_appointment,
            'date_last_promotion' => $request->date_last_promotion,
            'civil_service' => $request->civil_service,
            'education' => $request->education,
        ];

        $employee->otherInfo()->updateOrCreate(['employee_id' => $employee->id], $otherInfoData);

        // Update related user account
        if ($employee->user) {

            $userData = []; // <-- IMPORTANT: Always initialize

            // Update password only when provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            // Update email only when present in request
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }

            // Update user only if needed
            if (!empty($userData)) {
                $employee->user->update($userData);
            }
        }

        DB::commit();

        Log::info('EmployeeController@update: Employee and OtherInfo updated', [
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('employee.index')->with('message', 'Employee updated successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in EmployeeController@update: ' . $e->getMessage());
        return back()
            ->withErrors(['general' => 'Failed to update employee: ' . $e->getMessage()])
            ->withInput();
    }
}


    public function destroy(Employee $employee)
    {
        try {
            Log::info('EmployeeController: Deleting employee', ['employee_id' => $employee->id]);
            $employee->delete();
            Log::info('EmployeeController: Employee deleted', ['employee_id' => $employee->id]);
            return redirect()->route('employee.index')->with('message', 'Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to delete employee', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to delete employee: ' . $e->getMessage()]);
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
            $formattedEmployees = array_values($archivedEmployees); // Convert to array for frontend

            Log::info('EmployeeController: Fetched archived employees from storage', [
                'employee_count' => count($formattedEmployees),
            ]);

            return Inertia::render('employee/Archived', [
                'archivedEmployees' => $formattedEmployees,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to fetch archived employees', [
                'error' => $e->getMessage(),
            ]);
            return Inertia::render('employee/Archived', [
                'archivedEmployees' => [],
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

            if (!$archivedEmployee) {
                throw new \Exception('Archived employee not found.');
            }

            if (Employee::where('email', $archivedEmployee['email'])->exists()) {
                throw new \Exception('Email already exists in the employees table.');
            }

            $user = User::where('email', $archivedEmployee['email'])->first();
            if (!$user) {
                $fullName = trim("{$archivedEmployee['first_name']} {$archivedEmployee['middle_name']} {$archivedEmployee['last_name']} {$archivedEmployee['suffix']}");
                $user = User::create([
                    'name' => $fullName,
                    'email' => $archivedEmployee['email'],
                    'password' => Hash::make('default_password'),
                    'is_admin' => false,
                ]);
                Log::info('EmployeeController: Created new User record during unarchive', [
                    'email' => $archivedEmployee['email'],
                    'user_id' => $user->id,
                ]);
            }

            $employee = Employee::create([
                'id' => $archivedEmployee['id'],
                'first_name' => $archivedEmployee['first_name'],
                'middle_name' => $archivedEmployee['middle_name'],
                'last_name' => $archivedEmployee['last_name'],
                'suffix' => $archivedEmployee['suffix'],
                'sex' => $archivedEmployee['sex'],
                'contact_no' => $archivedEmployee['contact_no'],
                'email' => $archivedEmployee['email'],
                'emp_status' => $archivedEmployee['emp_status'],
                'position_name' => $archivedEmployee['position_id'],
                'assignment_name' => $archivedEmployee['assignment_id'],
                'div_sec_unit' => $archivedEmployee['div_sec_unit'],
            ]);

            // ADDED: Set position status back to Vacant when unarchiving
            if (!empty($archivedEmployee['position_id'])) {
                $positionName = $archivedEmployee['position_id'];
                $position = Position::where('name', $positionName)->first();

                if ($position) {
                    $position->update(['status' => 'Vacant']);
                    Log::info('EmployeeController: Position status set to Vacant on unarchive', [
                        'position_name' => $positionName,
                        'employee_id'   => $employee->id,
                    ]);
                }
            }

            $otherInfoData = [
                'employee_id' => $employee->id,
                'date_of_birth' => $archivedEmployee['date_of_birth'] ?? null,
                'tin_no' => $archivedEmployee['tin_no'] ?? null,
                'date_appointment' => $archivedEmployee['date_appointment'] ?? null,
                'date_last_promotion' => $archivedEmployee['date_last_promotion'] ?? null,
                'civil_service' => $archivedEmployee['civil_service'] ?? null,
                'education' => $archivedEmployee['education'] ?? null,
            ];

            if (array_filter($otherInfoData, fn($v) => !is_null($v))) {
                $employee->otherInfo()->create($otherInfoData);
                Log::info('EmployeeController: OtherInfo restored during unarchive', [
                    'employee_id' => $employee->id,
                ]);
            }

            unset($archivedEmployees[$archivedEmployeeId]);
            Storage::disk('local')->put($filePath, json_encode($archivedEmployees, JSON_PRETTY_PRINT));

            DB::commit();

            Log::info('EmployeeController: Employee unarchived successfully', [
                'archived_employee_id' => $archivedEmployeeId,
                'employee_id' => $employee->id,
            ]);

            return redirect()->route('employee.index')->with('message', "Employee ID {$employee->id} unarchived successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to unarchive employee', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to unarchive employee: ' . $e->getMessage()]);
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
            return back()->withErrors(['general' => 'Failed to create organizational unit: ' . $e->getMessage()])->withInput();
        }
    }

    public function position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:255|unique:positions,item_code',
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string|max:1000',
            'salary_grade' => 'nullable|string|max:50',
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

            return redirect()->route('employee.index')->with([
                'message' => 'Position created successfully',
                'position' => [
                    'value' => (string)$position->id,
                    'label' => $position->name,
                    'item_code' => $position->item_code,
                    'salary_grade' => $position->salary_grade,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create position', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to create position: ' . $e->getMessage()])->withInput();
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
                    'value' => (string)$position->id,
                    'label' => $position->name,
                    'salary_grade' => $position->salary_grade,
                    'item_code' => $position->item_code,
                    'org_code' => $position->org_code ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to update salary grade', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to update salary grade: ' . $e->getMessage()])->withInput();
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
                    'value' => (string)$assignmentPlace->id,
                    'label' => $assignmentPlace->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create assignment place', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to create assignment place: ' . $e->getMessage()])->withInput();
        }
    }

public function importExcel(Request $request)
{
    $request->validate([
        'excel_file' => 'required|file|mimes:xlsx,xls',
    ]);

    try {
        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Header
        $header = array_shift($rows);
        $header = array_map('trim', $header);

        // Column aliases
        $columnMap = [
            'fullname' => ['fullname', 'full name', 'name', 'employee name'],
            'sex'      => ['sex', 'gender'],
            'email'    => ['email', 'email address'],
            'password' => ['password', 'pwd'],
            'contact_no' => ['contact_no', 'contact', 'phone', 'mobile'],
            'emp_status' => ['emp_status', 'status'],
            'item_code'  => ['item_code', 'item code', 'plantilla'],
            'position_name' => ['position_name', 'position'],
            'salary_grade'  => ['salary_grade', 'sg'],
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
                if (!$value) continue;
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
                // FULL NAME — if missing, auto-fill
                $fullName = trim($row[$colIndex['fullname']] ?? '') ?: "Unknown {$rowNumber}";
                [$firstName, $middleName, $lastName, $suffix] = $this->splitFullName($fullName);

                // EMAIL + CONTACT
                $email = trim($row[$colIndex['email'] ?? ''] ?? '');
                $contactNo = trim($row[$colIndex['contact_no'] ?? ''] ?? '');

                if (empty($email) && empty($contactNo)) {
                    $email = strtolower(Str::random(10)) . '@auto.local';
                }

                // SEX
                $sexRaw = strtoupper(trim($row[$colIndex['sex'] ?? ''] ?? 'F'));
                $sex = in_array($sexRaw, ['M','MALE','1']) ? 'Male' : 'Female';

                // ITEM CODE — auto generate if missing
                $itemCode = trim($row[$colIndex['item_code']] ?? '');
                if (!$itemCode) {
                    $itemCode = "NO-CODE-" . $rowNumber;
                }

                // POSITION
$position = Position::updateOrCreate(
    ['item_code' => $itemCode],
    [
        'name'         => trim($row[$colIndex['position_name'] ?? ''] ?? "{$itemCode} Position"),
        'salary_grade' => trim($row[$colIndex['salary_grade'] ?? ''] ?? null),
        'status'       => 'Occupied', // Now guaranteed!
    ]
);

                // Assignment + Org Unit
                $assignmentName = trim($row[$colIndex['assignment_name'] ?? ''] ?? 'UNASSIGNED');
                $assignment = AssignmentPlace::firstOrCreate(['name' => $assignmentName]);

                $orgUnitName = trim($row[$colIndex['div_sec_unit'] ?? ''] ?? 'UNASSIGNED');
                $orgUnit = OrgUnit::firstOrCreate(['name' => $orgUnitName]);

                // Employee Data
                $employeeData = [
                    'first_name'      => $firstName,
                    'middle_name'     => $middleName ?: null,
                    'last_name'       => $lastName,
                    'suffix'          => $suffix ?: null,
                    'sex'             => $sex,
                    'email'           => $email ?: null,
                    'contact_no'      => $contactNo ?: null,
                    'emp_status'      => trim($row[$colIndex['emp_status'] ?? ''] ?? 'Regular'),
                    'position_name'   => $position->name,
                    'assignment_name' => $assignment->name,
                    'div_sec_unit'    => $orgUnit->name,
                ];

                // OTHER INFO
                $otherInfoData = [
                    'date_of_birth'       => $this->excelDateToYmd($row[$colIndex['date_of_birth'] ?? ''] ?? null),
                    'tin_no'              => trim($row[$colIndex['tin_no'] ?? ''] ?? null),
                    'date_appointment'    => $this->excelDateToYmd($row[$colIndex['date_appointment'] ?? ''] ?? null),
                    'date_last_promotion' => $this->excelDateToYmd($row[$colIndex['date_last_promotion'] ?? ''] ?? null),
                    'civil_service'       => trim($row[$colIndex['civil_service'] ?? ''] ?? null),
                    'education'           => trim($row[$colIndex['education'] ?? ''] ?? null),
                ];

                // Find existing employee
                $employee = Employee::where('email', $email)
                    ->orWhere('contact_no', $contactNo)
                    ->first();

                // Password — auto-create if missing
                $rawPassword = trim($row[$colIndex['password'] ?? ''] ?? '');
                if (!$rawPassword) {
                    $rawPassword = Str::random(18) . '@A1';
                }

                // If employee exists → update
                if ($employee) {

                    $employee->update($employeeData);
                    $employee->otherInfo()->updateOrCreate(['employee_id' => $employee->id], $otherInfoData);

                    if ($employee->user) {
                        $employee->user->update(['password' => Hash::make($rawPassword)]);
                    }

                    $updated++;

                } else {
                    // Create new user + employee
                    $user = User::create([
                        'email' => $email,
                        'password' => Hash::make($rawPassword),
                        'is_admin' => false,
                    ]);

                    $employee = Employee::create($employeeData);
                    $employee->otherInfo()->create($otherInfoData);

                    $created++;
                }

            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                // NO SKIP — we continue processing next row automatically
            }
        }

        DB::commit();

        $message = "Import completed: {$created} created, {$updated} updated";
        if ($errors) $message .= " — " . count($errors) . " warnings";

        return redirect()->route('employee.index')
            ->with('message', $message)
            ->with('import_errors', $errors ?: null);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Excel import failed', ['error' => $e->getMessage()]);
        return back()->withErrors(['excel_file' => 'Import failed: ' . $e->getMessage()]);
    }
}


    private function splitFullName(string $fullName): array
{
    $fullName = trim($fullName);
    if (empty($fullName)) {
        return ['', null, '', null];
    }

    // Common suffixes
    $suffixes = ['Jr', 'Sr', 'II', 'III', 'IV', 'V', 'VI', 'PhD', 'MD', 'CPA', 'RN'];
    $suffix = null;

    // Remove and capture suffix if at the end
    $parts = preg_split('/\s+/', $fullName);
    $lastWord = end($parts);

    if (in_array(strtoupper($lastWord), array_map('strtoupper', $suffixes))) {
        $suffix = strtoupper($lastWord);
        array_pop($parts); // remove suffix
        $fullName = trim(implode(' ', $parts));
    }

    // Remove comma if exists (common: "Dela Cruz, Juan A.")
    $fullName = str_replace([' ,', ', '], ',', $fullName);
    if (str_contains($fullName, ',')) {
        $nameParts = array_map('trim', explode(',', $fullName, 2));

        $lastName = $nameParts[0] ?? '';
        $remaining = $nameParts[1] ?? '';

        // Clean up remaining part: "Juan A." → first: Juan, middle: A
        $remainingParts = preg_split('/\s+/', trim($remaining));
        $firstName = $remainingParts[0] ?? '';
        $middleName = implode(' ', array_slice($remainingParts, 1));

        // If middle name is just one letter + dot, keep it
        if (strlen($middleName) <= 3 && preg_match('/^[A-Za-z]\.?$/i', $middleName)) {
            $middleName = strtoupper(str_replace('.', '', $middleName));
        } elseif (empty($middleName)) {
            $middleName = null;
        }

        return [
            $firstName,
            $middleName,
            $lastName,
            $suffix
        ];
    }

    // Fallback: No comma → assume "First Middle Last" order
    $parts = preg_split('/\s+/', $fullName);
    if (count($parts) === 1) {
        return [$parts[0], null, '', $suffix];
    }

    $firstName = array_shift($parts);
    $lastName = array_pop($parts);
    $middleName = !empty($parts) ? implode(' ', $parts) : null;

    return [$firstName, $middleName, $lastName, $suffix];
}

private function excelDateToYmd($excelDate): ?string
{
    if (empty($excelDate)) {
        Log::warning('Invalid Excel date: empty', ['value' => $excelDate]);
        return null;
    }

    // Handle numeric Excel serial dates
    if (is_numeric($excelDate)) {
        try {
            $excelDate = (float)$excelDate;
            // Excel dates range from 1 (1900-01-01) to ~2958465 (9999-12-31)
            if ($excelDate < 1 || $excelDate > 2958465) {
                Log::warning("Invalid Excel date (out of range): {$excelDate}");
                return null;
            }
            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($excelDate);
            $date = date('Y-m-d', $timestamp);
            Log::debug("Converted Excel serial date: {$excelDate} to {$date}");
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
        // Parse the date
        $dateTime = \DateTime::createFromFormat('m-d-y', $excelDate) ?: // MM-DD-YY or MM/DD/YY
                  \DateTime::createFromFormat('m-d-Y', $excelDate) ?: // MM-DD-YYYY
                  \DateTime::createFromFormat('Y-m-d', $excelDate);   // YYYY-MM-DD

        if ($dateTime === false) {
            Log::warning('Invalid text date format', ['value' => $excelDate]);
            return null;
        }

        // Ensure year is four digits (handle two-digit years)
        $year = (int)$dateTime->format('Y');
        if ($year < 100) { // Two-digit year
            // Assume years < 70 are 20XX, otherwise 19XX
            $year += ($year < 70) ? 2000 : 1900;
            $dateTime->setDate($year, (int)$dateTime->format('m'), (int)$dateTime->format('d'));
        }

        $date = $dateTime->format('Y-m-d');
        Log::debug("Converted text date: {$excelDate} to {$date}");
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