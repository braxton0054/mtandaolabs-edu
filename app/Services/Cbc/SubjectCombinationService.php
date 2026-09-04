<?php

namespace App\Services\Cbc;

use App\Enums\CbcLevel;
use App\Exceptions\InvalidValueException;
use App\Models\MyClass;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Validate and assign subject combinations.
 *
 * Every learner takes all compulsory examinable subjects offered in their
 * class. Senior School (Grades 10-12) learners additionally choose exactly
 * three electives, at least two of which must come from one pathway.
 * Non-examinable subjects (PE, ICT) are never required and never counted.
 */
class SubjectCombinationService
{
    /**
     * Check a set of subjects chosen for a student.
     *
     * The class defaults to the student's current class. Pass it explicitly
     * when validating a combination for a class the student is moving into,
     * such as a Senior School placement.
     *
     * @param  Collection<int, Subject>  $subjects
     * @return list<string> empty when the combination is valid.
     */
    public function validateForStudent(User $student, Collection $subjects, ?MyClass $class = null): array
    {
        $errors = [];
        if ($class === null) {
            $record = StudentRecord::query()->where('user_id', $student->id)->first();
            $class = $record === null ? null : MyClass::query()->find($record->my_class_id);
        }

        if ($class === null) {
            return ['Student is not assigned to a class.'];
        }

        $offered = Subject::query()->where('my_class_id', $class->id)->get();
        $chosenIds = $subjects->pluck('id')->all();

        $foreign = array_diff($chosenIds, $offered->pluck('id')->all());
        if ($foreign !== []) {
            $errors[] = 'Some subjects are not offered in '.$class->name.'.';
        }

        $missingCore = $offered
            ->where('is_compulsory', true)
            ->where('is_examinable', true)
            ->reject(fn (Subject $subject) => in_array($subject->id, $chosenIds, true));
        foreach ($missingCore as $subject) {
            $errors[] = $subject->name.' is compulsory and must be included.';
        }

        $electives = $offered
            ->where('is_compulsory', false)
            ->where('is_examinable', true)
            ->filter(fn (Subject $subject) => in_array($subject->id, $chosenIds, true))
            ->values();

        if ($class->level === CbcLevel::SeniorSchool) {
            if ($electives->count() !== 3) {
                $errors[] = 'Senior School learners must choose exactly 3 electives.';
            } elseif ($this->largestPathwayGroup($electives) < 2) {
                $errors[] = 'At least 2 electives must come from one pathway.';
            }
        } elseif ($electives->isNotEmpty()) {
            $errors[] = $class->name.' offers no electives; only class subjects apply.';
        }

        return $errors;
    }

    /**
     * Assign a validated set of subjects to a student.
     *
     * @param  list<int>  $subjectIds
     *
     * @throws InvalidValueException
     */
    public function assignToStudent(User $student, array $subjectIds): void
    {
        $subjects = Subject::query()->whereIn('id', $subjectIds)->get();
        $errors = $this->validateForStudent($student, $subjects);

        if ($errors !== []) {
            throw new InvalidValueException(implode(' ', $errors));
        }

        $student->enrolledSubjects()->sync($subjects->pluck('id')->all());
    }

    /**
     * Count the electives in the most represented pathway.
     *
     * @param  Collection<int, Subject>  $electives
     */
    private function largestPathwayGroup(Collection $electives): int
    {
        return $electives
            ->groupBy(fn (Subject $subject) => $subject->pathway_id ?? 'none')
            ->map->count()
            ->max() ?? 0;
    }
}
