<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Kenya runs the school year inside one calendar year (January to
     * December), so start and stop fall in the same year.
     */
    public function run()
    {
        $year = (int) date('Y');
        $academicYear = AcademicYear::firstOrCreate([
            'school_id' => 1,
            'start_year' => (string) $year,
        ], [
            'stop_year' => (string) $year,
        ]);
        $academicYear->school->academic_year_id = $academicYear->id;
        $academicYear->school->save();
    }
}
