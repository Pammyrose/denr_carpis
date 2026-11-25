<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentNameSeeder extends Seeder
{
    public function run()
    {
        $assignments = ['Regional Office', 'PENRO Abra', 'PENRO Apayao', 'PENRO Benguet', 'PENRO Ifugao', 'PENRO Kalinga', 'PENRO MT.Province', 'CENRO Bangued', 'CENRO Lagangilang', 'CENRO Conner', 'CENRO Calanasan', 'CENRO Baguio', 'CENRO Buguias', 'CENRO Alfonso Lista', 'CENRO Lamut', 'CENRO Tabuk', 'CENRO Pinukpuk', 'CENRO Paracelis', 'CENRO Sabangan', 'CTI'];
        $region = 'Cordillera Administrative Region';

        foreach ($assignments as $assignment) {
            DB::table('assignments')->insert([
                'name' => $assignment,
                'region' => $region,
                'address' => 'N/A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
