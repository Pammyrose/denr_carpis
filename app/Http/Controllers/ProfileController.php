<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        // Log request start
        Log::info('ProfileController@show: Starting request processing', [
            'session_authenticated' => $request->user() ? true : false,
        ]);

        // Check authenticated user
        $user = $request->user();
        if (!$user) {
            Log::error('ProfileController@show: No authenticated user found');
            return Inertia::render('Profile', [
                'user' => null,
                'error' => 'You must be logged in to view this page.',
            ]);
        }

        // Log user data
        Log::info('ProfileController@show: User data', [
            'user_id' => $user->id ?? 'null',
            'user_name' => $user->name ?? 'null',
            'user_email' => $user->email ?? 'null',
            'user_is_admin' => $user->is_admin ?? 'null',
        ]);

        // Fetch employee with relationships
        $employee = null;
        try {
            $employee = Employee::where('email', $user->email)
                ->with(['assets', 'position', 'assignment', 'orgUnit'])
                ->first();
            Log::info('ProfileController@show: Employee query result', [
                'email' => $user->email,
                'employee_found' => $employee ? true : false,
                'employee_id' => $employee ? $employee->id : 'null',
            ]);
        } catch (\Exception $e) {
            Log::error('ProfileController@show: Employee query failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Build profileUser array
        $profileUser = [
            'id' => $user->id ?? 0,
            'full_name' => $user->name ?? 'Unknown User',
            'email' => $user->email ?? 'N/A',
            'role' => isset($user->is_admin) ? ($user->is_admin ? 'Admin' : 'User') : 'User',
            'assets' => [],
            'employee' => null, // New: Employee details
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
                    ];
                })->toArray();
                Log::info('ProfileController@show: Assets mapped', [
                    'asset_count' => count($profileUser['assets']),
                ]);
            } catch (\Exception $e) {
                Log::error('ProfileController@show: Asset mapping failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $profileUser['assets'] = [];
            }
        }

        // Log final data
        Log::info('ProfileController@show: Final profile data', [
            'profile_user' => $profileUser,
        ]);

        return Inertia::render('Profile', [
            'user' => $profileUser,
        ]);
    }

    
}