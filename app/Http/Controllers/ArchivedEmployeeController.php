<?php

namespace App\Http\Controllers;

use App\Models\ArchivedEmployee;
use App\Models\Position;
use App\Models\ArchivedPosition;
use App\Models\AssignmentPlace;
use App\Models\OrgUnit;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ArchivedEmployeeController extends Controller
{
    public function index()
    {
        try {
            $archivedEmployees = ArchivedEmployee::all()->map(function ($employee) {
                $positionName = 'N/A';
                if ($employee->position_id) {
                    // Check if position_id references archived_positions or positions
                    $archivedPosition = ArchivedPosition::find($employee->position_id);
                    if ($archivedPosition) {
                        $positionName = $archivedPosition->name;
                    } else {
                        $position = Position::find($employee->position_id);
                        $positionName = $position ? $position->name : 'N/A';
                    }
                }

                $assignment = AssignmentPlace::find($employee->assignment_id);
                $orgUnit = OrgUnit::find($employee->org_unit_id);

                

                return [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name ?? 'N/A',
                    'middle_name' => $employee->middle_name ?? '',
                    'last_name' => $employee->last_name ?? 'N/A',
                    'suffix' => $employee->suffix ?? '',
                    'sex' => $employee->sex ?? 'N/A',
                    'email' => $employee->email ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',
                    'position_name' => $positionName,
                    'assignment_name' => $assignment ? $assignment->name : 'N/A',
                    'div_sec_unit' => $orgUnit ? $orgUnit->name : 'N/A',
                    'archived_at' => $employee->archived_at ? $employee->archived_at->toDateTimeString() : 'N/A',
                ];
            })->toArray();

            $currentEmployee = auth()->user()?->employee;

            Log::info('ArchivedEmployeeController: Fetched archived employees', [
                'archived_employee_count' => count($archivedEmployees),
            ]);

            return Inertia::render('Archived', [
                'archivedEmployees' => $archivedEmployees,
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('ArchivedEmployeeController: Failed to fetch archived employees', [
                'error' => $e->getMessage(),
            ]);
            return Inertia::render('Archived', [
                'archivedEmployees' => [],
                'error' => 'Failed to load archived employees. Please try again later.',
            ]);
        }
    }
}