<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RunInProductionSeeder::class,
            SchoolSeeder::class,
            ClassGroupSeeder::class,
            MyClassSeeder::class,
            SectionSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            PathwaySeeder::class,
            SubjectSeeder::class,
            AcademicYearSeeder::class,
            SemesterSeeder::class,
            PromotionSeeder::class,
            SyllabusSeeder::class,
            TimetableSeeder::class,
            ExamSeeder::class,
            NationalAssessmentSeeder::class,
            CompetencyLevelSeeder::class,
            GradeSystemSeeder::class,
            ExamSlotSeeder::class,
            ExamRecordSeeder::class,
            NoticeSeeder::class,
            FeeCategorySeeder::class,
            FeeSeeder::class,
            FeeInvoiceSeeder::class,
            FeeInvoiceRecordSeeder::class,
            FeeStructureSeeder::class,
        ]);
    }
}
