<?php

namespace App\Models;

use App\Services\GvgScoreCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GvgWeeklyStat extends Model
{
    protected $fillable = [
        'guild_member_id',
        'week_start_date',
        'kills',
        'deaths',
        'revives',
        'war_score',
    ];

    protected $casts = [
        'week_start_date' => 'date',
    ];

    public function guildMember(): BelongsTo
    {
        return $this->belongsTo(GuildMember::class);
    }

    public function calculatedScore(string $role = 'dps'): float
    {
        return app(GvgScoreCalculator::class)->calculate(
            $this->kills,
            $this->deaths,
            $this->revives,
            $this->war_score,
            $role
        );
    }

    public function calculatedScoreAuto(): float
    {
        $role = $this->guildMember?->role ?? 'dps';

        return $this->calculatedScore($role);
    }

    public function calculatedCombatPower(): float
    {
        if ($this->deaths === 0) {
            return 0.0;
        }

        $base = ($this->kills * 4) + ($this->revives * 3);
        $multiplier = 1 + (($this->war_score - 1500) / 3000);
        $result = ($base * $multiplier) / ($this->deaths + 1);

        return (float) $result;
    }
}
