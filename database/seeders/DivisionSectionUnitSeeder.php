<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSectionUnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            ['name' => 'OFFICE OF THE REGIONAL DIRECTOR', 'org_code' => 'ORD'],
            ['name' => 'FINANCE DIVISION', 'org_code' => 'FD'],
            ['name' => 'ADMINISTRATIVE DIVISION', 'org_code' => 'AD'],
            ['name' => 'LEGAL DIVISION', 'org_code' => 'LD'],
            ['name' => 'CONSERVATION AND DEVELOPMENT DIVISION', 'org_code' => 'CDD'],
            ['name' => 'SURVEYS AND MAPPING DIVISION', 'org_code' => 'SMD'],
            ['name' => 'LICENSES, PATENTS AND DEEDS DIVISION', 'org_code' => 'LPDD'],
            ['name' => 'ENFORCEMENT DIVISION', 'org_code' => 'ED'],
            ['name' => 'PLANNING AND MANAGEMENT DIVISION', 'org_code' => 'PMD'],
        ];

        foreach ($units as $unit) {
            DB::table('div_sec_units')->insert([
                'name'       => $unit['name'],
                'org_code'   => $unit['org_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
