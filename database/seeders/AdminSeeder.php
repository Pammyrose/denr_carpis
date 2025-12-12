<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run()
    {
   

$positions = [
    ['OSEC-DENRB-CADOF-7-2004', 'Chief Administrative Officer', 24],
    ['OSEC-DENRB-SADOF-51-2014', 'Supervising Administrative Officer', 22],
    ['OSEC-DENRB-ADOF5-23-2004', 'Administrative Officer V (HRMO III)', 18],
    ['OSEC-DENRB-ADOF5-25-2004', 'Administrative Officer V', 18],
    ['OSEC-DENRB-ADOF5-73-2014', 'Administrative Officer V (Cashier III)', 18],
    ['OSEC-DENRB-ADOF5-74-2014', 'Administrative Officer V', 18],
    ['OSEC-DENRB-ADOF5-75-2014', 'Administrative Officer V', 18],
    ['OSEC-DENRB-ADOF4-101-2014', 'Administrative Officer IV', 15],
    ['OSEC-DENRB-ADOF4-6-2004', 'Administrative Officer IV (HRMO II)', 15],
    ['OSEC-DENRB-ADOF4-7-2004', 'Administrative Officer IV', 15],
    ['OSEC-DENRB-ADOF3-19-2004', 'Administrative Officer III (Cashier II)', 14],
    ['OSEC-DENRB-ADOF3-20-2004', 'Administrative Officer III (Records Officer II)', 14],
    ['OSEC-DENRB-ADOF3-21-2004', 'Administrative Officer III', 14],
    ['OSEC-DENRB-ADOF2-24-2004', 'Administrative Officer II (HRMO I)', 11],
    ['OSEC-DENRB-ADOF2-63-2014', 'Administrative Officer II (HRMO I)', 11],
    ['OSEC-DENRB-ADOF2-64-2014', 'Administrative Officer II (HRMO I)', 11],
    ['OSEC-DENRB-ADOF2-65-2014', 'Administrative Officer II (HRMO I)', 11],
    ['OSEC-DENRB-ADOF1-15-2004', 'Administrative Officer I (Cashier I)', 10],
    ['OSEC-DENRB-ADOF1-16-2004', 'Administrative Officer I (Records Officer I)', 10],
    ['OSEC-DENRB-ADOF1-17-2004', 'Administrative Officer I (Records Officer I)', 10],
    ['OSEC-DENRB-ADAS3-103-2014', 'Administrative Assistant III', 9],
    ['OSEC-DENRB-ADAS3-104-2014', 'Administrative Assistant III', 9],
    ['OSEC-DENRB-CROF1-18-1998', 'Credit Officer I', 9],
    ['OSEC-DENRB-ADAS2-40-2004', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS2-41-2004', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS2-43-2004', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS2-65-2014', 'Administrative Assistant II', 8],
    ['OSEC-DENRB-ADAS1-185-2014', 'Administrative Assistant I (Computer Operator I)', 7],
    ['OSEC-DENRB-ADAS1-186-2014', 'Administrative Assistant I (Computer Operator I)', 7],
    ['OSEC-DENRB-ADAS1-187-2014', 'Administrative Assistant I (Computer Operator I)', 7],
    ['OSEC-DENRB-ADAS1-32-2004', 'Administrative Assistant I (Computer Operator I)', 7],
];

foreach ($positions as [$item_code, $name, $grade]) {
    DB::table('positions')->updateOrInsert(
        ['item_code' => $item_code],
        [
            'name' => $name,
            'salary_grade' => $grade,
            'desc' => 'ADMINISTRATIVE DIVISION',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}






    }
}
