<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Requests\UpdateExamStatusRequest;
use App\Models\AcademicYear;
use App\Models\AcademicYearStudentRecord;
use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\ExamSlot;
use App\Models\MyClass;
use App\Models\ParentRecord;
use App\Models\Semester;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use App\Services\Cbc\ReportCardService;
use App\Services\Exam\ExamService;
use App\Services\Print\PrintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ExamController extends Controller
{
    public ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
        $this->authorizeResource(Exam::class, 'exam');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pages.exam.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.exam.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExamRequest $request): RedirectResponse
    {
        $data = $request->except('_token');
        $exam = $this->examService->createExam($data);

        return redirect()->route('exam-slots.create', $exam)->with('success', 'Exam created successfully, Now, create exam slots for the exam');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam): Response
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam): View
    {
        return view('pages.exam.edit', compact('exam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->except(['_method', '_token']);
        $this->examService->updateExam($exam, $data);

        return back()->with('success', 'Exam updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam): RedirectResponse
    {
        $this->examService->deleteExam($exam);

        return back()->with('success', 'Exam deleted successfully');
    }

    /**
     * Tabulation for exams.
     */
    public function examTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('pages.exam.tabulation');
    }

    /**
     * Tabulation for semester results.
     */
    public function semesterResultTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('pages.exam.semester-result-tabulation');
    }

    /**
     * Tabulation for academic year results.
     */
    public function academicYearResultTabulation(): View
    {
        $this->authorize('viewAny', Exam::class);

        return view('pages.exam.academic-year-result-tabulation');
    }

    /**
     * Result checker.
     */
    public function resultChecker(): View
    {
        $this->authorize('checkResult', Exam::class);

        return view('pages.exam.result-checker');
    }

    /**
     * Download a learner's CBC report card as PDF.
     */
    public function printReportCard(User $student, string $scope, int $scopeId, ReportCardService $reportCards): Response
    {
        $this->authorize('checkResult', Exam::class);
        $viewer = auth()->user();

        if ($viewer->hasRole(Role::Student) && $viewer->id !== $student->id) {
            abort(403);
        }
        if ($viewer->hasRole(Role::Parent) && !$this->isOwnChild($viewer, $student)) {
            abort(403);
        }

        if ($scope === 'semester') {
            $semester = Semester::findOrFail($scopeId);
            $examIds = $semester->exams()->where('publish_result', true)->pluck('exams.id')->all();
            $academicYearId = $semester->academic_year_id;
            $label = $semester->name;
        } elseif ($scope === 'academic-year') {
            $academicYear = AcademicYear::findOrFail($scopeId);
            $examIds = $academicYear->exams()->where('publish_result', true)->pluck('exams.id')->all();
            $academicYearId = $academicYear->id;
            $label = $academicYear->start_year.' - '.$academicYear->stop_year;
        } else {
            abort(404);
        }

        $exams = Exam::query()->whereIn('id', $examIds)->with('examSlots')->get();
        if ($exams->isEmpty()) {
            abort(404, 'No published results for this period.');
        }
        $slotIds = ExamSlot::query()->whereIn('exam_id', $examIds)->pluck('id')->all();
        $examRecords = ExamRecord::query()->where('user_id', $student->id)->whereIn('exam_slot_id', $slotIds)->get();

        $class = $this->classForYear($student, $academicYearId);
        if ($class === null) {
            abort(404, 'No class record for this academic year.');
        }

        $subjects = Subject::query()->where('my_class_id', $class->id)->orderBy('name')->get();
        $card = $reportCards->build($student, $class, $exams, $examRecords, $subjects);
        $admissionNumber = StudentRecord::withoutGlobalScope('notGraduated')
            ->where('user_id', $student->id)->value('admission_number') ?? '';

        return PrintService::createPdfFromView('pages.exam.print-report-card', [
            'school' => current_school(),
            'student' => $student,
            'admission_number' => $admissionNumber,
            'class' => $class,
            'label' => $label,
            'card' => $card,
        ])->download($student->name.'-report-card.pdf');
    }

    private function isOwnChild(User $viewer, User $student): bool
    {
        $parentRecord = ParentRecord::query()->where('user_id', $viewer->id)->first();

        return $parentRecord !== null && $parentRecord->students()->where('users.id', $student->id)->exists();
    }

    private function classForYear(User $student, int $academicYearId): ?MyClass
    {
        $record = StudentRecord::withoutGlobalScope('notGraduated')->where('user_id', $student->id)->first();
        if ($record === null) {
            return null;
        }
        $yearClassIds = AcademicYearStudentRecord::query()
            ->where('academic_year_id', $academicYearId)
            ->where('student_record_id', $record->id)
            ->pluck('my_class_id')
            ->all();

        return MyClass::query()->find($yearClassIds === [] ? $record->my_class_id : $yearClassIds[0]);
    }

    /**
     * Set exam status.
     */
    public function setExamActiveStatus(Exam $exam, UpdateExamStatusRequest $request): RedirectResponse
    {
        $this->authorize('update', $exam);
        // get status from request
        $status = $request->status;
        $this->examService->setExamActiveStatus($exam, $status);

        return back()->with('success', 'Exam status updated successfully');
    }

    /**
     * Set publish result status.
     */
    public function setPublishResultStatus(Exam $exam, UpdateExamStatusRequest $request): RedirectResponse
    {
        $this->authorize('update', $exam);
        // get status from request
        $status = $request->status;
        $this->examService->setPublishResultStatus($exam, $status);

        return back()->with('success', 'Result published status updated successfully');
    }
}
