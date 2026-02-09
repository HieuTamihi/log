<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SearchService;
use App\Models\Machine;
use App\Models\Subsystem;
use App\Models\Component;
use App\Models\Upgrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchService $searchService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new SearchService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_returns_empty_results_for_empty_query()
    {
        $results = $this->searchService->search('');

        $this->assertEquals(0, $results['total']);
        $this->assertEmpty($results['machines']);
        $this->assertEmpty($results['subsystems']);
        $this->assertEmpty($results['components']);
        $this->assertEmpty($results['upgrades']);
    }

    /** @test */
    public function it_searches_machines_by_name()
    {
        $machine = Machine::factory()->create([
            'name' => 'Sales Machine',
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchMachines('Sales');

        $this->assertCount(1, $results);
        $this->assertEquals($machine->id, $results->first()->id);
    }

    /** @test */
    public function it_searches_machines_by_description()
    {
        $machine = Machine::factory()->create([
            'name' => 'Test Machine',
            'description' => 'Handles customer acquisition',
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchMachines('acquisition');

        $this->assertCount(1, $results);
        $this->assertEquals($machine->id, $results->first()->id);
    }

    /** @test */
    public function it_searches_subsystems_by_name()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Lead Generation',
            'machine_id' => $machine->id,
        ]);

        $results = $this->searchService->searchSubsystems('Lead');

        $this->assertCount(1, $results);
        $this->assertEquals($subsystem->id, $results->first()->id);
    }

    /** @test */
    public function it_searches_components_by_name()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create([
            'name' => 'Email Campaign',
            'subsystem_id' => $subsystem->id,
        ]);

        $results = $this->searchService->searchComponents('Email');

        $this->assertCount(1, $results);
        $this->assertEquals($component->id, $results->first()->id);
    }

    /** @test */
    public function it_searches_upgrades_by_name()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Automated Follow-up',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchUpgrades('Follow-up');

        $this->assertCount(1, $results);
        $this->assertEquals($upgrade->id, $results->first()->id);
    }

    /** @test */
    public function it_searches_upgrades_by_purpose()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Test Upgrade',
            'purpose' => 'Improve customer retention',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchUpgrades('retention');

        $this->assertCount(1, $results);
        $this->assertEquals($upgrade->id, $results->first()->id);
    }

    /** @test */
    public function it_returns_all_matching_results_across_entities()
    {
        $machine = Machine::factory()->create([
            'name' => 'Customer Machine',
            'user_id' => $this->user->id,
        ]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Customer Support',
            'machine_id' => $machine->id,
        ]);
        $component = Component::factory()->create([
            'name' => 'Customer Onboarding',
            'subsystem_id' => $subsystem->id,
        ]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Customer Welcome Email',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->search('Customer');

        $this->assertEquals(4, $results['total']);
        $this->assertCount(1, $results['machines']);
        $this->assertCount(1, $results['subsystems']);
        $this->assertCount(1, $results['components']);
        $this->assertCount(1, $results['upgrades']);
    }

    /** @test */
    public function it_performs_case_insensitive_search()
    {
        $machine = Machine::factory()->create([
            'name' => 'Sales Machine',
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchMachines('SALES');

        $this->assertCount(1, $results);
        $this->assertEquals($machine->id, $results->first()->id);
    }

    /** @test */
    public function it_performs_partial_text_matching()
    {
        $machine = Machine::factory()->create([
            'name' => 'Sales Machine',
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchMachines('ale');

        $this->assertCount(1, $results);
        $this->assertEquals($machine->id, $results->first()->id);
    }

    /** @test */
    public function it_loads_relationships_for_machines()
    {
        $machine = Machine::factory()->create([
            'name' => 'Test Machine',
            'user_id' => $this->user->id,
        ]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);

        $results = $this->searchService->searchMachines('Test');

        $this->assertTrue($results->first()->relationLoaded('subsystems'));
        $this->assertTrue($results->first()->subsystems->first()->relationLoaded('components'));
    }

    /** @test */
    public function it_loads_relationships_for_subsystems()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create([
            'name' => 'Test Subsystem',
            'machine_id' => $machine->id,
        ]);

        $results = $this->searchService->searchSubsystems('Test');

        $this->assertTrue($results->first()->relationLoaded('machine'));
        $this->assertTrue($results->first()->relationLoaded('components'));
    }

    /** @test */
    public function it_loads_relationships_for_components()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create([
            'name' => 'Test Component',
            'subsystem_id' => $subsystem->id,
        ]);

        $results = $this->searchService->searchComponents('Test');

        $this->assertTrue($results->first()->relationLoaded('subsystem'));
        $this->assertTrue($results->first()->subsystem->relationLoaded('machine'));
        $this->assertTrue($results->first()->relationLoaded('upgrades'));
    }

    /** @test */
    public function it_loads_relationships_for_upgrades()
    {
        $machine = Machine::factory()->create(['user_id' => $this->user->id]);
        $subsystem = Subsystem::factory()->create(['machine_id' => $machine->id]);
        $component = Component::factory()->create(['subsystem_id' => $subsystem->id]);
        $upgrade = Upgrade::factory()->create([
            'name' => 'Test Upgrade',
            'component_id' => $component->id,
            'user_id' => $this->user->id,
        ]);

        $results = $this->searchService->searchUpgrades('Test');

        $this->assertTrue($results->first()->relationLoaded('component'));
        $this->assertTrue($results->first()->component->relationLoaded('subsystem'));
        $this->assertTrue($results->first()->component->subsystem->relationLoaded('machine'));
    }
}
