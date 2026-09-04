<?php

namespace App\Livewire;

use App\Enums\CbcLevel;
use App\Models\MyClass;
use App\Models\Pathway;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PlaceSeniorLearners extends Component
{
    public $juniorClasses;

    public $juniorClass;

    public $students;

    public $pathways;

    public $seniorClasses;

    public $seniorClass;

    public $seniorSections;

    public $electives;

    protected $rules = [
        'juniorClass' => 'required|exists:my_classes,id',
        'seniorClass' => 'required|exists:my_classes,id',
    ];

    public function mount()
    {
        $this->juniorClasses = $this->classesForLevel(CbcLevel::JuniorSchool);
        $this->seniorClasses = $this->classesForLevel(CbcLevel::SeniorSchool);
        $this->pathways = Pathway::query()->orderBy('name')->get();

        $firstJunior = $this->juniorClasses->first();
        if ($firstJunior !== null) {
            $this->juniorClass = $firstJunior->id;
            $this->loadStudents();
        }
        $firstSenior = $this->seniorClasses->first();
        if ($firstSenior !== null) {
            $this->seniorClass = $firstSenior->id;
            $this->loadSeniorOptions();
        }
    }

    public function updatedJuniorClass()
    {
        $this->loadStudents();
    }

    public function updatedSeniorClass()
    {
        $this->loadSeniorOptions();
    }

    public function loadStudents()
    {
        $this->validateOnly('juniorClass');
        $class = MyClass::query()->find($this->juniorClass);
        $this->students = $class === null ? collect() : $class->students();
    }

    public function loadSeniorOptions()
    {
        $this->validateOnly('seniorClass');
        $this->electives = Subject::query()
            ->where('my_class_id', $this->seniorClass)
            ->where('is_compulsory', false)
            ->where('is_examinable', true)
            ->with('pathway')
            ->orderBy('name')
            ->get();
        $class = MyClass::query()->find($this->seniorClass);
        $this->seniorSections = $class === null ? collect() : $class->sections()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, MyClass>
     */
    private function classesForLevel(CbcLevel $level): Collection
    {
        return MyClass::query()
            ->whereHas('classGroup', fn ($query) => $query->where('level', $level))
            ->with('sections')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.place-senior-learners');
    }
}
