<?php

namespace Tests\Feature;

use App\Enums\AssessmentType;
use App\Models\ClassGroup;
use App\Models\CompetencyLevel;
use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\ExamSlot;
use App\Models\GradeSystem;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Services\Cbc\CompetencyService;
use App\Services\Cbc\NationalAssessmentService;
use App\Traits\FeatureTestTrait;
use App\Traits\MarkTabulationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetencyAssessmentTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_competency_levels_are_seeded_with_thresholds()
    {
        $this->assertSame(['AP', 'BE', 'EE', 'ME'], CompetencyLevel::query()->orderBy('code')->pluck('code')->all());

        $ee = CompetencyLevel::where('code', 'EE')->first();
        $this->assertSame(80, $ee->min_score);
        $this->assertSame(100, $ee->max_score);
    }

    public function test_grade_groups_carry_the_four_competency_bands()
    {
        $group = ClassGroup::where('school_id', 1)->where('level', 'junior_school')->firstOrFail();
        $bands = GradeSystem::where('class_group_id', $group->id)->orderBy('grade_from')->get();

        $this->assertSame(['BE', 'AP', 'ME', 'EE'], $bands->pluck('name')->all());
        $this->assertTrue($bands->every(fn (GradeSystem $band) => $band->competency_level_id !== null));
    }

    public function test_school_bands_win_over_global_thresholds()
    {
        $group = ClassGroup::where('school_id', 1)->where('level', 'senior_school')->firstOrFail();

        $level = app(CompetencyService::class)->forPercentage($group->id, 85);

        $this->assertSame('EE', $level->code);
    }

    public function test_global_thresholds_apply_without_school_bands()
    {
        $group = ClassGroup::factory()->create(['name' => 'Bandless']);

        $this->assertSame('ME', app(CompetencyService::class)->forPercentage($group->id, 70)->code);
        $this->assertSame('BE', app(CompetencyService::class)->forPercentage(null, 20)->code);
    }

    public function test_tabulation_reports_competency_per_subject_and_overall()
    {
        $group = ClassGroup::where('school_id', 1)->where('level', 'senior_school')->firstOrFail();
        $class = MyClass::where('name', 'Grade 10')->firstOrFail();
        $english = Subject::where('my_class_id', $class->id)->where('name', 'English')->firstOrFail();
        $student = $this->studentInClass($class);
        $exam = Exam::factory()->create(['assessment_type' => AssessmentType::Summative]);
        $slot = ExamSlot::create(['name' => 'Paper 1', 'description' => 'Algebra strand', 'strand' => 'Algebra', 'total_marks' => 100, 'exam_id' => $exam->id]);
        ExamRecord::create([
            'user_id' => $student->id, 'section_id' => Section::first()->id,
            'subject_id' => $english->id, 'exam_slot_id' => $slot->id, 'student_marks' => 85,
        ]);

        $tabulated = (new TabulationHarness)->tabulateMarks(
            $group, new Collection([$english]), User::whereKey($student->id)->get(), new Collection([$slot])
        );

        $row = $tabulated->get($student->id);
        $this->assertSame(85, $row['student_marks'][$english->id]);
        $this->assertSame(85, $row['subject_percent'][$english->id]);
        $this->assertSame('EE', $row['subject_competency'][$english->id]);
        $this->assertSame('EE', $row['competency']);
        $this->assertSame('EE', $row['grade']);
    }

    public function test_kjsea_blends_summative_sixty_and_sba_forty()
    {
        $class = MyClass::where('name', 'Grade 9')->firstOrFail();
        $maths = Subject::where('my_class_id', $class->id)->where('name', 'Mathematics')->firstOrFail();
        $student = $this->studentInClass($class);
        $sectionId = Section::first()->id;

        $summative = Exam::factory()->create(['assessment_type' => AssessmentType::Summative]);
        $this->recordMark($student, $maths, $summative, $sectionId, 80);

        $sba1 = Exam::factory()->create(['assessment_type' => AssessmentType::SchoolBased]);
        $this->recordMark($student, $maths, $sba1, $sectionId, 70);
        $sba2 = Exam::factory()->create(['assessment_type' => AssessmentType::SchoolBased]);
        $this->recordMark($student, $maths, $sba2, $sectionId, 90);

        $result = app(NationalAssessmentService::class)->kjseaComposite(
            $student, $maths, $summative, collect([$sba1, $sba2])
        );

        // 0.6*80 + 0.2*70 + 0.2*90 = 80
        $this->assertEqualsWithDelta(80.0, $result['score'], 0.001);
        $this->assertSame('EE', $result['competency']);
    }

    public function test_exam_percentage_is_null_without_records()
    {
        $class = MyClass::where('name', 'Grade 9')->firstOrFail();
        $maths = Subject::where('my_class_id', $class->id)->where('name', 'Mathematics')->firstOrFail();
        $student = $this->studentInClass($class);
        $exam = Exam::factory()->create();

        $this->assertNull(app(NationalAssessmentService::class)->examPercentage($student, $maths, $exam));
    }

    public function test_exam_assessment_type_must_be_known()
    {
        $this->authorized_user(['create exam'])
            ->post('/dashboard/exams', [
                'name' => 'KJSEA Rehearsal', 'description' => 'desc', 'semester_id' => 1,
                'start_date' => now()->toDateString(), 'stop_date' => now()->addWeek()->toDateString(),
                'assessment_type' => 'kraal',
            ])
            ->assertSessionHasErrors('assessment_type');

        $this->assertDatabaseMissing('exams', ['name' => 'KJSEA Rehearsal']);
    }

    public function test_exam_defaults_to_school_based_assessment()
    {
        $exam = Exam::factory()->create();

        $this->assertSame(AssessmentType::SchoolBased, $exam->fresh()->assessment_type);
    }

    private function recordMark(User $student, Subject $subject, Exam $exam, int $sectionId, int $marks): void
    {
        $slot = ExamSlot::create([
            'name' => 'Paper 1', 'total_marks' => 100, 'exam_id' => $exam->id,
        ]);
        ExamRecord::create([
            'user_id' => $student->id, 'section_id' => $sectionId,
            'subject_id' => $subject->id, 'exam_slot_id' => $slot->id, 'student_marks' => $marks,
        ]);
    }

    private function studentInClass(MyClass $class): User
    {
        $student = User::factory()->create();
        StudentRecord::create([
            'user_id' => $student->id,
            'my_class_id' => $class->id,
            'section_id' => null,
            'admission_date' => now()->toDateString(),
            'admission_number' => 'ADM-'.$student->id,
        ]);

        return $student;
    }
}

class TabulationHarness
{
    use MarkTabulationTrait;
}
