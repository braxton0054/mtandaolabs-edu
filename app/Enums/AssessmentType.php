<?php

namespace App\Enums;

/**
 * How an exam counts towards a learner's record.
 *
 * School-based assessments run continuously in class, summative exams close
 * a term or year, and national assessments (KPSEA, KJSEA, KSSEA) are set by
 * KNEC. The KJSEA placement score blends one summative with school-based
 * results, which is why exams carry their own weight.
 */
enum AssessmentType: string
{
    /**
     * Continuous classroom assessment set by the teacher.
     */
    case SchoolBased = 'school_based';

    /**
     * End of term or year exam set by the school.
     */
    case Summative = 'summative';

    /**
     * KNEC national assessment: KPSEA, KJSEA, or KSSEA.
     */
    case National = 'national';

    /**
     * Get the label to show in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::SchoolBased => 'School-based',
            self::Summative => 'Summative',
            self::National => 'National',
        };
    }
}
