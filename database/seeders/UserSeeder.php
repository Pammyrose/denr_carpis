<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        /** ==========================
         *  CREATE ADMIN USER
         *  ========================== */
        $admin = User::create([
            'email' => 'admin@denr.gov.ph',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create Admin's Employee Record
        $adminEmployee = $admin->employee()->create([
            'email' => 'admin@example.ph',
            'first_name' => 'John',
            'middle_name' => 'A',
            'last_name' => 'Doe',
            'suffix' => null,
            'sex' => 'Male',
            'emp_status' => 'Regular',
            'position_name' => 'Admin',
            'assignment_name' => 'Regional Office',
            'div_sec_unit' => 'PLANNING AND MANAGEMENT DIVISION',
            'contact_no' => '123-456-7890',
        ]);

        // Create Admin's Other Info
        $adminEmployee->otherInfo()->create([
            'date_of_birth'       => '1990-01-01',
            'tin_no'              => '123-456-789',
            'date_appointment'    => '2020-06-01',
            'date_last_promotion' => '2023-06-01',
            'civil_service'       => 'Professional',
            'education'           => 'BS Computer Science',
        ]);

        /** ==========================
         *  CREATE REGULAR USER
         *  ========================== */
        $user = User::create([
            'email' => 'user@gmail.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        // Create Regular User's Employee Record
        $userEmployee = $user->employee()->create([
            'email' => 'user@example.com',
            'first_name' => 'Jane',
            'middle_name' => 'B',
            'last_name' => 'Smith',
            'suffix' => null,
            'sex' => 'Female',
            'emp_status' => 'Regular',
            'position_name' => 'User',
            'assignment_name' => 'Regional Office',
            'div_sec_unit' => 'PLANNING AND MANAGEMENT DIVISION',
            'contact_no' => '098-765-4321',
        ]);

        // Create User's Other Info
        $userEmployee->otherInfo()->create([
            'date_of_birth'       => '1995-02-15',
            'tin_no'              => '987-654-321',
            'date_appointment'    => '2021-01-15',
            'date_last_promotion' => '2024-01-15',
            'civil_service'       => 'Sub-Professional',
            'education'           => 'BA Human Resources',
        ]);
    }
}
