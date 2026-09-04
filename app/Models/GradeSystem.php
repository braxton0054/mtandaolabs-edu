<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSystem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'remark', 'grade_from', 'grade_till', 'class_group_id', 'competency_level_id'];

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function competencyLevel()
    {
        return $this->belongsTo(CompetencyLevel::class);
    }
}
