<?php

namespace Database\Seeders;

use App\Models\EmpStatus;
use Illuminate\Database\Seeder;

class EmpStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Regular', 'description' => null],
            ['name' => 'Retired', 'description' => null],
            ['name' => 'Dismissed', 'description' => null],
        ];

        foreach ($statuses as $status) {
            EmpStatus::create($status);
        }
    }
}
