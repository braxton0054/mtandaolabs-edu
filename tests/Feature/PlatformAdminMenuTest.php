<?php

namespace Tests\Feature;

use App\Livewire\Layouts\Menu;
use App\Models\School;
use App\Models\User;
use App\Services\School\SchoolContext;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformAdminMenuTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    /**
     * Flatten menu texts (headers, items, submenu items) for assertions.
     */
    private function menuTexts(array $menu): array
    {
        $texts = [];
        foreach ($menu as $item) {
            if (isset($item['header'])) {
                $texts[] = $item['header'];
            }
            if (isset($item['text'])) {
                $texts[] = $item['text'];
            }
            foreach ($item['submenu'] ?? [] as $sub) {
                $texts[] = $sub['text'];
            }
        }

        return $texts;
    }

    public function test_platform_admin_without_school_sees_only_platform_menu()
    {
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAs($admin);
        school_context()->forget();

        $menu = Livewire::test(Menu::class)->get('menu');
        $texts = $this->menuTexts($menu);

        $this->assertContains('Dashboard', $texts);
        $this->assertContains('Schools', $texts);
        $this->assertContains('View Logs', $texts);
        $this->assertNotContains('Administration', $texts);
        $this->assertNotContains('Academics', $texts);
        $this->assertNotContains('Fees', $texts);
        $this->assertNotContains('Parents', $texts);
        $this->assertNotContains('Teachers', $texts);
        $this->assertNotContains('Students', $texts);
    }

    public function test_platform_admin_with_school_sees_full_menu()
    {
        $school = School::factory()->create();
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAsMemberOf($school, $admin);

        $menu = Livewire::test(Menu::class)->get('menu');
        $texts = $this->menuTexts($menu);

        $this->assertContains('Fees', $texts);
        $this->assertContains('Parents', $texts);
        $this->assertContains('Administration', $texts);
    }

    public function test_school_admin_menu_is_unchanged()
    {
        $this->authorized_user(['read fee', 'read parent']);

        $menu = Livewire::test(Menu::class)->get('menu');
        $texts = $this->menuTexts($menu);

        $this->assertContains('Fees', $texts);
        $this->assertContains('Parents', $texts);
    }

    public function test_school_scoped_pages_redirect_platform_admin_without_school()
    {
        $admin = User::factory()->platformAdmin()->create();
        $admin->schoolMemberships()->delete();
        $admin = $admin->refresh();
        $this->actingAs($admin);

        $this->get('/dashboard/fees')->assertRedirect(route('schools.index'));
        $this->get('/dashboard/parents')->assertRedirect(route('schools.index'));
    }

    public function test_platform_admin_can_exit_working_school_back_to_platform()
    {
        $school = School::factory()->create();
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAsMemberOf($school, $admin);

        $this->post(route('schools.exit'))
            ->assertRedirect(route('schools.index'));

        $this->assertNull(session(SchoolContext::SESSION_KEY));
    }

    public function test_platform_admin_sees_exit_button_while_inside_a_school()
    {
        $school = School::factory()->create();
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAsMemberOf($school, $admin);

        $this->get(route('schools.index'))
            ->assertOk()
            ->assertSee('Exit to platform', false);
    }
}
