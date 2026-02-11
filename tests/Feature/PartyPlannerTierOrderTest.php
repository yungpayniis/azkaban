<?php

namespace Tests\Feature;

use App\Models\GuildMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartyPlannerTierOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_members_are_sorted_by_tier_then_name(): void
    {
        GuildMember::create([
            'name' => 'Middle Z',
            'job_class_id' => null,
            'tier' => 'middle',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        GuildMember::create([
            'name' => 'Top B',
            'job_class_id' => null,
            'tier' => 'top',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        GuildMember::create([
            'name' => 'Low A',
            'job_class_id' => null,
            'tier' => 'low',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        GuildMember::create([
            'name' => 'Top A',
            'job_class_id' => null,
            'tier' => 'top',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $response = $this->get(route('party-planner.index'));
        $response->assertOk();

        $members = $response->viewData('unassignedMembers');

        $this->assertSame(
            ['top', 'top', 'middle', 'low'],
            $members->pluck('tier')->all()
        );
        $this->assertSame(
            ['Top A', 'Top B', 'Middle Z', 'Low A'],
            $members->pluck('name')->all()
        );
    }
}
