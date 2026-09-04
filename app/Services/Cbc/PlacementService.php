<?php

namespace App\Services\Cbc;

use App\Enums\CbcLevel;
use App\Exceptions\InvalidValueException;
use App\Models\ClassGroup;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Promotion;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\User;

/**
 * Move learners across the CBC transition points.
 *
 * Grade 6 graduates into Junior School on their KPSEA score, Grade 9
 * learners are placed into a Senior School class with a pathway and an
 * elective combination, and Grade 12 learners exit graduated. Every move
 * leaves a Promotion record behind, so transitions can be audited and
 * reversed like any other promotion.
 */
class PlacementService
{
    public function __construct(private SubjectCombinationService $combinations) {}

    /**
     * Place a Junior School learner into a Senior School class.
     *
     * @param  list<int>  $electiveIds
     */
    public function placeToSenior(
        User $student,
        Pathway $pathway,
        MyClass $seniorClass,
        array $electiveIds,
        ?float $kjseaScore = null,
        ?int $sectionId = null,
        ?int $academicYearId = null,
    ): Promotion {
        $record = $this->studentRecord($student);
        $juniorClass = MyClass::query()->find($record->my_class_id);

        if ($juniorClass === null || $juniorClass->level !== CbcLevel::JuniorSchool) {
            throw new InvalidValueException('Placement starts from a Junior School class.');
        }
        if ($seniorClass->level !== CbcLevel::SeniorSchool) {
            throw new InvalidValueException('Placement targets a Senior School class.');
        }

        $electives = Subject::query()->whereIn('id', $electiveIds)->get();
        $core = Subject::query()
            ->where('my_class_id', $seniorClass->id)
            ->where('is_compulsory', true)
            ->where('is_examinable', true)
            ->get();
        $errors = $this->combinations->validateForStudent($student, $core->merge($electives), $seniorClass);

        $inPathway = $electives
            ->where('is_compulsory', false)
            ->where('is_examinable', true)
            ->filter(fn (Subject $subject) => $subject->pathway_id === $pathway->id)
            ->count();
        if ($inPathway < 2) {
            $errors[] = 'At least 2 electives must come from the '.$pathway->name.' pathway.';
        }

        if ($errors !== []) {
            throw new InvalidValueException(implode(' ', $errors));
        }

        $oldSectionId = $record->section_id ?? $this->firstSectionId($juniorClass);
        $newSectionId = $sectionId ?? $this->firstSectionId($seniorClass);

        $record->update([
            'my_class_id' => $seniorClass->id,
            'section_id' => $newSectionId,
        ]);
        $this->combinations->assignToStudent($student, $core->merge($electives)->pluck('id')->all());

        return $this->recordPromotion(
            $student, $juniorClass, $seniorClass, $oldSectionId,
            $newSectionId, $academicYearId, $kjseaScore,
            $pathway->id, $electives->pluck('id')->all(),
        );
    }

    /**
     * Move an Upper Primary learner into Junior School on their KPSEA score.
     */
    public function transitionToJunior(
        User $student,
        MyClass $juniorClass,
        ?float $kpseaScore = null,
        ?int $sectionId = null,
        ?int $academicYearId = null,
    ): Promotion {
        $record = $this->studentRecord($student);
        $upperClass = MyClass::query()->find($record->my_class_id);

        if ($upperClass === null || $upperClass->level !== CbcLevel::UpperPrimary) {
            throw new InvalidValueException('Junior School transition starts from an Upper Primary class.');
        }
        if ($juniorClass->level !== CbcLevel::JuniorSchool) {
            throw new InvalidValueException('Junior School transition targets a Junior School class.');
        }

        $oldSectionId = $record->section_id ?? $this->firstSectionId($upperClass);
        $newSectionId = $sectionId ?? $this->firstSectionId($juniorClass);
        $record->update(['my_class_id' => $juniorClass->id, 'section_id' => $newSectionId]);
        $student->enrolledSubjects()->detach();

        return $this->recordPromotion(
            $student, $upperClass, $juniorClass, $oldSectionId,
            $newSectionId, $academicYearId, $kpseaScore, null, null,
        );
    }

    /**
     * Graduate a Senior School learner out of the school.
     */
    public function graduateToExit(User $student): void
    {
        $record = $this->studentRecord($student);
        $class = MyClass::query()->find($record->my_class_id);

        if ($class === null || $class->level !== CbcLevel::SeniorSchool) {
            throw new InvalidValueException('Only Senior School learners can graduate.');
        }

        $record->update(['is_graduated' => true]);
    }

    /**
     * @param  list<int>|null  $electiveIds
     */
    private function recordPromotion(
        User $student,
        MyClass $oldClass,
        MyClass $newClass,
        int $oldSectionId,
        int $sectionId,
        ?int $academicYearId,
        ?float $placementScore,
        ?int $pathwayId,
        ?array $electiveIds,
    ): Promotion {
        $group = ClassGroup::query()->find($newClass->class_group_id);

        return Promotion::create([
            'old_class_id' => $oldClass->id,
            'new_class_id' => $newClass->id,
            'old_section_id' => $oldSectionId,
            'new_section_id' => $sectionId,
            'students' => [$student->id],
            'academic_year_id' => $academicYearId ?? current_school()->academic_year_id,
            'school_id' => $group->school_id ?? current_school_id(),
            'pathway_id' => $pathwayId,
            'elective_subject_ids' => $electiveIds,
            'placement_score' => $placementScore,
        ]);
    }

    private function studentRecord(User $student): StudentRecord
    {
        $record = StudentRecord::query()->where('user_id', $student->id)->first();

        if ($record === null) {
            throw new InvalidValueException('Student has no student record.');
        }

        return $record;
    }

    private function firstSectionId(MyClass $class): int
    {
        $section = Section::query()->where('my_class_id', $class->id)->orderBy('id')->first();

        if ($section === null) {
            throw new InvalidValueException($class->name.' has no sections yet.');
        }

        return $section->id;
    }
}
