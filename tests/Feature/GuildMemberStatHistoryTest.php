<?php

namespace Tests\Feature;

use App\Models\GuildMember;
use App\Models\GuildMemberStat;
use App\Models\GuildMemberStatHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuildMemberStatHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_does_not_create_stat_history_when_stats_are_unchanged(): void
    {
        $member = GuildMember::create([
            'name' => 'Alice',
            'job_class_id' => null,
            'tier' => 'low',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $stats = [
            'str' => 10,
            'vit' => 11,
            'luk' => 12,
            'agi' => 13,
            'dex' => 14,
            'int' => 15,
            'hp' => 100,
            'sp' => 50,
            'patk' => 200,
            'matk' => 180,
            'pdef' => 90,
            'mdef' => 80,
        ];

        GuildMemberStat::create(array_merge(['guild_member_id' => $member->id], $stats));
        GuildMemberStatHistory::create(array_merge(['guild_member_id' => $member->id], $stats));

        $this->assertEquals(1, GuildMemberStatHistory::where('guild_member_id', $member->id)->count());

        $payload = [
            'name' => 'Alice Renamed',
            'job_class_id' => null,
            'tier' => 'low',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now()->format('Y-m-d H:i:s'),
            'stats' => $stats,
        ];

        $this->put(route('guild-members.update', $member), $payload)
            ->assertRedirect(route('guild-members.index'));

        $this->assertEquals(1, GuildMemberStatHistory::where('guild_member_id', $member->id)->count());
    }

    public function test_update_creates_stat_history_when_stats_change(): void
    {
        $member = GuildMember::create([
            'name' => 'Bob',
            'job_class_id' => null,
            'tier' => 'low',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $stats = [
            'str' => 10,
            'vit' => 11,
            'luk' => 12,
            'agi' => 13,
            'dex' => 14,
            'int' => 15,
            'hp' => 100,
            'sp' => 50,
            'patk' => 200,
            'matk' => 180,
            'pdef' => 90,
            'mdef' => 80,
        ];

        GuildMemberStat::create(array_merge(['guild_member_id' => $member->id], $stats));
        GuildMemberStatHistory::create(array_merge(['guild_member_id' => $member->id], $stats));

        $changedStats = $stats;
        $changedStats['str'] = 20;

        $payload = [
            'name' => 'Bob',
            'job_class_id' => null,
            'tier' => 'low',
            'role' => 'dps',
            'nationality' => 'thai',
            'status' => GuildMember::STATUS_ACTIVE,
            'joined_at' => now()->format('Y-m-d H:i:s'),
            'stats' => $changedStats,
        ];

        $this->put(route('guild-members.update', $member), $payload)
            ->assertRedirect(route('guild-members.index'));

        $this->assertEquals(2, GuildMemberStatHistory::where('guild_member_id', $member->id)->count());
        $this->assertDatabaseHas('guild_member_stat_histories', [
            'guild_member_id' => $member->id,
            'str' => 20,
        ]);
    }
}
