<?php

namespace App\Services\Cbc;

use App\Models\CompetencyLevel;
use App\Models\GradeSystem;

/**
 * Map a percentage score to a CBC competency level.
 *
 * The school's own grading bands for the class group win when one matches
 * and carries a linked level. Otherwise the national score thresholds on
 * the competency levels themselves apply.
 */
class CompetencyService
{
    public function forPercentage(?int $classGroupId, int $percentage): ?CompetencyLevel
    {
        if ($classGroupId !== null) {
            $band = GradeSystem::query()
                ->where('class_group_id', $classGroupId)
                ->where('grade_from', '<=', $percentage)
                ->where('grade_till', '>=', $percentage)
                ->whereNotNull('competency_level_id')
                ->first();

            if ($band !== null) {
                $level = CompetencyLevel::query()->find($band->competency_level_id);
                if ($level !== null) {
                    return $level;
                }
            }
        }

        return CompetencyLevel::query()
            ->where('min_score', '<=', $percentage)
            ->where('max_score', '>=', $percentage)
            ->orderBy('sort_order')
            ->first();
    }
}
