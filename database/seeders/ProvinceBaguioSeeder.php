<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceBaguioSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Baguio CENRO Positions
            ['OSEC-DENRB-CENRO-70-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-158-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-184-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-141-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-48-1998', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-180-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-130-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-711-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-713-1998', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-85-1998', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-15-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-81-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-18-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-20-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-102-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-110-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-45-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-166-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-873-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-874-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-875-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-876-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-877-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-779-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-790-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-802-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-477-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1329-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1330-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1331-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1332-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1333-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1334-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1335-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1336-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1337-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1338-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1339-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1340-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1341-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1342-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1343-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1344-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-273-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-85-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-371-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-372-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2314-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2317-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2318-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2319-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2585-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-372-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-373-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-374-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-375-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-378-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Baguio',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
