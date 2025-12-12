<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    public function run()
    {

 DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 $this->call([
        
            DivisionSectionUnitSeeder::class,
            UserSeeder::class,
            PositionSeeder::class,
            AssignmentNameSeeder::class,
            EmpStatusSeeder::class,
            PositionStatusSeeder::class,
            AdminSeeder::class,
            CDDSeeder::class,
            EnforcementSeeder::class,
            FinanceSeeder::class,
            LegalSeeder::class,
            LPDDSeeder::class,
            ORDSeeder::class,
            PMDSeeder::class,
            SMDSeeder::class,
        ]);
 DB::statement('SET FOREIGN_KEY_CHECKS=1;');



    }
}