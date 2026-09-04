<?php

namespace Tests\Feature;

use App\Enums\CbcLevel;
use App\Models\ClassGroup;
use App\Models\GradeSystem;
use App\Models\MyClass;
use App\Models\School;
use App\Services\Cbc\CbcCutoverService;
use App\Traits\FeatureTestTrait;
use Database\Seeders\ClassGroupSeeder;
use Database\Seeders\MyClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbcLevelTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_cbc_levels_cover_pp1_to_grade_12()
    {
        $this->assertCount(5, CbcLevel::cases());
        $this->assertSame(['PP1', 'PP2'], CbcLevel::PrePrimary->classNames());
        $this->assertSame(['Grade 1', 'Grade 2', 'Grade 3'], CbcLevel::LowerPrimary->classNames());
        $this->assertSame(['Grade 4', 'Grade 5', 'Grade 6'], CbcLevel::UpperPrimary->classNames());
        $this->assertSame(['Grade 7', 'Grade 8', 'Grade 9'], CbcLevel::JuniorSchool->classNames());
        $this->assertSame(['Grade 10', 'Grade 11', 'Grade 12'], CbcLevel::SeniorSchool->classNames());
    }

    public function test_class_group_factory_assigns_a_valid_level()
    {
        $group = ClassGroup::factory()->create();

        $this->assertInstanceOf(CbcLevel::class, $group->level);
    }

    public function test_authorized_user_can_create_class_group_with_level()
    {
        $this->authorized_user(['create class group'])
            ->post('/dashboard/class-groups', ['name' => 'Junior School', 'level' => CbcLevel::JuniorSchool->value]);

        $this->assertDatabaseHas('class_groups', [
            'name' => 'Junior School',
            'level' => CbcLevel::JuniorSchool->value,
            'school_id' => 1,
        ]);
    }

    public function test_class_group_level_must_be_a_known_cbc_level()
    {
        $this->authorized_user(['create class group'])
            ->post('/dashboard/class-groups', ['name' => 'Bogus', 'level' => 'o-level'])
            ->assertSessionHasErrors('level');

        $this->assertDatabaseMissing('class_groups', ['name' => 'Bogus']);
    }

    public function test_authorized_user_can_update_class_group_level()
    {
        $classGroup = ClassGroup::factory()->create(['level' => CbcLevel::LowerPrimary]);
        $this->authorized_user(['update class group'])
            ->put("/dashboard/class-groups/$classGroup->id", ['name' => $classGroup->name, 'level' => CbcLevel::UpperPrimary->value]);

        $this->assertDatabaseHas('class_groups', [
            'id' => $classGroup->id,
            'level' => CbcLevel::UpperPrimary->value,
        ]);
    }

    public function test_class_reports_its_cbc_level_through_its_group()
    {
        $group = ClassGroup::factory()->create(['level' => CbcLevel::SeniorSchool]);
        $class = MyClass::factory()->create(['name' => 'Grade 10', 'class_group_id' => $group->id]);

        $this->assertSame(CbcLevel::SeniorSchool, $class->level);
    }

    public function test_seeders_create_five_levels_and_fourteen_classes()
    {
        // Tests\TestCase seeds the full DatabaseSeeder before each test.
        // Re-running the seeders must be a no-op.
        (new ClassGroupSeeder)->run();
        (new MyClassSeeder)->run();

        $this->assertSame(5, ClassGroup::where('school_id', 1)->whereNotNull('level')->count());
        $this->assertSame(14, MyClass::whereHas('classGroup', fn ($query) => $query->where('school_id', 1))->count());

        foreach (CbcLevel::cases() as $level) {
            foreach ($level->classNames() as $name) {
                $this->assertDatabaseHas('my_classes', [
                    'name' => $name,
                ]);
            }
        }
    }

    public function test_cutover_moves_legacy_classes_into_cbc_levels()
    {
        $school = School::factory()->create();
        $primary = ClassGroup::factory()->create(['school_id' => $school->id, 'name' => 'Primary', 'level' => null]);
        $secondary = ClassGroup::factory()->create(['school_id' => $school->id, 'name' => 'Secondary', 'level' => null]);
        $kindergarten = ClassGroup::factory()->create(['school_id' => $school->id, 'name' => 'Kindergarten', 'level' => null]);
        MyClass::factory()->create(['name' => 'Primary 2', 'class_group_id' => $primary->id]);
        MyClass::factory()->create(['name' => 'Primary 5', 'class_group_id' => $primary->id]);
        MyClass::factory()->create(['name' => 'Form 1', 'class_group_id' => $secondary->id]);
        MyClass::factory()->create(['name' => 'Form 4', 'class_group_id' => $secondary->id]);
        MyClass::factory()->create(['name' => 'Kindergarten 1', 'class_group_id' => $kindergarten->id]);
        MyClass::factory()->create(['name' => 'Drama Club', 'class_group_id' => $secondary->id]);
        GradeSystem::create([
            'name' => 'A', 'remark' => 'Excellent', 'grade_from' => '80', 'grade_till' => '100', 'class_group_id' => $primary->id,
        ]);

        app(CbcCutoverService::class)->run();

        $levels = ClassGroup::where('school_id', $school->id)->whereNotNull('level')->pluck('level');
        $this->assertCount(5, $levels);

        $this->assertDatabaseHas('my_classes', ['name' => 'Grade 2']);
        $this->assertDatabaseHas('my_classes', ['name' => 'Grade 5']);
        $this->assertDatabaseHas('my_classes', ['name' => 'Grade 9']);
        $this->assertDatabaseHas('my_classes', ['name' => 'Grade 12']);
        $this->assertDatabaseHas('my_classes', ['name' => 'PP1']);

        $grade2 = MyClass::where('name', 'Grade 2')->first();
        $this->assertSame(CbcLevel::LowerPrimary, $grade2->level);
        $grade12 = MyClass::where('name', 'Grade 12')->first();
        $this->assertSame(CbcLevel::SeniorSchool, $grade12->level);

        // Unrecognised classes stay where they are.
        $this->assertDatabaseHas('my_classes', ['name' => 'Drama Club', 'class_group_id' => $secondary->id]);

        // Grading bands were copied onto the new groups.
        $juniorId = ClassGroup::where('school_id', $school->id)->where('level', CbcLevel::JuniorSchool)->first()->id;
        $this->assertDatabaseHas('grade_systems', ['name' => 'A', 'class_group_id' => $juniorId]);

        // Emptied legacy groups are gone.
        $this->assertDatabaseMissing('class_groups', ['id' => $primary->id]);
        $this->assertDatabaseMissing('class_groups', ['id' => $kindergarten->id]);

        // Running again changes nothing.
        app(CbcCutoverService::class)->run();
        $this->assertSame(5, ClassGroup::where('school_id', $school->id)->whereNotNull('level')->count());
    }

    public function test_cutover_maps_legacy_names_to_official_class_names()
    {
        $service = app(CbcCutoverService::class);

        $this->assertSame([CbcLevel::PrePrimary, 'PP1'], $service->mapClassName('Nursery 1'));
        $this->assertSame([CbcLevel::PrePrimary, 'PP2'], $service->mapClassName('Kindergarten 2'));
        $this->assertSame([CbcLevel::LowerPrimary, 'Grade 1'], $service->mapClassName('Primary 1'));
        $this->assertSame([CbcLevel::UpperPrimary, 'Grade 6'], $service->mapClassName('Standard 6'));
        $this->assertSame([CbcLevel::JuniorSchool, 'Grade 7'], $service->mapClassName('Grade 7'));
        $this->assertSame([CbcLevel::JuniorSchool, 'Grade 9'], $service->mapClassName('Form 1'));
        $this->assertSame([CbcLevel::SeniorSchool, 'Grade 10'], $service->mapClassName('Form 2'));
        $this->assertNull($service->mapClassName('Drama Club'));
    }
}
