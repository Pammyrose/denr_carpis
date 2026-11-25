<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Services\OrganizationalScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function __construct(private readonly OrganizationalScope $organizationalScope) {}

    public function index()
    {
        $users = $this->organizationalScope
            ->applyToUsers(User::query(), auth()->user())
            ->get()
            ->map(function ($user) {
            $employee = Employee::where('email', $user->email)
                ->with(['assets', 'position', 'assignment', 'orgUnit'])
                ->first();

            $fullName = $this->displayName($employee);

            $profileUser = [
                'id' => (int) $user->id,
                'full_name' => $fullName,
                'email' => $user->email,
                'is_admin' => (int) $user->is_admin,
                'office_scope' => $this->organizationalScope->assignedOffice($user),
                'employee' => null,
                'assets' => [],
            ];

            if ($employee) {
                $profileUser['employee'] = [
                    'sex' => $employee->sex ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',
                    'position_name' => $employee->position?->name ?? 'N/A',
                    'assignment_name' => $employee->assignment?->name ?? 'N/A',
                    'div_sec_unit' => $employee->orgUnit?->name ?? 'N/A',
                ];

                $profileUser['assets'] = $employee->assets->map(function ($asset) {
                    // Safely format dates – works whether they are Carbon, string, or null
                    $purchaseDate = 'N/A';
                    if ($asset->purchase_date) {
                        $purchaseDate = is_string($asset->purchase_date)
                            ? $asset->purchase_date
                            : $asset->purchase_date->format('Y-m-d');
                    }

                    $returnDate = 'N/A';
                    if ($asset->return_date) {
                        $returnDate = is_string($asset->return_date)
                            ? $asset->return_date
                            : $asset->return_date->format('Y-m-d');
                    }

                    return [
                        'id' => $asset->asset_id ?? $asset->id ?? 'N/A',
                        'name' => $asset->name ?? 'N/A',
                        'category' => $asset->category ?? 'N/A',
                        'location' => $asset->location ?? 'N/A',
                        'purchase_date' => $purchaseDate,
                        'return_date' => $returnDate,
                    ];
                })->toArray();
            }

            return $profileUser;
        })->toArray();

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        abort_unless($this->organizationalScope->canAccessUser(auth()->user(), $user), 403);

        Log::info('UsersController@show: Starting request processing', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        // Fetch employee with relationships
        $employee = null;
        try {
            $employee = Employee::where('email', $user->email)
                ->with(['assets', 'position', 'assignment', 'orgUnit'])
                ->first();
            Log::info('UsersController@show: Employee query result', [
                'email' => $user->email,
                'employee_found' => $employee ? true : false,
                'employee_id' => $employee ? $employee->id : 'null',
            ]);
        } catch (\Exception $e) {
            Log::error('UsersController@show: Employee query failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Construct full name from employee data or fallback
        $fullName = $this->displayName($employee);

        // Build profileUser array
        $profileUser = [
            'id' => $user->id ?? 0,
            'full_name' => $fullName,
            'email' => $user->email ?? 'N/A',
            'is_admin' => isset($user->is_admin) ? (int) $user->is_admin : 2,
            'office_scope' => $this->organizationalScope->assignedOffice($user),
            'role' => $user->role,
            'assets' => [],
            'employee' => null,
        ];

        // Add employee details if found
        if ($employee) {
            $profileUser['employee'] = [
                'first_name' => $employee->first_name ?? 'N/A',
                'middle_name' => $employee->middle_name ?? 'N/A',
                'last_name' => $employee->last_name ?? 'N/A',
                'suffix' => $employee->suffix ?? 'N/A',
                'sex' => $employee->sex ?? 'N/A',
                'emp_status' => $employee->emp_status ?? 'N/A',
                'position_name' => $employee->position ? $employee->position->name : 'N/A',
                'assignment_name' => $employee->assignment ? $employee->assignment->name : 'N/A',
                'div_sec_unit' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A',
            ];

            // Map assets
            try {
                $profileUser['assets'] = $employee->assets->map(function ($asset) {
                    return [
                        'id' => $asset->asset_id ?? 'N/A',
                        'name' => $asset->name ?? 'N/A',
                        'category' => $asset->category ?? 'N/A',
                        'location' => $asset->location ?? 'N/A',
                        'purchase_date' => $asset->purchase_date ? $asset->purchase_date : 'N/A',
                        'value' => $asset->value ?? 0,
                        'condition' => $asset->condition ?? 'N/A',
                        'status' => $asset->status ?? 'N/A',
                        'return_date' => $asset->return_date ? $asset->return_date : 'N/A',
                    ];
                })->toArray();
                Log::info('UsersController@show: Assets mapped', [
                    'asset_count' => count($profileUser['assets']),
                ]);
            } catch (\Exception $e) {
                Log::error('UsersController@show: Asset mapping failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $profileUser['assets'] = [];
            }
        }

        Log::info('UsersController@show: Final profile data', [
            'profile_user' => $profileUser,
        ]);

        return Inertia::render('users/View', [
            'user' => $profileUser,
        ]);
    }

    /**
     * Admin view of users (separate route name `users_admin.index`).
     */
    public function adminIndex()
    {
        $users = $this->organizationalScope
            ->applyToUsers(User::query(), auth()->user())
            ->get()
            ->map(function ($user) {
            $employee = Employee::where('email', $user->email)
                ->with(['assets', 'position', 'assignment', 'orgUnit'])
                ->first();

            $fullName = $this->displayName($employee);

            $profileUser = [
                'id' => (int) $user->id,
                'full_name' => $fullName,
                'email' => $user->email,
                'is_admin' => (int) $user->is_admin,
                'office_scope' => $this->organizationalScope->assignedOffice($user),
                'employee' => null,
                'assets' => [],
            ];

            if ($employee) {
                $profileUser['employee'] = [
                    'sex' => $employee->sex ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',
                    'position_name' => $employee->position?->name ?? 'N/A',
                    'assignment_name' => $employee->assignment?->name ?? 'N/A',
                    'div_sec_unit' => $employee->orgUnit?->name ?? 'N/A',
                ];

                $profileUser['assets'] = $employee->assets->map(function ($asset) {
                    $purchaseDate = 'N/A';
                    if ($asset->purchase_date) {
                        $purchaseDate = is_string($asset->purchase_date)
                            ? $asset->purchase_date
                            : $asset->purchase_date->format('Y-m-d');
                    }

                    $returnDate = 'N/A';
                    if ($asset->return_date) {
                        $returnDate = is_string($asset->return_date)
                            ? $asset->return_date
                            : $asset->return_date->format('Y-m-d');
                    }

                    return [
                        'id' => $asset->asset_id ?? $asset->id ?? 'N/A',
                        'name' => $asset->name ?? 'N/A',
                        'category' => $asset->category ?? 'N/A',
                        'location' => $asset->location ?? 'N/A',
                        'purchase_date' => $purchaseDate,
                        'return_date' => $returnDate,
                    ];
                })->toArray();
            }

            return $profileUser;
        })->toArray();

        return Inertia::render('users_admin/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        if ((int) $request->user()->is_admin !== 1) {
            $request->merge([
                'is_admin' => 0,
                'office_scope' => null,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'is_admin' => 'required|integer|in:0,1,2',
            'office_scope' => [
                Rule::requiredIf((int) $request->input('is_admin') === 2),
                'nullable',
                'string',
                Rule::in($this->organizationalScope->selectableOffices()),
            ],
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::warning('UsersController@store: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        $userData = [
            'email' => $request->email,
            'is_admin' => (int) $request->is_admin,
            'office_scope' => (int) $request->is_admin === 2 ? $request->office_scope : null,
            'password' => Hash::make($request->password),
        ];

        User::create($userData);

        session()->flash('message', 'User created successfully');

        return Inertia::location(route('users.index'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless($this->organizationalScope->canAccessUser($request->user(), $user), 403);

        if ((int) $request->user()->is_admin !== 1) {
            $request->merge(['is_admin' => (int) $user->is_admin]);
        }

        $employee = $user->employee;
        $employeeName = $employee && is_string($request->full_name)
            ? $this->parseEmployeeName($request->full_name)
            : null;

        Log::info('UsersController@update: Incoming request', [
            'request' => $request->all(),
            'user_id' => $user->id,
            'current_email' => $user->email,
        ]);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
                ...($employee ? [Rule::unique('employees', 'email')->ignore($employee->id)] : []),
            ],
            'is_admin' => 'required|integer|in:1,2,0',
            'office_scope' => [
                Rule::requiredIf((int) $request->input('is_admin') === 2),
                'nullable',
                'string',
                Rule::in($this->organizationalScope->selectableOffices()),
            ],
            'password' => 'nullable|min:8|confirmed',
        ]);

        $validator->after(function ($validator) use ($employee, $employeeName) {
            if ($employee && ! $employeeName) {
                $validator->errors()->add('full_name', 'Use the format: Last Name, First Name Middle Name Suffix.');
            }
        });

        if ($validator->fails()) {
            Log::warning('UsersController@update: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            $userData = [
                'email' => $request->email,
                'is_admin' => isset($request->is_admin) ? (int) $request->is_admin : $user->is_admin,
                'office_scope' => (int) $request->is_admin === 2 ? $request->office_scope : null,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            DB::transaction(function () use ($employee, $employeeName, $request, $user, $userData) {
                if ($employee && $employeeName) {
                    $employee->update([
                        ...$employeeName,
                        'email' => $request->email,
                    ]);
                }

                $user->update($userData);
            });
            Log::info('UsersController@update: User updated successfully', [
                'user_id' => $user->id,
                'updated_data' => $userData,
            ]);

            session()->flash('message', 'User updated successfully');

            return Inertia::location(route('users.index'));
        } catch (\Exception $e) {
            Log::error('UsersController@update: Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Failed to update user: '.$e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        abort_unless($this->organizationalScope->canAccessUser(auth()->user(), $user), 403);

        try {
            Log::info('UsersController: Deleting user', ['user_id' => $user->id]);
            $user->delete();
            Log::info('UsersController: User deleted', ['user_id' => $user->id]);

            return Inertia::location(route('users.index'));
        } catch (\Exception $e) {
            Log::error('UsersController: Failed to delete user', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Failed to delete user: '.$e->getMessage()]);
        }
    }

    private function displayName(?Employee $employee): string
    {
        if (! $employee) {
            return 'Unknown User';
        }

        return trim(
            ($employee->last_name ?? '').', '.
            ($employee->first_name ?? '').' '.
            ($employee->middle_name ?? '').' '.
            ($employee->suffix ?? '')
        );
    }

    /** @return array{first_name: string, middle_name: ?string, last_name: string, suffix: ?string}|null */
    private function parseEmployeeName(string $fullName): ?array
    {
        $nameParts = array_map('trim', explode(',', trim($fullName), 2));
        if (count($nameParts) !== 2 || $nameParts[0] === '' || $nameParts[1] === '') {
            return null;
        }

        $givenNameParts = preg_split('/\s+/', $nameParts[1]) ?: [];
        $firstName = array_shift($givenNameParts);
        if (! $firstName) {
            return null;
        }

        $suffix = null;
        $lastGivenNamePart = end($givenNameParts);
        if (is_string($lastGivenNamePart) && preg_match('/^(Jr\.?|Sr\.?|II|III|IV|V)$/i', $lastGivenNamePart)) {
            $suffix = array_pop($givenNameParts);
        }

        return [
            'first_name' => $firstName,
            'middle_name' => $givenNameParts ? implode(' ', $givenNameParts) : null,
            'last_name' => $nameParts[0],
            'suffix' => $suffix,
        ];
    }
}
