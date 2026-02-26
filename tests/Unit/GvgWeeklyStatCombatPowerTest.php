<?php

namespace Tests\Unit;

use App\Models\GvgWeeklyStat;
use PHPUnit\Framework\TestCase;

class GvgWeeklyStatCombatPowerTest extends TestCase
{
    public function test_combat_power_is_calculated_when_deaths_are_zero(): void
    {
        $stat = new GvgWeeklyStat([
            'kills' => 10,
            'deaths' => 0,
            'revives' => 2,
            'war_score' => 1500,
        ]);

        $this->assertSame(46.0, $stat->calculatedCombatPower());
    }
}
