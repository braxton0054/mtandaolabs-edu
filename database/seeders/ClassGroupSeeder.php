<?php

namespace Database\Seeders;

use App\Enums\CbcLevel;
use App\Models\ClassGroup;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach (CbcLevel::cases() as $level) {
            ClassGroup::firstOrCreate(
                ['school_id' => 1, 'name' => $level->label()],
                ['level' => $level]
            );
        }
    }
}
