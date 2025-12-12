<?php

// app/Models/Employee.php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees'; // Adjust if your table name is different
   
}

class User extends Model
{
    protected $table = 'users'; // Adjust if your table name is different
   
}

class Inventory extends Model
{
    protected $table = 'assets'; // Adjust if your table name is different
   
}