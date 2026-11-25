<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceApayaoSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Apayao PENRO Positions
            ['OSEC-DENRB-PENRO-15-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-189-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-42-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-100-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-100-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-100-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-103-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-10-2004', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-ADOF4-102-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-PLO2-78-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-80-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-25-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-41-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-81-2014', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-27-2004', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-49-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-759-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-760-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-139-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-219-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-310-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-315-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-316-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-2-2015', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-3-2015', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-4-2015', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ADA6-769-2014', 'Administrative Aide VI', 6],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PENRO Apayao',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
