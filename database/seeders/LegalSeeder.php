<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalSeeder extends Seeder
{
    public function run()
    {
 

$positions = [
    ['OSEC-DENRB-ATY5-16-1998', 'Attorney V', 25],
    ['OSEC-DENRB-ATY4-20-2014', 'Attorney IV', 23],
    ['OSEC-DENRB-ATY3-56-1998', 'Attorney III', 21],
    ['OSEC-DENRB-ATY3-57-1998', 'Attorney III', 21],
    ['OSEC-DENRB-ATY3-58-1998', 'Attorney III', 21],
    ['OSEC-DENRB-ATY2-24-2014', 'Attorney II', 18],
    ['OSEC-DENRB-ATY2-49-1998', 'Attorney II', 18],
    ['OSEC-DENRB-ATY2-51-1998', 'Attorney II', 18],
    ['OSEC-DENRB-LEA2-44-1998', 'Legal Assistant II', 12],
    ['OSEC-DENRB-LEA2-45-1998', 'Legal Assistant II', 12],
    ['OSEC-DENRB-ADAS1-184-2014', 'Administrative Assistant I (Computer Operator I)', 7],
    ['OSEC-DENRB-ADA6-88-2004', 'Administrative Aide VI', 6],
];

foreach ($positions as [$item_code, $name, $grade]) {
    DB::table('positions')->updateOrInsert(
        ['item_code' => $item_code],
        [
            'name' => $name,
            'salary_grade' => $grade,
            'desc' => 'LEGAL DIVISION',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}







    }
}
