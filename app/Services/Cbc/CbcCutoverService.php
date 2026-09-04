<?php

namespace App\Services\Cbc;

use App\Enums\CbcLevel;
use App\Models\ClassGroup;
use App\Models\GradeSystem;
use App\Models\MyClass;
use App\Models\School;

/**
 * Convert a school's 8-4-4 class structure to the CBC taxonomy.
 *
 * For every school this ensures the five CBC ClassGroups exist, moves each
 * legacy class into its CBC group (renaming it to the official class name),
 * copies the school's grading bands onto the new groups, and removes legacy
 * groups left empty. Classes it cannot recognise are left untouched.
 *
 * The run is idempotent: classes already sitting in levelled groups are
 * skipped, so running it twice changes nothing the second time.
 */
class CbcCutoverService
{
    public function run(): void
    {
        foreach (School::query()->get() as $school) {
            $this->cutoverSchool($school);
        }
    }

    private function cutoverSchool(School $school): void
    {
        $groups = [];
        foreach (CbcLevel::cases() as $level) {
            $groups[$level->value] = ClassGroup::query()->firstOrCreate(
                ['school_id' => $school->id, 'name' => $level->label()],
                ['level' => $level]
            );
        }

        $legacyClasses = MyClass::query()
            ->whereHas('classGroup', fn ($query) => $query->where('school_id', $school->id)->whereNull('level'))
            ->with('classGroup')
            ->get();

        foreach ($legacyClasses as $class) {
            $mapping = $this->mapClassName($class->name);
            if ($mapping === null) {
                continue;
            }

            [$level, $name] = $mapping;
            $target = $groups[$level->value];

            if ($class->class_group_id === $target->id && $class->name === $name) {
                continue;
            }

            $taken = MyClass::query()
                ->where('class_group_id', $target->id)
                ->where('name', $name)
                ->where('id', '!=', $class->id)
                ->exists();
            if ($taken) {
                continue;
            }

            $class->update(['class_group_id' => $target->id, 'name' => $name]);
        }

        $this->copyGradeSystems($school, $groups);

        ClassGroup::query()
            ->where('school_id', $school->id)
            ->whereNull('level')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('my_classes')->whereColumn('my_classes.class_group_id', 'class_groups.id'))
            ->delete();
    }

    /**
     * Map a legacy class name to its CBC level and official class name.
     *
     * @return array{0: CbcLevel, 1: string}|null null when unrecognised.
     */
    public function mapClassName(string $name): ?array
    {
        $normalized = strtolower(trim($name));

        if (preg_match('/^(kindergarten|nursery|pp|pre[\s-]?primary)\s*([12]?)$/', $normalized, $matches) === 1) {
            $number = $matches[2] === '2' ? 2 : 1;

            return [CbcLevel::PrePrimary, "PP{$number}"];
        }

        if (preg_match('/^(primary|grade|class|std|standard)\s*([1-9][0-9]*)$/', $normalized, $matches) === 1) {
            $number = (int) $matches[2];

            return match (true) {
                $number >= 1 && $number <= 3 => [CbcLevel::LowerPrimary, "Grade {$number}"],
                $number >= 4 && $number <= 6 => [CbcLevel::UpperPrimary, "Grade {$number}"],
                $number >= 7 && $number <= 8 => [CbcLevel::JuniorSchool, "Grade {$number}"],
                default => null,
            };
        }

        if (preg_match('/^(secondary|form)\s*([1-4])$/', $normalized, $matches) === 1) {
            $number = (int) $matches[2];

            return match ($number) {
                1 => [CbcLevel::JuniorSchool, 'Grade 9'],
                2 => [CbcLevel::SeniorSchool, 'Grade 10'],
                3 => [CbcLevel::SeniorSchool, 'Grade 11'],
                4 => [CbcLevel::SeniorSchool, 'Grade 12'],
                default => null,
            };
        }

        return null;
    }

    /**
     * Give every new CBC group the school's grading bands.
     *
     * @param  array<string, ClassGroup>  $groups
     */
    private function copyGradeSystems(School $school, array $groups): void
    {
        $groupIds = ClassGroup::query()->where('school_id', $school->id)->pluck('id');
        $template = GradeSystem::query()
            ->whereIn('class_group_id', $groupIds)
            ->orderBy('id')
            ->get()
            ->groupBy('class_group_id')
            ->first();

        if ($template === null || $template->isEmpty()) {
            return;
        }

        foreach ($groups as $group) {
            $hasBands = GradeSystem::query()->where('class_group_id', $group->id)->exists();
            if ($hasBands) {
                continue;
            }

            foreach ($template as $band) {
                GradeSystem::query()->create([
                    'name' => $band->name,
                    'remark' => $band->remark,
                    'grade_from' => $band->grade_from,
                    'grade_till' => $band->grade_till,
                    'class_group_id' => $group->id,
                ]);
            }
        }
    }
}
