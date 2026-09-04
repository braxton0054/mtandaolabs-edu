<?php

namespace App\Enums;

/**
 * The five CBC levels of basic education (2-6-3-3-3).
 *
 * Every ClassGroup belongs to exactly one level. The level drives which
 * learning areas, assessments, and (in Senior School) pathways apply, so it
 * is the single source of truth for "where in the CBC journey is this class".
 */
enum CbcLevel: string
{
    /**
     * PP1-PP2, ages ~4-5. Foundation stage.
     */
    case PrePrimary = 'pre_primary';

    /**
     * Grades 1-3, ages ~6-8.
     */
    case LowerPrimary = 'lower_primary';

    /**
     * Grades 4-6, ages ~9-11. Ends with the KPSEA national assessment.
     */
    case UpperPrimary = 'upper_primary';

    /**
     * Grades 7-9, ages ~12-14. Ends with the KJSEA national assessment,
     * which places learners into a Senior School pathway.
     */
    case JuniorSchool = 'junior_school';

    /**
     * Grades 10-12, ages ~15-17. Learners follow one pathway:
     * STEM, Social Sciences, or Arts and Sports Science.
     */
    case SeniorSchool = 'senior_school';

    /**
     * Get the label to show in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::PrePrimary => 'Pre-Primary',
            self::LowerPrimary => 'Lower Primary',
            self::UpperPrimary => 'Upper Primary',
            self::JuniorSchool => 'Junior School',
            self::SeniorSchool => 'Senior School',
        };
    }

    /**
     * Get the official class names that belong to this level, in order.
     *
     * @return list<string>
     */
    public function classNames(): array
    {
        return match ($this) {
            self::PrePrimary => ['PP1', 'PP2'],
            self::LowerPrimary => ['Grade 1', 'Grade 2', 'Grade 3'],
            self::UpperPrimary => ['Grade 4', 'Grade 5', 'Grade 6'],
            self::JuniorSchool => ['Grade 7', 'Grade 8', 'Grade 9'],
            self::SeniorSchool => ['Grade 10', 'Grade 11', 'Grade 12'],
        };
    }
}
