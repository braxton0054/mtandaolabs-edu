<?php

namespace Tests\Feature;

use App\Models\CompetencyLevel;
use App\Models\GradeSystem;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Promotion;
use App\Models\School;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CbcUiTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_subject_can_be_created_as_senior_elective()
    {
        $class = MyClass::where('name', 'Grade 10')->firstOrFail();
        $stem = Pathway::where('code', 'stem')->firstOrFail();

        $this->authorized_user(['create subject'])
            ->post('/dashboard/subjects', [
                'name' => 'Aviation Technology', 'short_name' => 'AVT',
                'my_class_id' => $class->id, 'school_id' => 1,
                'pathway_id' => $stem->id, 'is_compulsory' => '0', 'is_examinable' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', [
            'name' => 'Aviation Technology', 'my_class_id' => $class->id,
            'pathway_id' => $stem->id, 'is_compulsory' => false, 'is_examinable' => true,
        ]);
    }

    public function test_exam_can_be_created_as_national_assessment()
    {
        $this->authorized_user(['create exam'])
            ->post('/dashboard/exams', [
                'name' => 'KJSEA 2026', 'semester_id' => '1', 'description' => 'National assessment',
                'start_date' => '2026-10-01', 'stop_date' => '2026-10-20',
                'assessment_type' => 'national', 'weight_percent' => '60',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exams', [
            'name' => 'KJSEA 2026', 'assessment_type' => 'national', 'weight_percent' => 60,
        ]);
    }

    public function test_exam_slot_can_carry_a_strand()
    {
        $this->authorized_user(['create exam slot'])
            ->post('/dashboard/exams/1/manage/exam-slots', [
                'name' => 'Numbers strand', 'description' => 'desc',
                'strand' => 'Numbers', 'total_marks' => 20,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_slots', [
            'name' => 'Numbers strand', 'strand' => 'Numbers',
        ]);
    }

    public function test_grade_band_can_link_a_competency_level()
    {
        $group = MyClass::where('name', 'Grade 10')->firstOrFail()->class_group_id;
        $level = CompetencyLevel::where('code', 'ME')->firstOrFail();
        GradeSystem::query()->where('class_group_id', $group)->delete();

        $this->authorized_user(['create grade system'])
            ->post('/dashboard/grade-systems', [
                'name' => 'ME', 'remark' => 'Meeting', 'grade_from' => '65',
                'grade_till' => '79', 'class_group_id' => (string) $group,
                'competency_level_id' => (string) $level->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('grade_systems', [
            'name' => 'ME', 'class_group_id' => $group, 'competency_level_id' => $level->id,
        ]);
    }

    public function test_senior_placement_page_renders_for_authorized_user()
    {
        $this->authorized_user(['promote student'])
            ->get('/dashboard/students/place-senior')
            ->assertOk();
    }

    public function test_senior_placement_page_is_forbidden_without_permission()
    {
        $this->unauthorized_user()
            ->get('/dashboard/students/place-senior')
            ->assertForbidden();
    }

    public function test_senior_placement_posts_end_to_end()
    {
        $student = $this->studentInClass('Grade 9');
        $senior = MyClass::where('name', 'Grade 10')->firstOrFail();
        $seniorSection = $this->sectionFor($senior);
        $stem = Pathway::where('code', 'stem')->firstOrFail();
        $electives = Subject::query()->where('my_class_id', $senior->id)
            ->whereIn('name', ['Physics', 'Chemistry', 'Biology'])->pluck('id')->all();
        school_context()->set(School::first(), remember: false);
        School::first()->update(['academic_year_id' => 1]);

        $this->authorized_user(['promote student'])
            ->post('/dashboard/students/place-senior', [
                'student_id' => $student->id, 'senior_class_id' => $senior->id,
                'senior_section_id' => $seniorSection->id, 'pathway_id' => $stem->id,
                'electives' => $electives, 'kjsea_score' => '80',
            ])
            ->assertRedirect();

        $this->assertSame($senior->id, $student->studentRecord()->firstOrFail()->my_class_id);
        $this->assertSame(7, $student->enrolledSubjects()->count());
        $this->assertSame(1, Promotion::whereNotNull('pathway_id')->count());
    }

    public function test_placement_component_loads_options()
    {
        $school = School::first();
        school_context()->set($school, remember: false);
        $user = $this->memberOf($school);
        $user->givePermissionTo('promote student');

        Livewire::actingAs($user)
            ->test('place-senior-learners')
            ->assertOk()
            ->assertViewHas('juniorClasses')
            ->assertViewHas('seniorClasses')
            ->assertViewHas('electives');
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
}
