<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SMDSeeder extends Seeder
{
    public function run()
    {
    

        $positions = [
            ['OSEC-DENRB-ENG5-9-1998', 'Engineer V (Geodetic Engineer)', 24],
            ['OSEC-DENRB-ENG3-47-2014', 'Engineer III (Geodetic Engineer)', 19],
            ['OSEC-DENRB-ENG3-48-2014', 'Engineer III (Geodetic Engineer)', 19],
            ['OSEC-DENRB-ENG3-49-2014', 'Engineer III (Geodetic Engineer)', 19],
            ['OSEC-DENRB-ENG3-94-1998', 'Engineer III (Geodetic Engineer)', 19],
            ['OSEC-DENRB-ENG3-96-1998', 'Engineer III (Geodetic Engineer)', 19],
            ['OSEC-DENRB-FORST3-48-1998', 'Forester III', 18],
            ['OSEC-DENRB-FORST3-58-2014', 'Forester III', 18],
            ['OSEC-DENRB-SRFMS-84-2014', 'Senior Forest Management Specialist', 18],
            ['OSEC-DENRB-ENG2-111-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ENG2-112-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-ENG2-113-2014', 'Engineer II (Geodetic Engineer)', 16],
            ['OSEC-DENRB-CGR4-39-1998', 'Cartographer IV', 15],
            ['OSEC-DENRB-ECOMS2-166-2014', 'Ecosystems Management Specialist II', 15],
            ['OSEC-DENRB-FORST2-201-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-74-2014', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-75-2014', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-753-1998', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-76-2014', 'Forester II', 15],
            ['OSEC-DENRB-FORST2-77-2014', 'Forester II', 15],
            ['OSEC-DENRB-ADOF3-22-2004', 'Administrative Officer III (Records Officer II)', 14],
            ['OSEC-DENRB-COMRPO1-104-2014', 'Computer Programmer I', 11],
            ['OSEC-DENRB-ECOMS1-73-1998', 'Ecosystems Management Specialist I', 11],
            ['OSEC-DENRB-ADOF1-18-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-ADOF1-19-2004', 'Administrative Officer I (Records Officer I)', 10],
            ['OSEC-DENRB-MTH1-34-1998', 'Mathematician I', 10],
            ['OSEC-DENRB-MTH1-35-1998', 'Mathematician I', 10],
            ['OSEC-DENRB-CGR2-160-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-166-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-199-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-PITEC2-4-1998', 'Prescision Instrument Technician II', 8],
            ['OSEC-DENRB-ADA6-753-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-754-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-755-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-ADA6-756-2014', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-CGR1-255-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-463-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-466-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-468-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-469-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-470-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-472-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-473-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-476-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-CGR1-500-1998', 'Cartographer I', 6],
            ['OSEC-DENRB-MTHA1-138-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-140-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-141-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-142-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-143-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-146-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-149-1998', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-78-2014', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-79-2014', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-MTHA1-80-2014', 'Mathematician Aide I', 6],
            ['OSEC-DENRB-SURVM-140-1998', 'Surveyman', 6],
            ['OSEC-DENRB-SURVM-78-2014', 'Surveyman', 6],
            ['OSEC-DENRB-SURVM-79-2014', 'Surveyman', 6],
            ['OSEC-DENRB-SURVM-80-2014', 'Surveyman', 6],
            ['OSEC-DENRB-SURVM-81-2014', 'Surveyman', 6],
            ['OSEC-DENRB-ENGA-164-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-165-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-166-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-167-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-168-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-169-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-170-2014', 'Engineering Aide', 4],
            ['OSEC-DENRB-ENGA-171-2014', 'Engineering Aide', 4],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'SURVEYS AND MAPPING DIVISION',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
