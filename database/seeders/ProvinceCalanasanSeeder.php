<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceCalanasanSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Calanasan CENRO Positions
            ['OSEC-DENRB-CENRO-2-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-157-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-3-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-114-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-194-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-90-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-2-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-743-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-81-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-192-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-24-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-279-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1033-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-1034-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-125-2014', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-96-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-83-2014', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-167-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-866-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-867-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-868-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-869-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-870-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-871-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-872-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-778-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-789-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-801-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-98-2014', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1308-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1309-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1310-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1311-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1312-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1313-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1314-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1315-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1316-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1317-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1318-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1319-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1320-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1321-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1322-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1323-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1324-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1325-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1326-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1327-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1328-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-186-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-281-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-177-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-178-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2482-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2484-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2496-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2506-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3328-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3329-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3330-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3331-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3334-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3336-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3337-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3338-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3339-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-357-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-358-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-359-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Calanasan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
