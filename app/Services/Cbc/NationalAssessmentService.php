<?php

namespace App\Services\Cbc;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Score national assessments and their weighted blends.
 *
 * The KJSEA placement score blends the Grade 9 summative exam (60%) with
 * school-based assessments from Grades 7 and 8 (40%). Scores resolve to a
 * CBC competency level through the national thresholds.
 */
class NationalAssessmentService
{
    public function __construct(private CompetencyService $competency) {}

    /**
     * Percentage a student scored in one exam for one subject.
     *
     * Only slots with a recorded mark count towards the attainable total,
     * so absent or unmarked slots never drag the score down.
     */
    public function examPercentage(User $student, Subject $subject, Exam $exam): ?float
    {
        $slots = $exam->examSlots()->get()->keyBy('id');
        $records = ExamRecord::query()
            ->where('user_id', $student->id)
            ->where('subject_id', $subject->id)
            ->whereIn('exam_slot_id', $slots->keys()->all())
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        $recordSlotIds = $records->pluck('exam_slot_id')->unique()->all();
        $attainable = $slots->only($recordSlotIds)->sum('total_marks');
        if ($attainable <= 0) {
            return null;
        }

        return $records->sum('student_marks') / $attainable * 100;
    }

    /**
     * Blend several exam scores by weight.
     *
     * @param  array<int, array{exam: Exam, weight: float}>  $components
     */
    public function weightedScore(User $student, Subject $subject, array $components): ?float
    {
        $totalWeight = 0.0;
        $blended = 0.0;

        foreach ($components as $component) {
            $score = $this->examPercentage($student, $subject, $component['exam']);
            if ($score === null) {
                continue;
            }

            $blended += $score * $component['weight'];
            $totalWeight += $component['weight'];
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return $blended / $totalWeight;
    }

    /**
     * KJSEA placement score: 60% Grade 9 summative, 40% school-based.
     *
     * @param  Collection<int, Exam>  $sbaExams  school-based exams, e.g. Grades 7 and 8.
     * @return array{score: ?float, competency: ?string}
     */
    public function kjseaComposite(User $student, Subject $subject, Exam $summative, Collection $sbaExams): array
    {
        $components = [['exam' => $summative, 'weight' => 0.6]];
        foreach ($sbaExams as $exam) {
            $components[] = ['exam' => $exam, 'weight' => 0.4 / max($sbaExams->count(), 1)];
        }

        $score = $this->weightedScore($student, $subject, $components);
        $competency = $score === null
            ? null
            : $this->competency->forPercentage(null, (int) round($score))?->code;

        return ['score' => $score, 'competency' => $competency];
    }
}
