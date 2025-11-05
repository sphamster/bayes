<?php

declare(strict_types=1);

namespace Sphamster\Support\Filters;

use Sphamster\Contracts\PredictionFilter;
use Sphamster\Support\Probability;

class AboveMeanFilter implements PredictionFilter
{
    /**
     * Filters probabilities that are above the mean log probability.
     *
     * @param  array<Probability>  $probabilities
     * @return array<Probability>
     */
    public function filter(array $probabilities): array
    {
        if ($probabilities === []) {
            return [];
        }

        // Calculate mean of log probabilities
        $mean = array_sum(array_map(fn (Probability $p): float => $p->log(), $probabilities)) / count($probabilities);

        // Filter probabilities strictly above mean
        return array_values(array_filter($probabilities, fn (Probability $p): bool => $p->log() > $mean));
    }
}
