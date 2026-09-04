<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\CompetencyLevel;
use App\Models\GradeSystem;
use Illuminate\Database\Seeder;

class GradeSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Every CBC level reports against the four national competency bands,
     * so each levelled group gets exactly those four bands.
     */
    public function run()
    {
        $levels = CompetencyLevel::query()->get()->keyBy('code');
        if ($levels->isEmpty()) {
            return;
        }

        $bands = [
            ['code' => 'EE', 'grade_from' => '80', 'grade_till' => '100', 'remark' => 'Exceeding Expectation'],
            ['code' => 'ME', 'grade_from' => '65', 'grade_till' => '79', 'remark' => 'Meeting Expectation'],
            ['code' => 'AP', 'grade_from' => '50', 'grade_till' => '64', 'remark' => 'Approaching Expectation'],
            ['code' => 'BE', 'grade_from' => '0', 'grade_till' => '49', 'remark' => 'Below Expectation'],
        ];

        $groups = ClassGroup::query()->where('school_id', 1)->whereNotNull('level')->get();

        foreach ($groups as $group) {
            GradeSystem::query()->where('class_group_id', $group->id)->delete();

            foreach ($bands as $band) {
                GradeSystem::create([
                    'name' => $band['code'],
                    'remark' => $band['remark'],
                    'grade_from' => $band['grade_from'],
                    'grade_till' => $band['grade_till'],
                    'class_group_id' => $group->id,
                    'competency_level_id' => $levels->get($band['code'])?->id,
                ]);
            }
        }
    }
}
