<?php

namespace App\Services\Cbc;

use Carbon\Carbon;

/**
 * The Ministry of Education basic-education calendar.
 *
 * Kenya runs three terms inside one calendar year (January to December):
 * Term 1 opens in early January, Term 2 after the April holiday, Term 3
 * after the August holiday, and the KNEC national assessments (KPSEA,
 * KJSEA, KSSEA) run from late October into November.
 *
 * The 2026 dates below are the official MoE circular. Other years follow
 * the same shape as estimates until that year's circular is published.
 */
class KenyaSchoolCalendar
{
    /**
     * Term dates for a year.
     *
     * @return list<array{name: string, start: string, stop: string, midterm_start: string, midterm_stop: string, weeks: int}>
     */
    public function termsFor(int $year): array
    {
        if ($year === 2026) {
            return [
                [
                    'name' => 'Term 1', 'start' => '2026-01-05', 'stop' => '2026-04-02',
                    'midterm_start' => '2026-02-25', 'midterm_stop' => '2026-03-01', 'weeks' => 13,
                ],
                [
                    'name' => 'Term 2', 'start' => '2026-04-27', 'stop' => '2026-07-31',
                    'midterm_start' => '2026-06-24', 'midterm_stop' => '2026-06-28', 'weeks' => 14,
                ],
                [
                    'name' => 'Term 3', 'start' => '2026-08-24', 'stop' => '2026-10-23',
                    'midterm_start' => '2026-10-07', 'midterm_stop' => '2026-10-11', 'weeks' => 9,
                ],
            ];
        }

        return $this->patternFor($year);
    }

    /**
     * National assessment windows for a year.
     *
     * @return list<array{name: string, code: string, start: string, stop: string, grades: string}>
     */
    public function nationalsFor(int $year): array
    {
        if ($year === 2026) {
            return [
                ['name' => 'KPSEA', 'code' => 'kpsea', 'start' => '2026-10-26', 'stop' => '2026-10-28', 'grades' => 'Grade 6'],
                ['name' => 'KJSEA', 'code' => 'kjsea', 'start' => '2026-10-26', 'stop' => '2026-11-20', 'grades' => 'Grade 9'],
                ['name' => 'KSSEA', 'code' => 'kssea', 'start' => '2026-10-26', 'stop' => '2026-11-20', 'grades' => 'Grade 12'],
            ];
        }

        $monday = $this->lastMondayOfOctober($year);

        return [
            ['name' => 'KPSEA', 'code' => 'kpsea', 'start' => $monday->toDateString(), 'stop' => $monday->copy()->addDays(2)->toDateString(), 'grades' => 'Grade 6'],
            ['name' => 'KJSEA', 'code' => 'kjsea', 'start' => $monday->toDateString(), 'stop' => $monday->copy()->addDays(25)->toDateString(), 'grades' => 'Grade 9'],
            ['name' => 'KSSEA', 'code' => 'kssea', 'start' => $monday->toDateString(), 'stop' => $monday->copy()->addDays(25)->toDateString(), 'grades' => 'Grade 12'],
        ];
    }

    /**
     * Estimate the three terms from the usual shape.
     *
     * @return list<array{name: string, start: string, stop: string, midterm_start: string, midterm_stop: string, weeks: int}>
     */
    private function patternFor(int $year): array
    {
        $term1Start = Carbon::create($year, 1, 1)->startOfDay();
        if ($term1Start->dayOfWeek !== Carbon::MONDAY) {
            $term1Start->next(Carbon::MONDAY);
        }
        $term1Stop = $term1Start->copy()->addWeeks(13)->previous(Carbon::FRIDAY);

        $term2Start = $term1Stop->copy()->addWeeks(3)->next(Carbon::MONDAY);
        $term2Stop = $term2Start->copy()->addWeeks(14)->previous(Carbon::FRIDAY);

        $term3Start = $term2Stop->copy()->addWeeks(3)->next(Carbon::MONDAY);
        $term3Stop = $term3Start->copy()->addWeeks(9)->previous(Carbon::FRIDAY);

        $midterm = fn (Carbon $start, int $weeks) => [
            $start->copy()->addWeeks($weeks)->previous(Carbon::WEDNESDAY)->toDateString(),
            $start->copy()->addWeeks($weeks)->previous(Carbon::WEDNESDAY)->addDays(4)->toDateString(),
        ];

        [$mid1Start, $mid1Stop] = $midterm($term1Start, 7);
        [$mid2Start, $mid2Stop] = $midterm($term2Start, 8);
        [$mid3Start, $mid3Stop] = $midterm($term3Start, 6);

        return [
            ['name' => 'Term 1', 'start' => $term1Start->toDateString(), 'stop' => $term1Stop->toDateString(), 'midterm_start' => $mid1Start, 'midterm_stop' => $mid1Stop, 'weeks' => 13],
            ['name' => 'Term 2', 'start' => $term2Start->toDateString(), 'stop' => $term2Stop->toDateString(), 'midterm_start' => $mid2Start, 'midterm_stop' => $mid2Stop, 'weeks' => 14],
            ['name' => 'Term 3', 'start' => $term3Start->toDateString(), 'stop' => $term3Stop->toDateString(), 'midterm_start' => $mid3Start, 'midterm_stop' => $mid3Stop, 'weeks' => 9],
        ];
    }

    private function lastMondayOfOctober(int $year): Carbon
    {
        $date = Carbon::create($year, 10, 31)->startOfDay();
        if ($date->dayOfWeek !== Carbon::MONDAY) {
            $date->previous(Carbon::MONDAY);
        }

        return $date;
    }
}
