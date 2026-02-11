<?php

namespace Database\Seeders;

use App\Models\JobClass;
use Illuminate\Database\Seeder;

class JobClassSeeder extends Seeder
{
    public function run(): void
    {
        $first = [
            'Swordman',
            'Mage',
            'Archer',
            'Thief',
            'Acolyte',
            'Merchant',
        ];

        $firstIds = [];
        foreach ($first as $name) {
            $firstIds[$name] = JobClass::create([
                'name' => $name,
                'tier' => 1,
                'parent_id' => null,
            ])->id;
        }

        $second = [
            'Swordman' => ['Knight', 'Crusader'],
            'Mage' => ['Wizard', 'Sage'],
            'Archer' => ['Hunter', 'Bard', 'Dancer'],
            'Thief' => ['Assassin', 'Rogue'],
            'Acolyte' => ['Priest', 'Monk'],
            'Merchant' => ['Blacksmith', 'Alchemist'],
        ];

        $secondIds = [];
        foreach ($second as $parent => $names) {
            foreach ($names as $name) {
                $secondIds[$name] = JobClass::create([
                    'name' => $name,
                    'tier' => 2,
                    'parent_id' => $firstIds[$parent],
                ])->id;
            }
        }

        $third = [
            'Knight' => ['Lord Knight'],
            'Crusader' => ['Paladin'],
            'Wizard' => ['High Wizard'],
            'Sage' => ['Professor'],
            'Hunter' => ['Sniper'],
            'Bard' => ['Minstrel'],
            'Dancer' => ['Gypsy'],
            'Assassin' => ['Assassin Cross'],
            'Rogue' => ['Stalker'],
            'Priest' => ['High Priest'],
            'Monk' => ['Champion'],
            'Blacksmith' => ['Whitesmith'],
            'Alchemist' => ['Creator'],
        ];

        $thirdIds = [];
        foreach ($third as $parent => $names) {
            foreach ($names as $name) {
                $thirdIds[$name] = JobClass::create([
                    'name' => $name,
                    'tier' => 3,
                    'parent_id' => $secondIds[$parent],
                ])->id;
            }
        }

        $fourth = [
            'Lord Knight' => ['Dragon Knight'],
            'Paladin' => ['Imperial Guard'],
            'High Wizard' => ['Arch Mage'],
            'Professor' => ['Elemental Master'],
            'Sniper' => ['Wind Hawk'],
            'Minstrel' => ['Troubadour'],
            'Gypsy' => ['Trouvere'],
            'Assassin Cross' => ['Shadow Cross'],
            'Stalker' => ['Abyss Chaser'],
            'High Priest' => ['Cardinal'],
            'Champion' => ['Inquisitor'],
            'Whitesmith' => ['Meister'],
            'Creator' => ['Biolo'],
        ];

        foreach ($fourth as $parent => $names) {
            foreach ($names as $name) {
                JobClass::create([
                    'name' => $name,
                    'tier' => 4,
                    'parent_id' => $thirdIds[$parent],
                ]);
            }
        }
    }
}
