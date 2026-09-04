<?php

namespace Tests\Feature;

use App\Exceptions\InvalidValueException;
use App\Models\AcademicYear;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Promotion;
use App\Models\School;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Services\Cbc\PlacementService;
use App\Services\Student\StudentService;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_grade_9_learner_is_placed_into_senior_with_pathway_and_electives()
    {
        $student = $this->studentInClass('Grade 9');
        $senior = MyClass::where('name', 'Grade 10')->firstOrFail();
        $this->sectionFor($senior);
        $stem = Pathway::where('code', 'stem')->firstOrFail();
        $electives = $this->subjectIds('Grade 10', ['Physics', 'Chemistry', 'Biology']);

        $promotion = app(PlacementService::class)->placeToSenior(
            $student, $stem, $senior, $electives, 80.0, null, $this->academicYearId()
        );

        $this->assertSame($senior->id, $student->studentRecord()->first()->my_class_id);
        $this->assertSame(7, $student->enrolledSubjects()->count());
        $this->assertSame($stem->id, $promotion->pathway_id);
        $this->assertEqualsCanonicalizing($electives, $promotion->elective_subject_ids);
        $this->assertEqualsWithDelta(80.0, (float) $promotion->placement_score, 0.001);
        $this->assertSame('Grade 9', $promotion->oldClass->name);
        $this->assertSame('Grade 10', $promotion->newClass->name);
    }

    public function test_placement_rejects_non_junior_start()
    {
        $student = $this->studentInClass('Grade 6');
        $senior = MyClass::where('name', 'Grade 10')->firstOrFail();
        $stem = Pathway::where('code', 'stem')->firstOrFail();

        $this->expectException(InvalidValueException::class);

        app(PlacementService::class)->placeToSenior(
            $student, $stem, $senior, [], null, null, $this->academicYearId()
        );
    }

    public function test_placement_rejects_non_senior_target()
    {
        $student = $this->studentInClass('Grade 9');
        $junior = MyClass::where('name', 'Grade 8')->firstOrFail();
        $stem = Pathway::where('code', 'stem')->firstOrFail();

        $this->expectException(InvalidValueException::class);

        app(PlacementService::class)->placeToSenior(
            $student, $stem, $junior, [], null, null, $this->academicYearId()
        );
    }

    public function test_placement_rejects_electives_outside_the_chosen_pathway()
    {
        $student = $this->studentInClass('Grade 9');
        $senior = MyClass::where('name', 'Grade 10')->firstOrFail();
        $this->sectionFor($senior);
        $stem = Pathway::where('code', 'stem')->firstOrFail();
        $electives = $this->subjectIds('Grade 10', ['Physics', 'History and Citizenship', 'Music']);
        $promotionsBefore = Promotion::count();

        try {
            app(PlacementService::class)->placeToSenior(
                $student, $stem, $senior, $electives, null, null, $this->academicYearId()
            );
            $this->fail('Placement with off-pathway electives was accepted.');
        } catch (InvalidValueException $e) {
            $this->assertSame('Grade 9', $student->studentRecord()->first()->myClass->name);
            $this->assertSame($promotionsBefore, Promotion::count());
        }
    }

    public function test_grade_6_learner_transitions_to_junior_on_kpsea_score()
    {
        $student = $this->studentInClass('Grade 6');
        $junior = MyClass::where('name', 'Grade 7')->firstOrFail();
        $this->sectionFor($junior);
        $this->sectionFor($junior);

        $promotion = app(PlacementService::class)->transitionToJunior(
            $student, $junior, 75.5, null, $this->academicYearId()
        );

        $this->assertSame($junior->id, $student->studentRecord()->first()->my_class_id);
        $this->assertEqualsWithDelta(75.5, (float) $promotion->placement_score, 0.001);
        $this->assertNull($promotion->pathway_id);
    }

    public function test_grade_12_learner_graduates_out()
    {
        $student = $this->studentInClass('Grade 12');

        app(PlacementService::class)->graduateToExit($student);

        $this->assertTrue((bool) StudentRecord::withoutGlobalScope('notGraduated')->where('user_id', $student->id)->first()->is_graduated);
    }

    public function test_non_senior_learner_cannot_graduate()
    {
        $student = $this->studentInClass('Grade 9');

        $this->expectException(InvalidValueException::class);

        app(PlacementService::class)->graduateToExit($student);
    }

    public function test_resetting_a_placement_detaches_electives()
    {
        $student = $this->studentInClass('Grade 9');
        $senior = MyClass::where('name', 'Grade 10')->firstOrFail();
        $this->sectionFor($senior);
        $stem = Pathway::where('code', 'stem')->firstOrFail();
        $electives = $this->subjectIds('Grade 10', ['Physics', 'Chemistry', 'Biology']);

        school_context()->set(School::first(), remember: false);
        School::first()->update(['academic_year_id' => $this->academicYearId()]);
        $promotion = app(PlacementService::class)->placeToSenior(
            $student, $stem, $senior, $electives, 80.0, null, $this->academicYearId()
        );

        app(StudentService::class)->resetPromotion($promotion);

        $this->assertSame('Grade 9', $student->studentRecord()->first()->myClass->name);
        $this->assertSame(0, $student->enrolledSubjects()->count());
    }

    /**
     * @return list<int>
     */
    private function subjectIds(string $class, array $names): array
    {
        $classId = MyClass::where('name', $class)->firstOrFail()->id;

        return Subject::query()->where('my_class_id', $classId)->whereIn('name', $names)->pluck('id')->all();
    }

    private function sectionFor(MyClass $class): Section
    {
        return Section::firstOrCreate(['name' => 'Stream A', 'my_class_id' => $class->id]);
    }

    private function studentInClass(string $class): User
    {
        $myClass = MyClass::where('name', $class)->firstOrFail();
        $student = User::factory()->create();
        StudentRecord::create([
            'user_id' => $student->id,
            'my_class_id' => $myClass->id,
            'section_id' => $this->sectionFor($myClass)->id,
            'admission_date' => now()->toDateString(),
            'admission_number' => 'ADM-'.$student->id,
        ]);

        return $student;
    }

    private function academicYearId(): int
    {
        return AcademicYear::firstOrFail()->id;
    }
}
