<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ORDSeeder extends Seeder
{
    public function run()
    {
   
 

        $positions = [
            ['OSEC-DENRB-SRCDO-20-2014', 'Senior Communications Development Officer', 18],
            ['OSEC-DENRB-COMDO2-18-2014', 'Communications Development Officer II', 15],
            ['OSEC-DENRB-INFO2-22-2014', 'Information Officer II', 15],
            ['OSEC-DENRB-COMDO1-21-2014', 'Communications Development Officer I', 11],
            ['OSEC-DENRB-INFO1-25-2014', 'Information Officer I', 11],
            ['OSEC-DENRB-ADAS3-24-2004', 'Administrative Assistant III', 9],
            ['OSEC-DENRB-ADAS2-42-2004', 'Administrative Assistant II', 8],
            ['OSEC-DENRB-ADA4-116-2004', 'Administrative Aide IV (Driver II)', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'OFFICE OF THE REGIONAL DIRECTOR',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
