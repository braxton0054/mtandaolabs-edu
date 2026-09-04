<?php

namespace Database\Seeders;

use App\Enums\CbcLevel;
use App\Models\ClassGroup;
use App\Models\MyClass;
use Illuminate\Database\Seeder;

class MyClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $groups = ClassGroup::query()
            ->where('school_id', 1)
            ->whereNotNull('level')
            ->get()
            ->keyBy(fn (ClassGroup $group) => $group->level->value);

        foreach (CbcLevel::cases() as $level) {
            $group = $groups->get($level->value);
            if ($group === null) {
                continue;
            }

            foreach ($level->classNames() as $name) {
                MyClass::firstOrCreate([
                    'name' => $name,
                    'class_group_id' => $group->id,
                ]);
            }
        }
    }
}
