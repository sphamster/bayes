<?php

declare(strict_types=1);

namespace Sphamster\Support\Filters;

use Sphamster\Contracts\PredictionFilter;
use Sphamster\Support\Probability;

class TopKFilter implements PredictionFilter
{
    public function __construct(
        private readonly int $k = 3
    ) {
    }

    /**
     * Returns the top K probabilities sorted by log probability descending.
     *
     * @param  array<Probability>  $probabilities
     * @return array<Probability>
     */
    public function filter(array $probabilities): array
    {
        if ($probabilities === [] || $this->k === 0) {
            return [];
        }

        // Sort by log probability descending
        usort($probabilities, fn (Probability $a, Probability $b): int => $b->log() <=> $a->log());

        // Return top k elements
        return array_slice($probabilities, 0, $this->k);
    }
}
