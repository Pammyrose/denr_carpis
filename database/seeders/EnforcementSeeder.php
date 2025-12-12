<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnforcementSeeder extends Seeder
{
    public function run()
    {
 

        $positions = [
            ['OSEC-DENRB-DMO5-144-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-DMO4-165-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-SRFMS-148-1998', 'Senior Forest Management Specialist', 18],
            ['OSEC-DENRB-SRFMS-149-1998', 'Senior Forest Management Specialist', 18],
            ['OSEC-DENRB-SRFMS-151-1998', 'Senior Forest Management Specialist', 18],
            ['OSEC-DENRB-ECOMS2-120-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-ECOMS2-167-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-ECOMS2-168-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-COMPRO1-106-2014', 'Computer Programmer I', 11],
            ['OSEC-DENRB-ECOMS1-64-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-77-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-SPI1-105-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-SPI1-106-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-SPI1-94-2014', 'Special Investigator I', 11],
            ['OSEC-DENRB-FORA-2337-1998', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'ENFORCEMENT DIVISION',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
