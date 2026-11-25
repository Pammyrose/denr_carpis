<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceCTISeeder extends Seeder
{
    public function run()
    {

        $positions = [
            // CTI Positions
            ['OSEC-DENRB-ENG3-95-1998', 'Engineer III', 19],
            ['OSEC-DENRB-ENG1-53-1998', 'Engineer I', 12],
            ['OSEC-DENRB-FORST1-29-1998', 'Forester I', 11],
            ['OSEC-DENRB-ADOF1-38-2004', 'Administrative Officer I', 10],
            ['OSEC-DENRB-ADAS3-29-200', 'Administrative Assistant III', 9],
            ['OSEC-DENRB-CDA2-63-1998', 'Community Development Assistant II', 9],
            ['OSEC-DENRB-CDA2-65-1998', 'Community Development Assistant II', 9],
            ['OSEC-DENRB-ADAS2-52-2004', 'Administrative Assistant II', 8],
            ['OSEC-DENRB-ADAS2-54-2004', 'Administrative Assistant II', 8],
            ['OSEC-DENRB-CGR2-158-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-162-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-163-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-168-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-170-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-CGR2-173-1998', 'Cartographer II', 8],
            ['OSEC-DENRB-FASU-92-1998', 'Farm Supervisor', 8],
            ['OSEC-DENRB-FASU-93-1998', 'Farm Supervisor', 8],
            ['OSEC-DENRB-FASU-96-1998', 'Farm Supervisor', 8],
            ['OSEC-DENRB-FASU-98-1998', 'Farm Supervisor', 8],
            ['OSEC-DENRB-MTHA2-110-1998', 'Mathematician Aide II', 8],
            ['OSEC-DENRB-MTHA2-111-1998', 'Mathematician Aide II', 8],
            ['OSEC-DENRB-ADA6-86-2004', 'Administrative Aide VI', 6],
            ['OSEC-DENRB-SURVM-130-1998', 'Surveyman', 6],
            ['OSEC-DENRB-SURVM-131-1998', 'Surveyman', 6],
            ['OSEC-DENRB-BC-73-1998', 'Bill Collector', 5],
            ['OSEC-DENRB-SC-595-1998', 'Scaler', 5],
            ['OSEC-DENRB-ADA4-117-2004', 'Administrative Aide IV (Storekeeper)', 4],
            ['OSEC-DENRB-ADA4-133-2004', "Administrative Aide IV (Rep'n Machine Operator)", 4],
            ['OSEC-DENRB-FORA-2260-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2273-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2338-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2388-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2449-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2462-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2471-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2485-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2492-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2495-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2499-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2502-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2505-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2509-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2513-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2525-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2530-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2539-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2542-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2548-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2549-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2552-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2553-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2572-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-2574-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3332-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3333-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3335-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3348-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-FORA-3349-1998', 'Forest Ranger', 4],
            ['OSEC-DENRB-ADA3-74-2004', 'Administrative Aide III (Driver I)', 3],
            ['OSEC-DENRB-ADA3-78-2004', 'Administrative Aide III (Driver I)', 3],
            ['OSEC-DENRB-LA2-49-1998', 'Laborer II', 3],
            ['OSEC-DENRB-SECG1-63-1998', 'Security Guard I', 3],
            ['OSEC-DENRB-TMRK-706-1998', 'Tree Marker', 2],
            ['OSEC-DENRB-TMRK-711-1998', 'Tree Marker', 2],
            ['OSEC-DENRB-TMRK-714-1998', 'Tree Marker', 2],
            ['OSEC-DENRB-TMRK-719-1998', 'Tree Marker', 2],
            ['OSEC-DENRB-TMRK-722-1998', 'Tree Marker', 2],
            ['OSEC-DENRB-ADA1-79-2004', 'Administrative Aide I (Utility Worker I)', 1],
            ['OSEC-DENRB-LA1-310-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-316-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-317-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-324-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-326-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-328-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-329-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-330-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-331-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-337-1998', 'Laborer I', 1],
            ['OSEC-DENRB-LA1-347-1998', 'Laborer I', 1],
            ['OSEC-DENRB-UTW1-192-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-203-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-206-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-207-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-209-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-210-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-213-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-214-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-219-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-228-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-236-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-247-1998', 'Utility Worker I', 1],
            ['OSEC-DENRB-UTW1-253-1998', 'Utility Worker I', 1],
        ];

        foreach ($positions as [$item_code, $name, $grade]) {
            DB::table('positions')->updateOrInsert(
                ['item_code' => $item_code],
                [
                    'name' => $name,
                    'salary_grade' => $grade,
                    'desc' => 'CTI',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
