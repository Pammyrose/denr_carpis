<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpStatus;

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
