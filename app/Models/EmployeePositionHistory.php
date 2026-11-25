<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePositionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_email',
        'employee_name',
        'position_id',
        'item_code',
        'position_name',
        'assignment_name',
        'div_sec_unit',
        'event',
        'status_from',
        'status_to',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
