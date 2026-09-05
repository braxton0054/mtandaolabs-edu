<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashRenderTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_dashboard_renders_new_stat_cards()
    {
        $school = School::factory()->create();
        $admin = User::factory()->platformAdmin()->create();
        $this->actingAsMemberOf($school, $admin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Class groups')
            ->assertSee('Students (active)')
            ->assertSee('Parents')
            ->assertSee('Exit to platform', false);
    }
}
