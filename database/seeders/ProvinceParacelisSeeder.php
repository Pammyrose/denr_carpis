<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceParacelisSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Paracelis CENRO Positions
            ['OSEC-DENRB-CENRO-73-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-164-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-187-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-151-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-200-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-119-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-173-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-722-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-84-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-198-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-17-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-267-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-268-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-39-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-41-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-105-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-115-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-48-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-174-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-906-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-907-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-908-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-909-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-910-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-795-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-797-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-807-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-99-2014', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1404-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1405-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1406-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1407-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1408-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1409-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1410-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1411-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1412-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1413-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1414-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-279-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-88-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-188-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-189-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2361-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2362-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2364-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2365-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2366-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-369-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-370-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-371-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Paracelis',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
