<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceAlfonsoListaSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // AlfonsoLista CENRO Positions
            ['OSEC-DENRB-CENRO-76-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-160-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-59-2014', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-143-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-196-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-117-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-135-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-738-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-739-1998', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-194-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-16-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-277-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1005-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-1012-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-96-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-118-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-51-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-170-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-883-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-884-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-885-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-886-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-887-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-888-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-781-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-792-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-804-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-480-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1353-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1354-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1355-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1356-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1357-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1358-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1359-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1360-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1361-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1362-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1363-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1364-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1365-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1366-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1367-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1368-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-275-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-92-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-181-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-187-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2424-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2426-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2428-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2429-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2432-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2435-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2438-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2440-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2441-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2446-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-361-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Alfonso Lista',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
