<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceAbraSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Abra PENRO Positions
            ['OSEC-DENRB-PENRO-66-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-188-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-41-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-99-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-99-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-99-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-102-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-103-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-ADOF4-16-2004', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-PLO2-77-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-83-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-20-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-26-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-32-2004', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-105-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-44-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-757-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-758-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-138-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-218-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-298-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-299-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-311-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-297-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-298-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-299-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ADA6-768-2014', 'Administrative Aide VI', 6],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PENRO Abra',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
