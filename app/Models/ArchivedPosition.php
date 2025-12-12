<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivedPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_position_id',
        'item_code',
        'name',
        'desc',
        'salary_grade',
        'org_code',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];
}