<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceLamutSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Lamut CENRO Positions
            ['OSEC-DENRB-CENRO-75-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-161-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-189-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-153-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-197-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-182-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-134-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-730-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-733-1998', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-195-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-20-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-276-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1004-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-997-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-107-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-117-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-50-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-171-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-889-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-890-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-891-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-892-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-893-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-782-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-793-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-805-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-481-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1369-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1370-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1371-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1372-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1373-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1374-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1375-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1376-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-276-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-91-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-185-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-376-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2393-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2397-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2400-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2403-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2412-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2413-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2414-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2415-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2590-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-362-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-363-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-364-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Lamut',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
