<?php

namespace App\Models;

use App\Casts\Money;
use App\Traits\InSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One fee line for one class in one term.
 *
 * Each school sets its own amounts per grade per term: Grade 5 Term 1
 * tuition never has to equal Grade 3 Term 1 tuition. Invoices for the
 * whole class are generated from these lines.
 */
class FeeStructure extends Model
{
    use HasFactory;
    use InSchool;

    protected $fillable = [
        'school_id', 'my_class_id', 'semester_id', 'fee_id', 'amount',
    ];

    protected $casts = [
        'amount' => Money::class,
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function myClass(): BelongsTo
    {
        return $this->belongsTo(MyClass::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
}
