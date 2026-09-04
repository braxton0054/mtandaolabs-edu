<?php

namespace Tests\Feature;

use App\Exceptions\InvalidValueException;
use App\Models\Fee;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Models\MyClass;
use App\Models\School;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentRecord;
use App\Models\User;
use App\Services\Fee\FeeStructureService;
use App\Traits\FeatureTestTrait;
use Database\Seeders\FeeStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TermFeeStructureTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_grade_5_pays_more_than_grade_3_for_the_same_term()
    {
        (new FeeStructureSeeder)->run();

        $term1 = $this->term1();
        $grade3Total = $this->structureTotal('Grade 3', $term1->id);
        $grade5Total = $this->structureTotal('Grade 5', $term1->id);

        $this->assertSame(15000, $grade3Total);
        $this->assertSame(22500, $grade5Total);
    }

    public function test_term_invoices_generate_from_the_structure_without_double_billing()
    {
        $school = School::first();
        school_context()->set($school, remember: false);
        (new FeeStructureSeeder)->run();
        $term1 = $this->term1();
        $grade5 = MyClass::where('name', 'Grade 5')->firstOrFail();
        $studentA = $this->studentInClass($grade5);
        $studentB = $this->studentInClass($grade5);

        $service = app(FeeStructureService::class);
        $before = FeeInvoice::whereIn('user_id', [$studentA->id, $studentB->id])->where('semester_id', $term1->id)->count();
        $service->generateInvoices($grade5, $term1, '2026-01-05', '2026-04-02');
        $after = FeeInvoice::whereIn('user_id', [$studentA->id, $studentB->id])->where('semester_id', $term1->id)->count();
        $this->assertSame($before + 2, $after);

        $service->generateInvoices($grade5, $term1, '2026-01-05', '2026-04-02');
        $this->assertSame($after, FeeInvoice::whereIn('user_id', [$studentA->id, $studentB->id])->where('semester_id', $term1->id)->count());

        foreach ([$studentA, $studentB] as $student) {
            $invoice = FeeInvoice::where('user_id', $student->id)->where('semester_id', $term1->id)->firstOrFail();
            $this->assertEqualsWithDelta(22500, $invoice->feeInvoiceRecords->sum(fn ($record) => $record->amount->getAmount()->toFloat()), 0.01);
        }
    }

    public function test_generation_needs_a_structure()
    {
        $school = School::first();
        school_context()->set($school, remember: false);
        $grade4 = MyClass::where('name', 'Grade 4')->firstOrFail();

        $this->expectException(InvalidValueException::class);

        app(FeeStructureService::class)->generateInvoices($grade4, $this->term1(), '2026-01-05');
    }

    public function test_negative_amounts_are_rejected()
    {
        school_context()->set(School::first(), remember: false);
        $grade5 = MyClass::where('name', 'Grade 5')->firstOrFail();
        $fee = Fee::firstOrFail();

        $this->expectException(InvalidValueException::class);

        app(FeeStructureService::class)->setLine($grade5, $this->term1(), $fee->id, -100);
    }

    public function test_structures_page_renders_for_authorized_user()
    {
        $this->authorized_user(['read fee'])
            ->get('/dashboard/fees/fee-structures')
            ->assertOk();
    }

    public function test_structures_component_loads_lines()
    {
        $school = School::first();
        school_context()->set($school, remember: false);
        $user = $this->memberOf($school);
        $user->givePermissionTo('read fee');
        (new FeeStructureSeeder)->run();

        Livewire::actingAs($user)
            ->test('manage-fee-structures')
            ->assertOk()
            ->assertViewHas('lines');
    }

    private function term1(): Semester
    {
        $term1 = Semester::where('school_id', 1)->where('name', 'Term 1')->orderBy('id')->firstOrFail();
        if ($term1->id === null) {
            throw new \RuntimeException('Term 1 is missing an id');
        }

        return $term1;
    }

    private function structureTotal(string $class, int $semesterId): int
    {
        $classId = MyClass::where('name', $class)->firstOrFail()->id;

        return (int) FeeStructure::query()
            ->where('my_class_id', $classId)
            ->where('semester_id', $semesterId)
            ->get()
            ->sum(fn ($line) => $line->amount->getAmount()->toFloat());
    }

    private function studentInClass(MyClass $class): User
    {
        $school = School::first();
        $student = User::factory()->create();
        $student->assignRole('student');
        StudentRecord::create([
            'user_id' => $student->id,
            'my_class_id' => $class->id,
            'section_id' => Section::firstOrCreate(['name' => 'Stream A', 'my_class_id' => $class->id])->id,
            'admission_date' => now()->toDateString(),
            'admission_number' => 'ADM-'.$student->id,
        ]);

        if (!$student->schoolMemberships()->where('school_id', $school->id)->exists()) {
            $student->schoolMemberships()->create([
                'school_id' => $school->id, 'status' => 'active', 'is_primary' => true, 'joined_at' => now(),
            ]);
        }

        return $student;
    }
}
