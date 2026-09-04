<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\School;
use App\Models\Semester;
use App\Services\Cbc\KenyaSchoolCalendar;
use App\Services\Semester\SemesterService;
use App\Traits\FeatureTestTrait;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\NationalAssessmentSeeder;
use Database\Seeders\SemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KenyaCalendarTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_2026_terms_match_the_official_moe_circular()
    {
        $terms = app(KenyaSchoolCalendar::class)->termsFor(2026);

        $this->assertSame('2026-01-05', $terms[0]['start']);
        $this->assertSame('2026-04-02', $terms[0]['stop']);
        $this->assertSame('2026-02-25', $terms[0]['midterm_start']);
        $this->assertSame('2026-03-01', $terms[0]['midterm_stop']);
        $this->assertSame('2026-04-27', $terms[1]['start']);
        $this->assertSame('2026-07-31', $terms[1]['stop']);
        $this->assertSame('2026-08-24', $terms[2]['start']);
        $this->assertSame('2026-10-23', $terms[2]['stop']);
    }

    public function test_other_years_follow_the_same_shape()
    {
        $terms = app(KenyaSchoolCalendar::class)->termsFor(2030);

        $this->assertCount(3, $terms);
        $this->assertSame([13, 14, 9], array_column($terms, 'weeks'));
        $this->assertLessThan($terms[1]['start'], $terms[0]['stop']);
        $this->assertLessThan($terms[2]['start'], $terms[1]['stop']);
    }

    public function test_2026_national_windows_match_knec()
    {
        $nationals = app(KenyaSchoolCalendar::class)->nationalsFor(2026);
        $byCode = collect($nationals)->keyBy('code');

        $this->assertSame('2026-10-26', $byCode['kpsea']['start']);
        $this->assertSame('2026-10-28', $byCode['kpsea']['stop']);
        $this->assertSame('2026-10-26', $byCode['kjsea']['start']);
        $this->assertSame('2026-11-20', $byCode['kjsea']['stop']);
        $this->assertSame('2026-11-20', $byCode['kssea']['stop']);
    }

    public function test_seeders_create_dated_terms_and_national_exams()
    {
        (new AcademicYearSeeder)->run();
        (new SemesterSeeder)->run();
        (new NationalAssessmentSeeder)->run();

        $terms = Semester::where('school_id', 1)->whereIn('name', ['Term 1', 'Term 2', 'Term 3'])->orderBy('id')->get();
        $this->assertCount(3, $terms);
        $this->assertTrue($terms->every(fn (Semester $term) => $term->start_date !== null && $term->stop_date !== null));

        $term3 = $terms->firstWhere('name', 'Term 3');
        $year = date('Y');
        $nationals = Exam::where('semester_id', $term3->id)->where('assessment_type', 'national')->orderBy('name')->pluck('name')->all();
        $this->assertSame(["KJSEA $year", "KPSEA $year", "KSSEA $year"], $nationals);

        $this->assertNotNull(School::find(1)->semester_id);
    }

    public function test_current_term_resolves_by_date()
    {
        (new AcademicYearSeeder)->run();
        (new SemesterSeeder)->run();

        $term = app(SemesterService::class)->getTermOnDate(1, '2026-06-01');

        $this->assertSame('Term 2', $term->name);
        $this->assertNull(app(SemesterService::class)->getTermOnDate(1, '2026-12-25'));
    }

    public function test_semester_can_be_created_with_dates()
    {
        $this->authorized_user(['create semester'])
            ->post('/dashboard/semesters', [
                'name' => 'Term 1', 'academic_year_id' => 1,
                'start_date' => '2026-01-05', 'stop_date' => '2026-04-02',
                'midterm_start' => '2026-02-25', 'midterm_stop' => '2026-03-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('semesters', [
            'name' => 'Term 1', 'start_date' => '2026-01-05', 'stop_date' => '2026-04-02',
        ]);
    }

    public function test_school_can_restore_official_moe_dates()
    {
        (new AcademicYearSeeder)->run();
        (new SemesterSeeder)->run();
        $term1 = Semester::where('school_id', 1)->where('name', 'Term 1')->firstOrFail();
        $term1->update(['start_date' => '2026-02-02', 'stop_date' => '2026-05-02']);

        $this->authorized_user(['set semester'])
            ->post('/dashboard/semesters/reset-calendar')
            ->assertRedirect();

        $official = app(KenyaSchoolCalendar::class)->termsFor((int) date('Y'))[0];
        $this->assertDatabaseHas('semesters', [
            'id' => $term1->id, 'start_date' => $official['start'], 'stop_date' => $official['stop'],
        ]);
    }
}
