<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Services\OrganizationalScope;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(private readonly OrganizationalScope $organizationalScope) {}

    public function index(): Response
    {
        $occupantQuery = Employee::query();
        $this->organizationalScope->applyToEmployees($occupantQuery, auth()->user());
        $occupantsByPosition = $occupantQuery
            ->whereNotNull('position_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'email',
                'contact_no',
                'emp_status',
                'position_name',
                'assignment_name',
                'div_sec_unit',
            ])
            ->groupBy('position_name');

        $occupantOffsets = [];
        $positionQuery = Position::query();
        $this->organizationalScope->applyToPositions($positionQuery, auth()->user());
        $positions = $positionQuery
            ->orderBy('name')
            ->orderBy('item_code')
            ->get([
                'id',
                'item_code',
                'org_code',
                'name',
                'desc',
                'salary_grade',
                'status',
            ])
            ->map(function (Position $position) use ($occupantsByPosition, &$occupantOffsets) {
                $occupant = null;

                if (strcasecmp($position->status, 'Occupied') === 0) {
                    $offset = $occupantOffsets[$position->name] ?? 0;
                    $occupant = $occupantsByPosition->get($position->name)?->get($offset);
                    $occupantOffsets[$position->name] = $offset + 1;
                }

                return [
                    ...$position->toArray(),
                    'occupied_by' => $occupant ? [
                        'id' => $occupant->id,
                        'full_name' => $this->formatEmployeeName($occupant),
                        'email' => $occupant->email,
                        'contact_no' => $occupant->contact_no,
                        'employee_status' => $occupant->emp_status,
                        'assignment' => $occupant->assignment_name,
                        'division' => $occupant->div_sec_unit,
                    ] : null,
                ];
            });

        return Inertia::render('positions/Index', [
            'positions' => $positions,
        ]);
    }

    private function formatEmployeeName(Employee $employee): string
    {
        $givenNames = trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name,
        ])));
        $lastName = trim(implode(' ', array_filter([
            $employee->last_name,
            $employee->suffix,
        ])));

        return $lastName !== '' ? "{$lastName}, {$givenNames}" : $givenNames;
    }
}
