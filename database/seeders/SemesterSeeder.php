<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\Cbc\KenyaSchoolCalendar;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $year = (int) date('Y');
        $academicYear = AcademicYear::query()
            ->where('school_id', 1)
            ->where('start_year', (string) $year)
            ->first();

        if ($academicYear === null) {
            return;
        }

        $today = Carbon::today()->toDateString();
        $current = null;

        foreach (app(KenyaSchoolCalendar::class)->termsFor($year) as $term) {
            $semester = Semester::firstOrCreate(
                ['school_id' => 1, 'academic_year_id' => $academicYear->id, 'name' => $term['name']],
                [
                    'start_date' => $term['start'],
                    'stop_date' => $term['stop'],
                    'midterm_start' => $term['midterm_start'],
                    'midterm_stop' => $term['midterm_stop'],
                ]
            );

            if ($term['start'] <= $today && $today <= $term['stop']) {
                $current = $semester;
            }
        }

        $school = $academicYear->school;
        $school->semester_id = ($current ?? Semester::query()
            ->where('school_id', 1)
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('id')
            ->first()
        )?->id;
        $school->save();
    }
}
