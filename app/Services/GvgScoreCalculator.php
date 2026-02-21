<?php

namespace App\Services;

class GvgScoreCalculator
{
    public function calculate(
        int $kills,
        int $deaths,
        int $revives,
        int $warScore,
        string $role = 'dps'
    ): float {
        $weights = config('gvg.weights');
        $roleWeights = $weights[$role] ?? $weights['dps'];
        $warScoreDivisor = max((float) config('gvg.war_score_divisor', 100), 1.0);
        $normalizedWarScore = $warScore / $warScoreDivisor;

        $score = ($kills * $roleWeights['kills'])
            + ($deaths * $roleWeights['deaths'])
            + ($revives * $roleWeights['revives'])
            + ($normalizedWarScore * $roleWeights['war_score']);

        return max((float) config('gvg.min_score', 0), $score);
    }
}
