<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\ExamSlot;
use App\Models\MyClass;
use App\Models\School;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Services\Cbc\ReportCardService;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportCardTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_report_card_reports_marks_with_competency()
    {
        $setup = $this->resultSetup(85);

        $card = app(ReportCardService::class)->build(
            $setup['student'], $setup['class'], $setup['exams'], $setup['records'], $setup['subjects']
        );

        $english = collect($card['rows'])->firstWhere('subject', 'English');
        $this->assertSame(85, $english['obtained']);
        $this->assertSame(100, $english['attainable']);
        $this->assertSame(85, $english['percent']);
        $this->assertSame('EE', $english['competency']);
        $this->assertSame('EE', $card['competency']);
        $this->assertSame(array_sum(array_column($card['rows'], 'obtained')), $card['total']);
    }

    public function test_unpublished_exams_are_excluded()
    {
        $setup = $this->resultSetup(85);
        $hidden = Exam::factory()->create(['semester_id' => $setup['semester']->id, 'publish_result' => false]);
        $slot = ExamSlot::create(['name' => 'Hidden', 'total_marks' => 100, 'exam_id' => $hidden->id]);
        ExamRecord::create([
            'user_id' => $setup['student']->id, 'section_id' => Section::first()->id,
            'subject_id' => $setup['english']->id, 'exam_slot_id' => $slot->id, 'student_marks' => 100,
        ]);

        $published = $setup['semester']->exams()->where('publish_result', true)->with('examSlots')->get();
        $this->assertSame(1, $published->count());

        $card = app(ReportCardService::class)->build(
            $setup['student'], $setup['class'], $published, $setup['records'], $setup['subjects']
        );

        $this->assertSame(85, collect($card['rows'])->firstWhere('subject', 'English')['obtained']);
    }

    public function test_staff_can_download_report_card_pdf()
    {
        $setup = $this->resultSetup(72);

        $response = $this->authorized_user(['check result'])
            ->get("/dashboard/exams/report-card/{$setup['student']->id}/semester/{$setup['semester']->id}");

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_student_can_download_own_report_card()
    {
        $setup = $this->resultSetup(72);
        $setup['student']->assignRole('student');

        $response = $this->actingAsMemberOf(School::first(), $setup['student'])
            ->get("/dashboard/exams/report-card/{$setup['student']->id}/semester/{$setup['semester']->id}");

        $response->assertOk();
    }

    public function test_student_cannot_download_another_learners_card()
    {
        $setup = $this->resultSetup(72);
        $setup['student']->assignRole('student');
        $other = User::factory()->create();
        $other->assignRole('student');
        $grade9 = MyClass::where('name', 'Grade 9')->firstOrFail();
        StudentRecord::create([
            'user_id' => $other->id, 'my_class_id' => $grade9->id,
            'section_id' => Section::firstOrCreate(['name' => 'Stream A', 'my_class_id' => $grade9->id])->id,
            'admission_date' => now()->toDateString(), 'admission_number' => 'ADM-'.$other->id,
        ]);

        $this->actingAsMemberOf(School::first(), $other)
            ->get("/dashboard/exams/report-card/{$setup['student']->id}/semester/{$setup['semester']->id}")
            ->assertForbidden();
    }

    public function test_missing_results_return_not_found()
    {
        $setup = $this->resultSetup(72);
        $empty = Semester::factory()->create();

        $this->authorized_user(['check result'])
            ->get("/dashboard/exams/report-card/{$setup['student']->id}/semester/{$empty->id}")
            ->assertNotFound();
    }

    /**
     * @return array{student: User, class: MyClass, semester: Semester, english: Subject, exams: Collection, records: Collection, subjects: Collection}
     */
    private function resultSetup(int $marks): array
    {
        $class = MyClass::where('name', 'Grade 10')->firstOrFail();
        $english = Subject::where('my_class_id', $class->id)->where('name', 'English')->firstOrFail();
        $semester = Semester::factory()->create();

        $student = User::factory()->create();
        $section = Section::firstOrCreate(['name' => 'Stream A', 'my_class_id' => $class->id]);
        StudentRecord::create([
            'user_id' => $student->id, 'my_class_id' => $class->id, 'section_id' => $section->id,
            'admission_date' => now()->toDateString(), 'admission_number' => 'ADM-'.$student->id,
        ]);

        $exam = Exam::factory()->create(['semester_id' => $semester->id, 'publish_result' => true]);
        $slot = ExamSlot::create(['name' => 'Main Paper', 'total_marks' => 100, 'exam_id' => $exam->id]);
        $records = collect();
        foreach (['English', 'Kiswahili', 'Mathematics', 'Community Service Learning'] as $core) {
            $subject = Subject::where('my_class_id', $class->id)->where('name', $core)->firstOrFail();
            $records->push(ExamRecord::create([
                'user_id' => $student->id, 'section_id' => $section->id,
                'subject_id' => $subject->id, 'exam_slot_id' => $slot->id, 'student_marks' => $marks,
            ]));
        }

        $exams = collect([$exam->fresh()->load('examSlots')]);

        return [
            'student' => $student,
            'class' => $class,
            'semester' => $semester,
            'english' => $english,
            'exams' => $exams,
            'records' => $records,
            'subjects' => Subject::where('my_class_id', $class->id)->orderBy('name')->get(),
        ];
    }
}
