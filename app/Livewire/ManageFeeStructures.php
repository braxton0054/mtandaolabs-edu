<?php

namespace App\Livewire;

use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\MyClass;
use App\Models\Semester;
use App\Services\Fee\FeeStructureService;
use App\Services\MyClass\MyClassService;
use Livewire\Component;

class ManageFeeStructures extends Component
{
    public $classes;

    public $classId;

    public $semesters;

    public $semesterId;

    public $fees;

    public $lines;

    public $feeId;

    public $amount;

    public $message;

    protected $rules = [
        'classId' => 'required|exists:my_classes,id',
        'semesterId' => 'required|exists:semesters,id',
        'feeId' => 'required|exists:fees,id',
        'amount' => 'required|numeric|min:0',
    ];

    public function mount(MyClassService $myClassService, FeeStructureService $structures)
    {
        $this->classes = $myClassService->getAllClasses();
        $this->semesters = Semester::inSchool()->orderBy('id')->get();
        $this->fees = Fee::query()
            ->whereIn('fee_category_id', FeeCategory::query()->where('school_id', current_school_id())->pluck('id')->all())
            ->orderBy('name')
            ->get();

        $this->classId = $this->classes->first()->id ?? null;
        $this->semesterId = $this->semesters->first()->id ?? null;
        $this->feeId = $this->fees->first()->id ?? null;
        $this->loadLines($structures);
    }

    public function updatedClassId(FeeStructureService $structures)
    {
        $this->loadLines($structures);
    }

    public function updatedSemesterId(FeeStructureService $structures)
    {
        $this->loadLines($structures);
    }

    public function addLine(FeeStructureService $structures)
    {
        $this->validate();
        $class = $this->selectedClass();
        $semester = $this->selectedSemester();
        if ($class === null || $semester === null) {
            return;
        }
        $structures->setLine($class, $semester, (int) $this->feeId, (float) $this->amount);
        $this->amount = null;
        $this->message = null;
        $this->loadLines($structures);
    }

    public function removeLine(int $lineId, FeeStructureService $structures)
    {
        $line = FeeStructure::query()->where('school_id', current_school_id())->findOrFail($lineId);
        $structures->removeLine($line);
        $this->loadLines($structures);
    }

    public function generateInvoices(FeeStructureService $structures)
    {
        $class = $this->selectedClass();
        $semester = $this->selectedSemester();
        if ($class === null || $semester === null) {
            return;
        }
        $count = $structures->generateInvoices($class, $semester, now()->toDateString());
        $this->message = $count.' invoice(s) created.';
        $this->loadLines($structures);
    }

    public function render()
    {
        return view('livewire.manage-fee-structures');
    }

    private function loadLines(FeeStructureService $structures): void
    {
        $class = $this->classId === null ? null : MyClass::query()->find($this->classId);
        $semester = $this->semesterId === null ? null : Semester::query()->find($this->semesterId);
        $this->lines = $class === null || $semester === null ? collect() : $structures->linesFor($class, $semester);
    }

    private function selectedClass(): ?MyClass
    {
        return $this->classId === null ? null : MyClass::query()->find($this->classId);
    }

    private function selectedSemester(): ?Semester
    {
        return $this->semesterId === null ? null : Semester::query()->find($this->semesterId);
    }
}
