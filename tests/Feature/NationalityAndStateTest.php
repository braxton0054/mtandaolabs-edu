<?php

namespace Tests\Feature;

use App\Livewire\NationalityAndStateInputFields;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class NationalityAndStateTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;
    use WithFaker;

    public function test_authorized_user_can_create_teacher_with_nationality_and_state()
    {
        $email = $this->faker()->freeEmail();

        $this->authorized_user(['create teacher'])->post('dashboard/teachers', [
            'first_name' => 'Test',
            'last_name' => 'teacher',
            'other_name' => 'cody',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'nationality' => 'Kenya',
            'state' => 'Nairobi',
            'city' => 'Nairobi',
            'blood_group' => 'a+',
            'address' => 'test address',
            'birthday' => '2004/04/22',
            'phone' => '08080808080',
            'my_class_id' => 1,
            'section_id' => 1,
            'admission_date' => '2004/04/22',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'nationality' => 'Kenya',
            'state' => 'Nairobi',
        ]);
    }

    public function test_nationality_and_state_dropdowns_use_livewire_safe_native_selects()
    {
        $blade = file_get_contents(resource_path('views/livewire/nationality-and-state-input-fields.blade.php'));

        // The fix: native <select> elements survive Livewire DOM-morphing, while
        // the Alpine custom <april:select> had its options panel wiped on every
        // wire:model.live round-trip so the dropdowns never opened.
        $this->assertStringContainsString('native-select', $blade);
        $this->assertStringContainsString('wire:model.live="nationality"', $blade);
        $this->assertStringContainsString('wire:model.live="state"', $blade);
        $this->assertStringNotContainsString('<x-select', $blade);
        $this->assertStringNotContainsString('wire:init', $blade);
    }

    public function test_nationality_component_loads_states_on_mount_and_updates_them()
    {
        $component = Livewire::test(NationalityAndStateInputFields::class);

        $component->assertViewHas('nationalities');
        $nationalities = $component->viewData('nationalities');
        $this->assertNotEmpty($nationalities);

        // States are populated during mount (wire:init no longer fires in Livewire 4).
        $states = $component->viewData('states');
        $this->assertNotEmpty($states);

        $component->set('nationality', $nationalities->first());
        $component->assertViewHas('states');
        $this->assertNotEmpty($component->viewData('states'));
    }
}
