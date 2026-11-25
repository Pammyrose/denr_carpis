<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceBuguiasSeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // Buguias CENRO Positions
            ['OSEC-DENRB-CENRO-72-1998', 'Community Environment and Natural Resources Officer', 24],
            ['OSEC-DENRB-DMO4-159-2014', 'Development Management Officer IV', 22],
            ['OSEC-DENRB-FORST3-186-1998', 'Forester III', 18],
            ['OSEC-DENRB-LAMO3-150-1998', 'Land Management Officer III', 18],
            ['OSEC-DENRB-SREMS-195-2014', 'Senior Ecosystems Management Specialist', 18],
            ['OSEC-DENRB-ENG2-179-1998', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ECOMS2-170-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-719-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-720-1998', 'Forester II', 15],
            ['OSEC-DENRB-LAMO2-193-2014', 'Land Management Officer II', 15],
            ['OSEC-DENRB-CDVO1-19-2014', 'Community Development Officer I', 11],
            ['OSEC-DENRB-ECOMS1-266-2014', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-FORST1-32-1998', 'Forester I', 11],
            ['OSEC-DENRB-FORST1-34-1998', 'Forester I', 11],
            ['OSEC-DENRB-LAMO1-104-1998', 'Land Management Officer I', 11],
            ['OSEC-DENRB-SPI1-114-1998', 'Special Investigator I', 11],
            ['OSEC-DENRB-ADOF1-47-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-CROF1-169-2014', 'Credit Officer I', 9],
            ['OSEC-DENRB-FORT2-878-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-879-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-880-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-881-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-FORT2-882-2014', 'Forest Technician II', 8],
            ['OSEC-DENRB-ADA6-780-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-791-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-803-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-478-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-FORT1-1345-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1346-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1347-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1348-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1349-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1350-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1351-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-FORT1-1352-2014', 'Forest Technician I', 6],
            ['OSEC-DENRB-LAMI-274-2014', 'Land Management Inspector', 6],
            ['OSEC-DENRB-LAMI-87-1998', 'Land Management Inspector', 6],
            ['OSEC-DENRB-ENGA-179-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-180-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-FORA-2345-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2347-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2350-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-360-2014', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-379-2014', 'Forest Ranger', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CENRO Buguias',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
