<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CDDSeeder extends Seeder
{
    public function run()
    {


$positions = [
    ['OSEC-DENRB-DMO5-145-2014', 'Development Management Officer V', 24],
    ['OSEC-DENRB-DMO4-153-2014', 'Development Management Officer IV', 22],
    ['OSEC-DENRB-DMO3-308-2014', 'Development Management Officer III', 18],
    ['OSEC-DENRB-FORST3-233-1998', 'Forester III', 18],
    ['OSEC-DENRB-SREMS-42-1998', 'Senior Ecosystems Management Specialist', 18],
    ['OSEC-DENRB-ECOMS2-123-1998', 'Ecosystems Management Specialist II', 15],
    ['OSEC-DENRB-ECOMS2-124-1998', 'Ecosystems Management Specialist II', 15],
    ['OSEC-DENRB-ECOMS2-127-1998', 'Ecosystems Management Specialist II', 15],
    ['OSEC-DENRB-FMS2-82-1998', 'Forest Management Specialist II', 15],
    ['OSEC-DENRB-FMS2-83-1998', 'Forest Management Specialist II', 15],
    ['OSEC-DENRB-FMS2-84-1998', 'Forest Management Specialist II', 15],
    ['OSEC-DENRB-FMS2-85-1998', 'Forest Management Specialist II', 15],
    ['OSEC-DENRB-FORST2-1-1998', 'Forester II', 15],
    ['OSEC-DENRB-FORST2-698-1998', 'Forester II', 15],
    ['OSEC-DENRB-FORST2-702-1998', 'Forester II', 15],
    ['OSEC-DENRB-FORST2-703-1998', 'Forester II', 15],
    ['OSEC-DENRB-COMPRO1-103-2014', 'Computer Programmer I', 11],
    ['OSEC-DENRB-ECOMS1-66-1998', 'Ecosystems Management Specialist I', 11],
    ['OSEC-DENRB-ECOMS1-67-1998', 'Ecosystems Management Specialist I', 11],
    ['OSEC-DENRB-ECOMS1-71-1998', 'Ecosystems Management Specialist I', 11],
    ['OSEC-DENRB-ECOMS1-72-1998', 'Ecosystems Management Specialist I', 11],
    ['OSEC-DENRB-FORST1-28-1998', 'Forester I', 11],
    ['OSEC-DENRB-FORST1-30-1998', 'Forester I', 11],
    ['OSEC-DENRB-FORST1-31-1998', 'Forester I', 11],
    ['OSEC-DENRB-FORST1-995-1998', 'Forester I', 11],
    ['OSEC-DENRB-DFM2-8-1998', 'Draftsman II', 8],
    ['OSEC-DENRB-FASU-97-2014', 'Farm Supervisor', 8],
    ['OSEC-DENRB-CGR1-25-1998', 'Cartographer I', 6],
    ['OSEC-DENRB-FORA-2330-1998', 'Forest Ranger', 4],
    ['OSEC-DENRB-FORA-2331-1998', 'Forest Ranger', 4],
    ['OSEC-DENRB-FORA-2341-1998', 'Forest Ranger', 4],
    ['OSEC-DENRB-FORA-2353-1998', 'Forest Ranger', 4],
    ['OSEC-DENRB-FORA-2356-1998', 'Forest Ranger', 4],
    ['OSEC-DENRB-FORA-2359-1998', 'Forest Ranger', 4],
];

foreach ($positions as [$item_code, $name, $grade]) {
    DB::table('positions')->updateOrInsert(
        ['item_code' => $item_code],
        [
            'name' => $name,
            'salary_grade' => $grade,
            'desc' => 'CONSERVATION AND DEVELOPMENT DIVISION',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
}






    }
}
