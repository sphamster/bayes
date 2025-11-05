<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

use Sphamster\Support\Probability;

interface PredictionFilter
{
    /**
     * Filters an array of probabilities based on the strategy.
     *
     * @param  array<Probability>  $probabilities
     * @return array<Probability>
     */
    public function filter(array $probabilities): array;
}
