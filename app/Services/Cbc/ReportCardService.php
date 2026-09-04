<?php

namespace App\Services\Cbc;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\GradeSystem;
use App\Models\MyClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Build a per-learner CBC report card.
 *
 * Each row carries raw marks next to the CBC interpretation: percentage,
 * competency level (EE/ME/AP/BE), and the school's grading band. Only
 * examinable subjects appear; PE and ICT are reported by the school, not
 * on the card.
 */
class ReportCardService
{
    public function __construct(private CompetencyService $competency) {}

    /**
     * @param  Collection<int, Exam>  $exams  published exams in the period.
     * @param  Collection<int, ExamRecord>  $records  the learner's records in the period.
     * @param  Collection<int, Subject>  $subjects  the class subjects.
     * @return array{rows: list<array<string, mixed>>, total: int, attainable: int, percent: int, competency: ?string, grade: string}
     */
    public function build(User $student, MyClass $class, Collection $exams, Collection $records, Collection $subjects): array
    {
        $groupId = $class->class_group_id;
        $attainable = $exams->sum(fn ($exam) => $exam->examSlots->sum('total_marks'));

        // The card covers the learner's combination: every compulsory
        // examinable subject plus their enrolled electives.
        $takenIds = $subjects
            ->where('is_compulsory', true)
            ->where('is_examinable', true)
            ->pluck('id')
            ->merge($student->enrolledSubjects()->pluck('subjects.id'))
            ->unique()
            ->all();
        $taken = $subjects->whereIn('id', $takenIds)->values();

        $rows = [];
        foreach ($taken as $subject) {
            $obtained = $records->where('subject_id', $subject->id)->sum('student_marks');
            $percent = $attainable > 0 ? (int) ceil($obtained / $attainable * 100) : 0;
            $band = $this->bandFor($groupId, $percent);

            $rows[] = [
                'subject' => $subject->name,
                'obtained' => $obtained,
                'attainable' => $attainable,
                'percent' => $percent,
                'competency' => $this->competency->forPercentage($groupId, $percent)?->code,
                'grade' => $band ? $band->name : 'No Grade',
                'remark' => $band ? $band->remark : '',
            ];
        }

        $total = array_sum(array_column($rows, 'obtained'));
        $overallAttainable = $attainable * count($rows);
        $percent = $overallAttainable > 0 ? (int) ceil($total / $overallAttainable * 100) : 0;
        $overallBand = $this->bandFor($groupId, $percent);

        return [
            'rows' => $rows,
            'total' => $total,
            'attainable' => $overallAttainable,
            'percent' => $percent,
            'competency' => $this->competency->forPercentage($groupId, $percent)?->code,
            'grade' => $overallBand ? $overallBand->name : 'No Grade',
        ];
    }

    private function bandFor(int $groupId, int $percent): ?GradeSystem
    {
        return GradeSystem::query()
            ->where('class_group_id', $groupId)
            ->where('grade_from', '<=', $percent)
            ->where('grade_till', '>=', $percent)
            ->first();
    }
}
