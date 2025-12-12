<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpStatus extends Model
{
    protected $table = 'emp_status';

    protected $fillable = ['name', 'description'];
}
