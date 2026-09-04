<?php

namespace App\Models;

use App\Enums\CbcLevel;
use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use InSchool;
    use SoftDeletes;

    protected $fillable = [
        'name', 'short_name', 'school_id', 'my_class_id',
        'pathway_id', 'is_compulsory', 'is_examinable',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
        'is_examinable' => 'boolean',
    ];

    /**
     * Get the class that owns the Subject.
     *
     * @return BelongsTo
     */
    public function myClass()
    {
        return $this->belongsTo(MyClass::class);
    }

    /**
     * Get the Senior School pathway the elective belongs to, if any.
     *
     * @return BelongsTo
     */
    public function pathway()
    {
        return $this->belongsTo(Pathway::class);
    }

    /**
     * Get the CBC level of the subject through its class.
     */
    public function getLevelAttribute(): ?CbcLevel
    {
        $class = MyClass::query()->find($this->my_class_id);

        return $class?->level;
    }

    /**
     * The students enrolled in the Subject as an elective.
     *
     * @return BelongsToMany
     */
    public function enrolledStudents()
    {
        return $this->belongsToMany(User::class, 'student_subject');
    }

    /**
     * The teachers that belong to the Subject.
     *
     * @return BelongsToMany
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'subject_user');
    }

    /**
     * Get the subjects timetable records.
     */
    public function timetableRecord(): MorphOne
    {
        return $this->morphOne(TimetableRecord::class, 'timetable_time_slot_weekdayable');
    }
}
