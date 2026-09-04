<?php

namespace Database\Seeders;

use App\Models\CompetencyLevel;
use Illuminate\Database\Seeder;

class CompetencyLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $levels = [
            [
                'code' => 'EE', 'name' => 'Exceeding Expectation',
                'description' => 'Performance is above the expected standard.',
                'min_score' => 80, 'max_score' => 100, 'sort_order' => 1,
            ],
            [
                'code' => 'ME', 'name' => 'Meeting Expectation',
                'description' => 'Performance meets the expected standard.',
                'min_score' => 65, 'max_score' => 79, 'sort_order' => 2,
            ],
            [
                'code' => 'AP', 'name' => 'Approaching Expectation',
                'description' => 'Performance is approaching the expected standard.',
                'min_score' => 50, 'max_score' => 64, 'sort_order' => 3,
            ],
            [
                'code' => 'BE', 'name' => 'Below Expectation',
                'description' => 'Performance is below the expected standard.',
                'min_score' => 0, 'max_score' => 49, 'sort_order' => 4,
            ],
        ];

        foreach ($levels as $level) {
            CompetencyLevel::firstOrCreate(['code' => $level['code']], $level);
        }
    }
}
