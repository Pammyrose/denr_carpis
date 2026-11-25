<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceBanguedSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Bangued CENRO Positions
            ['OSEC-DENRB-CENRO-67-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-154-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-181-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-145-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-50-1998', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-177-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-171-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-707-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-78-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-189-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-13-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-80-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-5-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-9-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-95-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-108-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-43-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-164-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-851-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-852-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-853-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-854-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-855-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-774-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-786-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-95-2014', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1275-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1276-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1277-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1278-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1279-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1280-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1281-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1282-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1283-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-270-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-83-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-369-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-370-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2259-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2264-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2267-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2276-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2283-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2581-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-350-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Bangued',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
