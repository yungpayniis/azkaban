<?php

namespace App\Services;

use App\Models\GuildMember;

class MemberColorService
{
    private const LINE_COLORS = [
        'Swordman Line' => '#C62828',
        'Dragon Knight' => '#C62828',
        'Imperial Guard' => '#C62828',
        'Mage Line' => '#1E5AA8',
        'Arch Mage' => '#1E5AA8',
        'Elemental Master' => '#1E5AA8',
        'Archer Line' => '#2E7D32',
        'Wind Hawk' => '#2E7D32',
        'Troubadour' => '#2E7D32',
        'Trouvere' => '#2E7D32',
        'Thief Line' => '#6A1B9A',
        'Shadow Cross' => '#6A1B9A',
        'Abyss Chaser' => '#6A1B9A',
        'Acolyte Line' => '#FBC02D',
        'Cardinal' => '#FBC02D',
        'Inquisitor' => '#FBC02D',
        'Merchant Line' => '#EF6C00',
        'Meister' => '#EF6C00',
        'Biolo' => '#EF6C00',
    ];

    public static function colorFor(GuildMember $member): ?string
    {
        $jobClass = $member->jobClass;
        if ($jobClass?->color) {
            return $jobClass->color;
        }

        $jobLine = $jobClass?->name;
        return self::LINE_COLORS[$jobLine] ?? null;
    }

    public static function textColorFor(GuildMember $member): string
    {
        return $member->jobClass?->force_dark_text ? '#0f172a' : '#fff';
    }
}
