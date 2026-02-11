<?php

namespace Database\Seeders;

use App\Models\KvmParty;
use App\Models\KvmPartySlot;
use App\Models\GuildMember;
use Illuminate\Database\Seeder;

class KvmPartyPlannerSeeder extends Seeder
{
    public function run(): void
    {
        $members = GuildMember::orderBy('id')->take(30)->get();
        $memberIndex = 0;

        for ($partyNumber = 1; $partyNumber <= 6; $partyNumber++) {
            $party = KvmParty::create([
                'name' => 'KVM Party ' . $partyNumber,
            ]);

            for ($position = 1; $position <= 5; $position++) {
                $member = $memberIndex < $members->count() ? $members[$memberIndex] : null;
                $memberIndex++;

                KvmPartySlot::create([
                    'kvm_party_id' => $party->id,
                    'position' => $position,
                    'member_id' => $member?->id,
                ]);
            }
        }
    }
}
