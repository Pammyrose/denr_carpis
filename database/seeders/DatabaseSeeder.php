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

            // PENRO Seeders
            ProvinceAbraSeeder::class,
            ProvinceApayaoSeeder::class,
            ProvinceBenguetSeeder::class,
            ProvinceIfugaoSeeder::class,
            ProvinceKalingaSeeder::class,
            ProvinceMtProvinceSeeder::class,

            // CENRO Seeders
            ProvinceBanguedSeeder::class,
            ProvinceLagangilangSeeder::class,
            ProvinceConnerSeeder::class,
            ProvinceCalanasanSeeder::class,
            ProvinceBaguioSeeder::class,
            ProvinceBuguiasSeeder::class,
            ProvinceAlfonsoListaSeeder::class,
            ProvinceLamutSeeder::class,
            ProvinceTabukSeeder::class,
            ProvincePinukpukSeeder::class,
            ProvinceParacelisSeeder::class,
            ProvinceSabanganSeeder::class,

            // CTI Seeder
            ProvinceCTISeeder::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
