<?php

namespace App\Services\Fee;

use App\Exceptions\InvalidValueException;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Models\MyClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Per-term, per-grade fee structures and bulk invoicing.
 *
 * Each school prices every grade separately for every term. Invoices for
 * a whole class are generated from its structure lines, one invoice per
 * learner, and re-running generation never double-bills anyone already
 * invoiced for that term.
 */
class FeeStructureService
{
    /**
     * All structure lines for a class in a term.
     *
     * @return Collection<int, FeeStructure>
     */
    public function linesFor(MyClass $class, Semester $semester): Collection
    {
        return FeeStructure::query()
            ->where('my_class_id', $class->id)
            ->where('semester_id', $semester->id)
            ->with('fee')
            ->orderBy('id')
            ->get();
    }

    /**
     * Set one structure line. Amounts are major currency units.
     */
    public function setLine(MyClass $class, Semester $semester, int $feeId, float $amount): FeeStructure
    {
        if ($amount < 0) {
            throw new InvalidValueException('Fee amount cannot be negative');
        }

        return FeeStructure::updateOrCreate(
            [
                'school_id' => current_school_id(),
                'my_class_id' => $class->id,
                'semester_id' => $semester->id,
                'fee_id' => $feeId,
            ],
            ['amount' => $amount]
        );
    }

    public function removeLine(FeeStructure $line): void
    {
        $line->delete();
    }

    /**
     * Invoice every learner in the class from the term structure.
     *
     * @return int number of invoices created.
     */
    public function generateInvoices(MyClass $class, Semester $semester, string $issueDate, ?string $dueDate = null): int
    {
        $lines = $this->linesFor($class, $semester);
        if ($lines->isEmpty()) {
            throw new InvalidValueException($class->name.' has no fee structure for '.$semester->name);
        }

        $semesterId = (int) $semester->id;

        $created = 0;
        $className = $class->name;
        $semesterName = $semester->name;
        $semesterStop = $semester->stop_date;
        $studentIds = $class->students()->pluck('id');
        DB::transaction(function () use ($studentIds, $lines, $issueDate, $dueDate, &$created, $semesterId, $className, $semesterName, $semesterStop) {
            foreach ($studentIds as $studentId) {
                $studentId = (int) $studentId;
                $exists = FeeInvoice::query()
                    ->where('user_id', $studentId)
                    ->where('semester_id', $semesterId)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $invoice = FeeInvoice::create([
                    'name' => app(FeeInvoiceService::class)->generateInvoiceNumber(),
                    'user_id' => $studentId,
                    'semester_id' => $semesterId,
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate ?? $semesterStop ?? $issueDate,
                    'note' => $semesterName.' fees for '.$className,
                ]);

                $invoice->feeInvoiceRecords()->createMany(
                    $lines->map(fn (FeeStructure $line) => [
                        'fee_id' => $line->fee_id,
                        'amount' => $line->amount->getAmount()->toFloat(),
                    ])->all()
                );
                $created++;
            }
        });

        return $created;
    }
}
