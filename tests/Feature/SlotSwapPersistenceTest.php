<?php

namespace Tests\Feature;

use App\Models\GuildMember;
use App\Models\KvmParty;
use App\Models\KvmPartySlot;
use App\Models\Party;
use App\Models\PartySlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotSwapPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_party_planner_swap_updates_are_persisted(): void
    {
        [$memberA, $memberB] = $this->createMembers();

        $partyA = Party::create(['name' => 'Party A']);
        $partyB = Party::create(['name' => 'Party B']);

        $slotA = PartySlot::create([
            'party_id' => $partyA->id,
            'position' => 1,
            'member_id' => $memberA->id,
        ]);
        $slotB = PartySlot::create([
            'party_id' => $partyB->id,
            'position' => 1,
            'member_id' => $memberB->id,
        ]);

        $response = $this->postJson(route('party-planner.slots.update'), [
            'updates' => [
                ['slot_id' => $slotA->id, 'member_id' => $memberB->id],
                ['slot_id' => $slotB->id, 'member_id' => $memberA->id],
            ],
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('party_slots', ['id' => $slotA->id, 'member_id' => $memberB->id]);
        $this->assertDatabaseHas('party_slots', ['id' => $slotB->id, 'member_id' => $memberA->id]);
    }

    public function test_kvm_planner_swap_updates_are_persisted(): void
    {
        [$memberA, $memberB] = $this->createMembers();

        $partyA = KvmParty::create(['name' => 'KVM A']);
        $partyB = KvmParty::create(['name' => 'KVM B']);

        $slotA = KvmPartySlot::create([
            'kvm_party_id' => $partyA->id,
            'position' => 1,
            'member_id' => $memberA->id,
        ]);
        $slotB = KvmPartySlot::create([
            'kvm_party_id' => $partyB->id,
            'position' => 1,
            'member_id' => $memberB->id,
        ]);

        $response = $this->postJson(route('kvm-planner.slots.update'), [
            'updates' => [
                ['slot_id' => $slotA->id, 'member_id' => $memberB->id],
                ['slot_id' => $slotB->id, 'member_id' => $memberA->id],
            ],
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('kvm_party_slots', ['id' => $slotA->id, 'member_id' => $memberB->id]);
        $this->assertDatabaseHas('kvm_party_slots', ['id' => $slotB->id, 'member_id' => $memberA->id]);
    }

    private function createMembers(): array
    {
        return [
            GuildMember::create([
                'name' => 'Member A',
                'job_class_id' => null,
                'tier' => 'top',
                'role' => 'dps',
                'nationality' => 'thai',
                'status' => GuildMember::STATUS_ACTIVE,
                'joined_at' => now(),
            ]),
            GuildMember::create([
                'name' => 'Member B',
                'job_class_id' => null,
                'tier' => 'middle',
                'role' => 'support',
                'nationality' => 'thai',
                'status' => GuildMember::STATUS_ACTIVE,
                'joined_at' => now(),
            ]),
        ];
    }
}
