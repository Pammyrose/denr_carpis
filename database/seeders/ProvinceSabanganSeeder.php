<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSabanganSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Sabangan CENRO Positions
            ['OSEC-DENRB-CENRO-74-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-SVEMS-225-2014', 'Supervisng Ecosystems Management Specialist', 22],
            ['OSEC-DENRB-FORST3-188-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-152-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-49-1998', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-184-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-129-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-726-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-728-1998', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-199-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-18-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-269-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-270-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-271-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-272-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-273-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ECOMS1-82-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-44-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-45-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-106-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-116-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-49-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-175-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-911-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-912-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-913-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-914-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-915-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-785-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-796-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-798-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-479-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1415-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1416-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1417-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1418-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1419-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1420-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1421-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1422-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1423-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1424-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1425-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-280-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-89-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-374-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-375-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2371-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2376-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2383-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2384-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2386-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2582-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-376-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-377-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Sabangan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
