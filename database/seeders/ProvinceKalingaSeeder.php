<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceKalingaSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Kalinga PENRO Positions
            ['OSEC-DENRB-PENRO-71-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-192-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-45-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-103-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-103-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-103-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-106-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-105-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-ADOF4-108-2014', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-PLO2-81-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-82-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-24-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-30-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-40-2004', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-108-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-48-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-765-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-766-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-142-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-222-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-303-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-304-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-313-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-304-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-305-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-311-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ADA6-772-2014', 'Administrative Aide VI', 6],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PENRO Kalinga',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
