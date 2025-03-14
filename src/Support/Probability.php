<?php

declare(strict_types=1);

namespace Sphamster\Support;

class Probability
{
    public function __construct(
        protected readonly string $category,
        protected float $log_probability
    ) {
    }

    public function category(): string
    {
        return $this->category;
    }

    public function log(): float
    {
        return $this->log_probability;
    }

    public function decimal(): float
    {
        return exp($this->log_probability);
    }

    public function fromDecimal(float $decimal): static
    {
        $this->log_probability = log($decimal);

        return $this;
    }
}
