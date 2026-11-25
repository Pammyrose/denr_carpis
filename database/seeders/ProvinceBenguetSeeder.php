<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceBenguetSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Benguet PENRO Positions
            ['OSEC-DENRB-PENRO-67-1998', 'Provincial Environment and Natural Resources Officer', 26],
            ['OSEC-DENRB-ADAS1-190-2014', 'Administrative Assistant I (Computer Operator I)', 7],
            ['OSEC-DENRB-ADA4-43-2014', 'Administrative Aide IV (Driver II)', 4],
            ['OSEC-DENRB-CADOF-101-2014', 'Chief Administrative Officer', 24],
            ['OSEC-DENRB-A3-101-2014', 'Accountant III', 19],
            ['OSEC-DENRB-PLO3-101-2014', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-104-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-106-2014', 'Administrative Officer IV (Budget Officer II)', 15],
            ['OSEC-DENRB-ADOF4-18-2004', 'Administrative Officer IV (HRMO II)', 15],
            ['OSEC-DENRB-PLO2-79-2014', 'Planning Officer II', 15],
            ['OSEC-DENRB-PLO1-84-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-ADOF1-27-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-33-2004', 'Administrative Officer I (Cashier I)', 10],
            ['OSEC-DENRB-ADOF1-46-2004', 'Administrative Officer I (Supply Officer I)', 10],
            ['OSEC-DENRB-ADAS3-106-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS2-45-2004', 'Administrative Assistant II (Bookkeeper)', 8],
            ['OSEC-DENRB-ADA6-761-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-762-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-DMO5-140-2014', 'Development Management Officer V', 24],
            ['OSEC-DENRB-SVEMS-220-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-SVEMS-224-2014', 'Supervising Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-DMO3-300-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-309-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO3-314-2014', 'Development Management Officer III', 18],
            ['OSEC-DENRB-DMO2-300-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-301-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-DMO2-309-2014', 'Development Management Officer II', 15],
            ['OSEC-DENRB-ECOMS2-172-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-ECOMS2-174-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-ECOMS1-264-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-265-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-275-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-22-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-24-1998', 'Forester I', 11],
            ['OSEC-DENRB-ADA6-770-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-FORA-343-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-344-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-345-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-346-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-347-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-348-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-349-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PENRO Benguet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
