<?php

namespace Tests\Feature;

use App\Models\Party;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartyPlannerReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorder_endpoint_updates_party_positions(): void
    {
        $party1 = Party::create(['name' => 'Party 1', 'position' => 1]);
        $party2 = Party::create(['name' => 'Party 2', 'position' => 2]);
        $party3 = Party::create(['name' => 'Party 3', 'position' => 3]);

        $response = $this->postJson(route('party-planner.parties.reorder'), [
            'party_ids' => [$party3->id, $party1->id, $party2->id],
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('parties', ['id' => $party3->id, 'position' => 1]);
        $this->assertDatabaseHas('parties', ['id' => $party1->id, 'position' => 2]);
        $this->assertDatabaseHas('parties', ['id' => $party2->id, 'position' => 3]);
    }

    public function test_index_uses_party_position_order(): void
    {
        Party::create(['name' => 'Party A', 'position' => 2]);
        Party::create(['name' => 'Party B', 'position' => 1]);
        Party::create(['name' => 'Party C', 'position' => 3]);

        $response = $this->get(route('party-planner.index'));
        $response->assertOk();

        $parties = $response->viewData('parties');
        $this->assertSame(
            ['Party B', 'Party A', 'Party C'],
            $parties->pluck('name')->all()
        );
    }
}
