<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceMtProvinceSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // MtProvince PENRO Positions
            ['OSEC-DENRB-PENRO-68-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-193-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-46-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-104-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-104-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-104-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-107-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-104-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-ADOF4-13-2004', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-PLO2-82-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-81-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-22-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-28-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-37-2004', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-109-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-46-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-767-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-808-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-143-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-223-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-305-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-306-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-307-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-306-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-307-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-308-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ADA6-773-2014', 'Administrative Aide VI', 6],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO MT.Province',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
