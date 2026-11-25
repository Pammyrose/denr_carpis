<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceLagangilangSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Lagangilang CENRO Positions
            ['OSEC-DENRB-CENRO-68-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-155-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-183-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-146-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-192-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-116-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-EMS2-169-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-708-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-79-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-190-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-14-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-274-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-10-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-11-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-101-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-109-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-44-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-165-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-856-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-857-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-858-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-859-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-860-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-775-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-776-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-799-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-96-2014', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1284-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1285-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1286-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1287-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1288-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1289-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1290-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1291-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1292-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1293-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-271-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-84-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-174-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-186-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2291-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2294-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2295-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2296-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2298-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2300-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2302-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-351-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-352-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-353-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Lagangilang',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
