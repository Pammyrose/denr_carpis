<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'asset_id',
        'name',
        'category',
        'location',
        'purchase_date',
        'value',
        'condition',
        'assigned',
        'assigned_to_name',
        'status',
        'property_no',
        'serial_no',
        'serviceable',
        'unserviceable',
        'coa_representative',
        'coa_date',
        'assigned_date',
        'image',
        'return_date',
        'unit_qty',
    ];

    protected $primaryKey = 'id';

    /**
     * Get the route key for the model (use asset_id for binding).
     */
    public function getRouteKeyName(): string
    {
        return 'asset_id';
    }

    public function getAssignedToNameAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if (!$this->assigned) {
            return 'Unassigned';
        }

        $employee = Employee::find($this->assigned);
        if (!$employee) {
            return 'Unassigned (Archived)';
        }

        return trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
    }
    /**
     * Get the employee assigned to this asset.
     */
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned');
    }
}