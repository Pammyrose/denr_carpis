<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LPDDSeeder extends Seeder
{
    public function run()
    {


$positions = [
    ['OSEC-DENRB-DMO5-137-2014', 'Development Management Officer V', 24],
    ['OSEC-DENRB-DMO4-166-2014', 'Development Management Officer IV', 22],
    ['OSEC-DENRB-ENG3-46-2014', 'Engineer III', 19],
    ['OSEC-DENRB-FORST3-179-1998', 'Forester III', 18],
    ['OSEC-DENRB-LAMO3-139-1998', 'Land Management Officer III', 18],
    ['OSEC-DENRB-SREMS-43-1998', 'Senior Ecosystems Management Specialist', 18],
    ['OSEC-DENRB-ENG2-114-2014', 'Engineer II', 16],
    ['OSEC-DENRB-ENG2-115-2014', 'Engineer II', 16],
    ['OSEC-DENRB-ECOMS2-128-1998', 'Ecosystems Management Specialist II', 15],
    ['OSEC-DENRB-FMS2-42-2014', 'Forest Management Specialist II', 15],
    ['OSEC-DENRB-LAMO2-84-1998', 'Land Management Officer II', 15],
    ['OSEC-DENRB-COMPRO1-105-2014', 'Computer Programmer I', 11],
    ['OSEC-DENRB-ECOMS1-78-1998', 'Ecosystems Management Specialist I', 11],
    ['OSEC-DENRB-FORST1-1028-1998', 'Forester I', 11],
    ['OSEC-DENRB-FORST1-13-1998', 'Forester I', 11],
    ['OSEC-DENRB-PRAP1-5-1998', 'Property Appraiser I', 11],
    ['OSEC-DENRB-SPI1-107-1998', 'Special Investigator I', 11],
    ['OSEC-DENRB-CGR1-462-1998', 'Cartographer I', 6],
    ['OSEC-DENRB-CGR1-465-1998', 'Cartographer I', 6],
    ['OSEC-DENRB-ENGA-172-2014', 'Engineering Aide', 4],
    ['OSEC-DENRB-ENGA-173-2014', 'Engineering Aide', 4],
];

foreach ($positions as [$item_code, $name, $grade]) {
    DB::table('positions')->updateOrInsert(
        ['item_code' => $item_code],
        [
            'name' => $name,
            'salary_grade' => $grade,
            'desc' => 'LICENSES, PATENTS AND DEEDS DIVISION',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}







    }
}
