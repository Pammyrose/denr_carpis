<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'desc',
        'salary_grade',
        'org_code',
        'status',
    ];

    
}