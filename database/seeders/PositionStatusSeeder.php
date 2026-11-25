<?php

namespace Database\Seeders;

use App\Models\PositionStatus;
use Illuminate\Database\Seeder;

class PositionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['status' => 'vacant',    'remarks' => 'Position is available'],
            ['status' => 'occupied',  'remarks' => 'Position is currently filled'],
        ];

        foreach ($statuses as $status) {
            PositionStatus::create($status);
        }

        $this->command->info('PositionStatusSeeder: vacant & occupied statuses seeded successfully!');
    }
}
