<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceTabukSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Tabuk CENRO Positions
            ['OSEC-DENRB-CENRO-77-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-163-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-192-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-154-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-199-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-183-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-136-1998', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-740-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-83-2014', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-197-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-22-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-278-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-1015-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-1016-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-97-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-119-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-52-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-172-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-899-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-900-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-901-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-902-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-903-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-904-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-905-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-784-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-794-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-806-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-482-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1388-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1389-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1390-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1391-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1392-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1393-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1394-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1395-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1396-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1397-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1398-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1399-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1400-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1401-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1402-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1403-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-278-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-93-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-184-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-377-1998', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2452-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2457-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2458-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2459-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2461-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2463-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2466-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2468-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2469-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2472-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2475-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2476-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2479-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2480-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2481-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-366-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-367-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-368-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Tabuk',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
