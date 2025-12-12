<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'email',
        'emp_status',
        'position_id',
        'assignment_id',
        'org_unit_id',
        'user_id',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }



    public function assignment()
    {
        return $this->belongsTo(AssignmentPlace::class, 'assignment_id');
    }

    public function orgUnit()
    {
        return $this->belongsTo(OrgUnit::class, 'div_sec_unit');
    }
}