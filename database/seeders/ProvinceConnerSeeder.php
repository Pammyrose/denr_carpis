<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceConnerSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Conner CENRO Positions
            ['OSEC-DENRB-CENRO-84-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-156-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-115-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-165-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-193-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-166-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-114-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-776-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-80-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-191-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-23-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-280-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1026-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-1036-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-124-2014', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-95-2014', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-82-2014', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-168-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-861-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-862-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-863-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-864-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-865-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-777-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-788-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-800-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-97-2014', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1294-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1295-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1296-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1297-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1298-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1299-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1300-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1301-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1302-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1303-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1304-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1305-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1306-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1307-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-185-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-272-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-175-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-176-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2507-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2532-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2554-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2555-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2559-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3340-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3346-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-354-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-355-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-356-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Conner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
