<?php

declare(strict_types=1);

namespace Sphamster\Support\Filters;

use Sphamster\Contracts\PredictionFilter;
use Sphamster\Support\Probability;

class ThresholdFilter implements PredictionFilter
{
    public function __construct(
        private readonly float $threshold = 0.3
    ) {
    }

    /**
     * Filters probabilities that meet or exceed the threshold.
     *
     * @param  array<Probability>  $probabilities
     * @return array<Probability>
     */
    public function filter(array $probabilities): array
    {
        return array_values(array_filter(
            $probabilities,
            fn (Probability $probability): bool => $probability->decimal() >= $this->threshold
        ));
    }
}
