<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSectionUnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            ['name' => 'OFFICE OF THE REGIONAL DIRECTOR', 'org_code' => 'ORD'],
            ['name' => 'FINANCE DIVISION', 'org_code' => 'FD'],
            ['name' => 'ADMINISTRATIVE DIVISION', 'org_code' => 'AD'],
            ['name' => 'LEGAL DIVISION', 'org_code' => 'LD'],
            ['name' => 'CONSERVATION AND DEVELOPMENT DIVISION', 'org_code' => 'CDD'],
            ['name' => 'SURVEYS AND MAPPING DIVISION', 'org_code' => 'SMD'],
            ['name' => 'LICENSES, PATENTS AND DEEDS DIVISION', 'org_code' => 'LPDD'],
            ['name' => 'ENFORCEMENT DIVISION', 'org_code' => 'ED'],
            ['name' => 'PLANNING AND MANAGEMENT DIVISION', 'org_code' => 'PMD'],

            // ABRA
            ['name' => 'PENRO Abra', 'org_code' => 'PENRO_ABRA'],
            ['name' => 'MSD-PENRO Abra', 'org_code' => 'MSD_ABRA'],
            ['name' => 'TSD-PENRO Abra', 'org_code' => 'TSD_ABRA'],

            // Bangued
            ['name' => 'CENRO Bangued', 'org_code' => 'CENRO_BANGUED'],
            ['name' => 'CENRO Lagangilang', 'org_code' => 'CENRO_LAGANGILANG'],

            // APAYAO
            ['name' => 'PENRO Apayao', 'org_code' => 'PENRO_APAYAO'],
            ['name' => 'MSD-PENRO Apayao', 'org_code' => 'MSD_APAYAO'],
            ['name' => 'TSD-PENRO Apayao', 'org_code' => 'TSD_APAYAO'],

            ['name' => 'CENRO Conner', 'org_code' => 'CENRO_CONNER'],
            ['name' => 'CENRO Calanasan', 'org_code' => 'CENRO_CALANASAN'],

            // BENGUET
            ['name' => 'PENRO Benguet', 'org_code' => 'PENRO_BENGUET'],
            ['name' => 'MSD-PENRO Benguet', 'org_code' => 'MSD_BENGUET'],
            ['name' => 'TSD-PENRO Benguet', 'org_code' => 'TSD_BENGUET'],
            ['name' => 'CENRO Baguio', 'org_code' => 'CENRO_BAGUIO'],
            ['name' => 'CENRO Buguias', 'org_code' => 'CENRO_BUGUIAS'],

            // IFUGAO
            ['name' => 'PENRO Ifugao', 'org_code' => 'PENRO_IFUGAO'],
            ['name' => 'MSD-PENRO Ifugao', 'org_code' => 'MSD_IFUGAO'],
            ['name' => 'TSD-PENRO Ifugao', 'org_code' => 'TSD_IFUGAO'],
            ['name' => 'CENRO Alfonso Lista', 'org_code' => 'CENRO_ALFONSO_LISTA'],
            ['name' => 'CENRO Lamut', 'org_code' => 'CENRO_LAMUT'],

            // KALINGA
            ['name' => 'PENRO Kalinga', 'org_code' => 'PENRO_KALINGA'],
            ['name' => 'MSD-PENRO Kalinga', 'org_code' => 'MSD_KALINGA'],
            ['name' => 'TSD-PENRO Kalinga', 'org_code' => 'TSD_KALINGA'],
            ['name' => 'CENRO Pinukpuk', 'org_code' => 'CENRO_PINUKPUK'],
            ['name' => 'CENRO Tabuk', 'org_code' => 'CENRO_TABUK'],

            // MOUNTAIN PROVINCE
            ['name' => 'PENRO Mountain Province', 'org_code' => 'PENRO_MTPROV'],
            ['name' => 'MSD-PENRO Mt. Province', 'org_code' => 'MSD_MTPROV'],
            ['name' => 'TSD-PENRO Mt. Province', 'org_code' => 'TSD_MTPROV'],
            ['name' => 'CENRO Paracelis', 'org_code' => 'CENRO_PARACELIS'],
            ['name' => 'CENRO Sabangan', 'org_code' => 'CENRO_SABANGAN'],

            ['name' => 'CTI', 'org_code' => 'CTI'],
        ];

        foreach ($units as $unit) {
            DB::table('div_sec_units')->insert([
                'name' => $unit['name'],
                'org_code' => $unit['org_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
