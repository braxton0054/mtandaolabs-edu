<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Models\Exam;
use App\Models\Semester;
use App\Services\Cbc\KenyaSchoolCalendar;
use Illuminate\Database\Seeder;

class NationalAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * KNEC runs the national assessments in Term 3: KPSEA at the end of
     * Grade 6, KJSEA at the end of Grade 9, and KSSEA at the end of
     * Grade 12. They are seeded as national exam shells; results flow
     * through the normal exam records.
     */
    public function run()
    {
        $year = (int) date('Y');
        $term3 = Semester::query()
            ->where('school_id', 1)
            ->where('name', 'Term 3')
            ->orderByDesc('id')
            ->first();

        if ($term3 === null) {
            return;
        }

        foreach (app(KenyaSchoolCalendar::class)->nationalsFor($year) as $national) {
            Exam::firstOrCreate(
                ['semester_id' => $term3->id, 'name' => $national['name'].' '.$year],
                [
                    'description' => $national['name'].' national assessment for '.$national['grades'].'.',
                    'start_date' => $national['start'],
                    'stop_date' => $national['stop'],
                    'assessment_type' => AssessmentType::National,
                ]
            );
        }
    }
}
