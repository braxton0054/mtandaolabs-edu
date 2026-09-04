<?php

namespace App\Models;

use App\Enums\CbcLevel;
use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'name', 'school_id', 'level',
    ];

    protected $casts = [
        'level' => CbcLevel::class,
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->hasMany(MyClass::class);
    }

    public function gradeSystem()
    {
        return $this->hasMany(GradeSystem::class);
    }
}
