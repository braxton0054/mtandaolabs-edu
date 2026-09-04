<?php

namespace App\Services\Semester;

use App\Exceptions\InvalidValueException;
use App\Models\Semester;
use App\Services\Cbc\KenyaSchoolCalendar;
use Illuminate\Database\Eloquent\Collection;

class SemesterService
{
    /**
     * Get all semesters in school.
     *
     * @return Collection
     */
    public function getAllSemesters()
    {
        return Semester::inSchool()->get();
    }

    /**
     * Get all semesters in academic year.
     *
     *
     * @return Collection
     */
    public function getAllSemestersInAcademicYear(int $academicYear)
    {
        return $this->getAllSemesters()->where('academic_year_id', $academicYear);
    }

    /**
     * Get semester by Id.
     *
     *
     * @return Semester
     */
    public function getSemesterById(int $id)
    {
        return Semester::find($id);
    }

    /**
     * Create a new semester.
     *
     * @param  mixed  $data
     * @return Semester
     */
    public function createSemester($data)
    {
        $data['academic_year_id'] = current_school()->academicYear->id;
        $data['school_id'] = current_school_id();
        $semester = Semester::create([
            'name' => $data['name'],
            'school_id' => $data['school_id'],
            'academic_year_id' => $data['academic_year_id'],
            'start_date' => $data['start_date'] ?? null,
            'stop_date' => $data['stop_date'] ?? null,
            'midterm_start' => $data['midterm_start'] ?? null,
            'midterm_stop' => $data['midterm_stop'] ?? null,
        ]);

        return $semester;
    }

    /**
     * Set current semester.
     *
     *
     *
     *
     * @return void
     *
     * @throws InvalidValueException
     */
    public function setSemester(Semester $semester)
    {
        $school = current_school();
        if ($semester->academicYear->id != $school->academic_year_id) {
            throw new InvalidValueException('Term not in current academic year');
        }
        $school->semester_id = $semester->id;
        $school->save();
    }

    /**
     * Semester service.
     *
     * @param  mixed  $data
     * @return void
     */
    public function updateSemester(Semester $semester, $data)
    {
        $semester->name = $data['name'];
        $semester->start_date = $data['start_date'] ?? $semester->start_date;
        $semester->stop_date = $data['stop_date'] ?? $semester->stop_date;
        $semester->midterm_start = $data['midterm_start'] ?? $semester->midterm_start;
        $semester->midterm_stop = $data['midterm_stop'] ?? $semester->midterm_stop;
        $semester->save();
    }

    /**
     * Find the term covering a date for a school.
     */
    public function getTermOnDate(int $schoolId, string $date): ?Semester
    {
        return Semester::query()
            ->where('school_id', $schoolId)
            ->whereNotNull('start_date')
            ->whereNotNull('stop_date')
            ->where('start_date', '<=', $date)
            ->where('stop_date', '>=', $date)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Restore the official MoE term dates for a school year.
     *
     * Schools set their own opening and closing dates; this puts the
     * official calendar back as the baseline. Only terms already present
     * are updated, nothing is created or removed.
     *
     * @return int number of terms updated.
     */
    public function resetToOfficialCalendar(int $schoolId, int $year): int
    {
        $count = 0;
        foreach ((new KenyaSchoolCalendar)->termsFor($year) as $term) {
            $count += Semester::query()
                ->where('school_id', $schoolId)
                ->where('name', $term['name'])
                ->update([
                    'start_date' => $term['start'],
                    'stop_date' => $term['stop'],
                    'midterm_start' => $term['midterm_start'],
                    'midterm_stop' => $term['midterm_stop'],
                ]);
        }

        return $count;
    }

    /**
     * Delete Semester.
     *
     *
     * @return void
     */
    public function deleteSemester(Semester $semester)
    {
        $semester->delete();
    }
}
