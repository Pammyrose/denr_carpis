<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceSeeder extends Seeder
{
    public function run()
    {
 

        $positions = [
    ['OSEC-DENRB-CADOF-6-2004', 'Chief Administrative Officer', 24],
    ['OSEC-DENRB-SADOF-50-2014', 'Supervising Administrative Officer', 22],
    ['OSEC-DENRB-A3-16-1998', 'Accountant III', 19],
    ['OSEC-DENRB-ADOF5-22-2004', 'Administrative Officer V (Budget Officer III)', 18],
    ['OSEC-DENRB-A2-45-2014', 'Accountant II', 16],
    ['OSEC-DENRB-ADOF4-100-2014', 'Administrative Officer IV (Budget Officer II)', 15],
    ['OSEC-DENRB-ADOF4-5-2004', 'Administrative Officer IV (Budget Officer II)', 15],
    ['OSEC-DENRB-A1-47-1998', 'Accountant I', 12],
    ['OSEC-DENRB-AA-39-2014', 'Accounting Analyst', 11],
    ['OSEC-DENRB-ADOF2-15-2004', 'Administrative Officer II (Budget Officer I)', 11],
    ['OSEC-DENRB-ADOF2-18-2004', 'Administrative Officer II (Budget Officer I)', 11],
    ['OSEC-DENRB-ADAS3-25-2004', 'Administrative Assistant III', 9],
    ['OSEC-DENRB-ADAS2-63-2014', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS2-64-2014', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS1-183-2014', 'Administrative Assistant I (Computer Operator I)', 7],
];

foreach ($positions as [$item_code, $name, $grade]) {
    DB::table('positions')->updateOrInsert(
        ['item_code' => $item_code],
        [
            'name' => $name,
            'salary_grade' => $grade,
            'desc' => 'FINANCE DIVISION',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}

    }
}
