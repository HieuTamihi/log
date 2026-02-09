<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Machine;
use App\Models\Subsystem;
use App\Models\Component;
use App\Models\Upgrade;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_requires_authentication_to_search()
    {
        $response = $this->get(route('search', ['q' => 'test']));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_displays_search_results_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'test']));

        $response->assertStatus(200);
        $response->assertViewIs('search.results');
        $response->assertViewHas('results');
        $response->assertViewHas('query', 'test');
    }

    /** @test */
    public function it_displays_empty_results_for_no_matches()
    {
        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    /** @test */
    public function it_displays_machine_results()
    {
        $machine = Machine::factory()->create([
            'name' => 'Sales Machine',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Sales']));

        $response->assertStatus(200);
        $response->assertSee('Sales Machine');
        $response->assertSee('Machines');
    }

    /** @test */
    public function it_displays_subsystem_results()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Lead Generation',
            'machine_id' => $machine->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Lead']));

        $response->assertStatus(200);
        $response->assertSee('Lead Generation');
        $response->assertSee('Subsystems');
    }

    /** @test */
    public function it_displays_component_results()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create([
            'name' => 'Email Campaign',
            'subsystem_id' => $subsystem->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Email']));

        $response->assertStatus(200);
        $response->assertSee('Email Campaign');
        $response->assertSee('Components');
    }

    /** @test */
    public function it_displays_upgrade_results()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Automated Follow-up',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Follow-up']));

        $response->assertStatus(200);
        $response->assertSee('Automated Follow-up');
        $response->assertSee('Upgrades');
    }

    /** @test */
    public function it_displays_total_results_count()
    {
        $machine = Machine::factory()->create([
            'name' => 'Customer Machine',
            'user_id' => $this->user->id,
        ]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Customer Support',
            'machine_id' => $machine->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Customer']));

        $response->assertStatus(200);
        $response->assertSee('Found 2 results');
    }

    /** @test */
    public function it_displays_search_query_in_results()
    {
        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'test query']));

        $response->assertStatus(200);
        $response->assertSee('test query');
    }

    /** @test */
    public function it_handles_empty_query_gracefully()
    {
        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => '']));

        $response->assertStatus(200);
        $response->assertSee('No results found');
    }

    /** @test */
    public function it_displays_links_to_machine_detail_pages()
    {
        $machine = Machine::factory()->create([
            'name' => 'Test Machine',
            'slug' => 'test-machine',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Test']));

        $response->assertStatus(200);
        $response->assertSee(route('machines.show', $machine->slug));
    }

    /** @test */
    public function it_displays_links_to_subsystem_detail_pages()
    {
        $machine = Machine::factory()->create([
            'slug' => 'test-machine',
            'user_id' => $this->user->id,
        ]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Test Subsystem',
            'slug' => 'test-subsystem',
            'machine_id' => $machine->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Test']));

        $response->assertStatus(200);
        $response->assertSee(route('subsystems.show', [
            'machineSlug' => $machine->slug,
            'subsystemSlug' => $subsystem->slug
        ]));
    }

    /** @test */
    public function it_displays_component_health_status()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create([
            'name' => 'Test Component',
            'health_status' => 'on_fire',
            'subsystem_id' => $subsystem->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Test']));

        $response->assertStatus(200);
        $response->assertSee('🔥');
    }

    /** @test */
    public function it_displays_upgrade_status()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Test Upgrade',
            'status' => 'active',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'Test']));

        $response->assertStatus(200);
        $response->assertSee('Shipped');
    }

    /** @test */
    public function it_preserves_search_query_in_search_box()
    {
        $response = $this->actingAs($this->user)
            ->get(route('search', ['q' => 'my search']));

        $response->assertStatus(200);
        $response->assertSee('value="my search"', false);
    }
}
