<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentPlace extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name', 'region', 'address'];
}