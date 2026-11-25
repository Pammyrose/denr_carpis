<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvincePinukpukSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Pinukpuk CENRO Positions
            ['OSEC-DENRB-CENRO-79-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-162-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-190-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-156-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-198-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-118-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-133-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-749-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-82-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-196-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-21-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-79-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1027-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-1029-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-100-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-121-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-54-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-173-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-894-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-895-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-896-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-897-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-898-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-783-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-787-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-809-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-483-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1377-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1378-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1379-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1380-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1381-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1382-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1383-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1384-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1385-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1386-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1387-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-277-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-95-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-182-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-183-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2538-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2565-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2569-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2573-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2577-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2586-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2587-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-365-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Pinukpuk',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
