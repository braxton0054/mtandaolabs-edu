<?php

namespace Tests\Feature;

use App\Exceptions\InvalidValueException;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Services\Cbc\SubjectCombinationService;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SubjectCombinationTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_pathways_are_seeded()
    {
        $this->assertSame(['arts_sports', 'social_sciences', 'stem'], Pathway::query()->orderBy('code')->pluck('code')->all());
    }

    public function test_grade_10_offers_core_electives_and_non_examinable_subjects()
    {
        $subjects = $this->gradeSubjects('Grade 10');

        $this->assertSame(4, $subjects->where('is_compulsory', true)->where('is_examinable', true)->count());
        $this->assertSame(15, $subjects->where('is_compulsory', false)->count());
        $this->assertSame(['ICT', 'Physical Education'], $subjects->where('is_examinable', false)->sortBy('name')->pluck('name')->all());
        $this->assertSame(6, $subjects->where('is_compulsory', false)->whereNotNull('pathway_id')->where('pathway.code', 'stem')->count());
    }

    public function test_valid_senior_combination_assigns_electives()
    {
        $student = $this->studentInClass('Grade 10');
        $chosen = $this->gradeSubjects('Grade 10')
            ->whereIn('name', ['English', 'Kiswahili', 'Mathematics', 'Community Service Learning', 'Physics', 'Chemistry', 'Biology']);

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertSame([], $errors);

        app(SubjectCombinationService::class)->assignToStudent($student, $chosen->pluck('id')->all());

        $this->assertSame(7, $student->enrolledSubjects()->count());
    }

    public function test_missing_core_subject_is_rejected()
    {
        $student = $this->studentInClass('Grade 10');
        $chosen = $this->gradeSubjects('Grade 10')
            ->whereNotIn('name', ['English'])
            ->where(fn (Subject $subject) => $subject->is_examinable);

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertContains('English is compulsory and must be included.', $errors);
    }

    public function test_senior_learners_need_exactly_three_electives()
    {
        $student = $this->studentInClass('Grade 10');
        $chosen = $this->gradeSubjects('Grade 10')
            ->whereIn('name', ['English', 'Kiswahili', 'Mathematics', 'Community Service Learning', 'Physics', 'Chemistry']);

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertContains('Senior School learners must choose exactly 3 electives.', $errors);
    }

    public function test_electives_must_share_a_pathway()
    {
        $student = $this->studentInClass('Grade 10');
        $chosen = $this->gradeSubjects('Grade 10')
            ->whereIn('name', ['English', 'Kiswahili', 'Mathematics', 'Community Service Learning', 'Physics', 'History and Citizenship', 'Music']);

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertContains('At least 2 electives must come from one pathway.', $errors);
    }

    public function test_subjects_from_another_class_are_rejected()
    {
        $student = $this->studentInClass('Grade 10');
        $chosen = $this->gradeSubjects('Grade 10')
            ->whereIn('name', ['English', 'Kiswahili', 'Mathematics', 'Community Service Learning', 'Physics', 'Chemistry', 'Biology'])
            ->merge($this->gradeSubjects('Grade 9')->where('name', 'Agriculture'));

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertContains('Some subjects are not offered in Grade 10.', $errors);
    }

    public function test_assigning_an_invalid_combination_throws()
    {
        $student = $this->studentInClass('Grade 10');

        $this->expectException(InvalidValueException::class);

        app(SubjectCombinationService::class)->assignToStudent($student, []);
    }

    public function test_lower_primary_learner_takes_all_class_subjects()
    {
        $student = $this->studentInClass('Grade 2');
        $chosen = $this->gradeSubjects('Grade 2')->where(fn (Subject $subject) => $subject->is_examinable);

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, $chosen);

        $this->assertSame([], $errors);
    }

    public function test_student_without_a_class_is_rejected()
    {
        $student = User::factory()->create();

        $errors = app(SubjectCombinationService::class)->validateForStudent($student, new Collection);

        $this->assertSame(['Student is not assigned to a class.'], $errors);
    }

    /**
     * @return Collection<int, Subject>
     */
    private function gradeSubjects(string $class): Collection
    {
        $classId = MyClass::where('name', $class)->firstOrFail()->id;

        return Subject::query()->where('my_class_id', $classId)->with('pathway')->get();
    }

    private function studentInClass(string $class): User
    {
        $student = User::factory()->create();
        StudentRecord::create([
            'user_id' => $student->id,
            'my_class_id' => MyClass::where('name', $class)->firstOrFail()->id,
            'section_id' => null,
            'admission_date' => now()->toDateString(),
            'admission_number' => 'ADM-'.$student->id,
        ]);

        return $student;
    }
}
