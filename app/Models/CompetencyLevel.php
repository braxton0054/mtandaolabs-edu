<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A CBC performance level.
 *
 * Learners are reported against four national levels instead of raw marks:
 * Exceeding, Meeting, Approaching, or Below Expectation.
 */
class CompetencyLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'min_score', 'max_score', 'sort_order',
    ];

    /**
     * Get all of the grading bands mapped to the level.
     */
    public function gradeSystems(): HasMany
    {
        return $this->hasMany(GradeSystem::class);
    }
}
