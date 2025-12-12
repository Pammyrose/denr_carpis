<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PMDSeeder extends Seeder
{
    public function run()
    {
    

        $positions = [
            ['OSEC-DENRB-PLO5-8-1998', 'Planning Officer V', 24],
            ['OSEC-DENRB-PLO4-24-2014', 'Planning Officer IV', 22],
            ['OSEC-DENRB-INFOSA3-31-2014', 'Information Systems Analyst III', 19],
            ['OSEC-DENRB-PEO3-29-2014', 'Project Evaluation Officer III', 18],
            ['OSEC-DENRB-PLO3-16-1998', 'Planning Officer III', 18],
            ['OSEC-DENRB-INFOSA2-101-2014', 'Information Systems Analyst II', 16],
            ['OSEC-DENRB-ADOF4-109-2014', 'Administrative Officer IV', 15],
            ['OSEC-DENRB-PEO2-25-1998', 'Project Evaluation Officer II', 15],
            ['OSEC-DENRB-PLO2-23-1998', 'Planning Officer II', 15],
            ['OSEC-DENRB-STAT2-38-2014', 'Statistician II', 15],
            ['OSEC-DENRB-STAT2-39-2014', 'Statistician II', 15],
            ['OSEC-DENRB-PEO1-28-2014', 'Project Evaluation Officer I', 11],
            ['OSEC-DENRB-PLO1-79-2014', 'Planning Officer I', 11],
            ['OSEC-DENRB-STAT1-29-1998', 'Statistician I', 11],
            ['OSEC-DENRB-STAT1-30-1998', 'Statistician I', 11],
            ['OSEC-DENRB-ADAS3-102-2014', 'Administrative Assistant III (Computer Operator II)', 9],
            ['OSEC-DENRB-ADAS1-182-2014', 'Administrative Assistant I (Computer Operator I)', 7],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'PLANNING AND MANAGEMENT DIVISION',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}