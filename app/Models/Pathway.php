<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A Senior School (Grades 10-12) pathway.
 *
 * Pathways are national curriculum structure, shared by every school:
 * STEM, Social Sciences, and Arts and Sports Science.
 */
class Pathway extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description',
    ];

    /**
     * Get all of the subjects in the pathway.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
}
