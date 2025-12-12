<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentNameSeeder extends Seeder
{
    public function run()
    {
        $assignments = ['Regional Office'];
        $region = 'Cordillera Administrative Region';

        foreach ($assignments as $assignment) {
            DB::table('assignments')->insert([
                'name'       => $assignment,
                'region'     => $region,
                'address' =>'N/A',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}