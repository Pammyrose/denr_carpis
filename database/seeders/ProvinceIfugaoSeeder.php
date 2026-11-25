<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceIfugaoSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Ifugao PENRO Positions
            ['OSEC-DENRB-PENRO-69-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-191-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-44-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-102-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-102-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-102-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-105-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-107-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-ADOF4-23-2004', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-PLO2-80-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-85-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-23-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-29-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-80-2014', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-107-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-47-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-763-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-764-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-141-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-221-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-301-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-302-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-312-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-302-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-303-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-310-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ADA6-771-2014', 'Administrative Aide VI', 6],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PENRO Ifugao',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
