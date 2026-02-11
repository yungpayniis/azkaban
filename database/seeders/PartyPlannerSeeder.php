<?php

namespace Database\Seeders;

use App\Models\GuildMember;
use App\Models\Party;
use App\Models\PartySlot;
use Illuminate\Database\Seeder;

class PartyPlannerSeeder extends Seeder
{
    public function run(): void
    {
        $members = [];
        for ($i = 1; $i <= 90; $i++) {
            $members[] = GuildMember::create([
                'name' => 'Member ' . $i,
                'nationality' => $i % 5 === 0 ? 'foreign' : 'thai',
            ]);
        }

        $memberIndex = 0;
        for ($partyNumber = 1; $partyNumber <= 6; $partyNumber++) {
            $party = Party::create([
                'name' => 'Party ' . $partyNumber,
            ]);

            for ($position = 1; $position <= 15; $position++) {
                $memberId = $memberIndex < count($members) ? $members[$memberIndex]->id : null;
                $memberIndex++;

                PartySlot::create([
                    'party_id' => $party->id,
                    'position' => $position,
                    'member_id' => $memberId,
                ]);
            }
        }
    }
}
