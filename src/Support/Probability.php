<?php

declare(strict_types=1);

namespace Sphamster\Support;

class Probability
{
    /**
     * Creates a new Probability instance.
     *
     * @param string $category The category name
     * @param float $log_probability The log probability value
     */
    public function __construct(
        protected readonly string $category,
        protected readonly float $log_probability
    ) {
    }

    /**
     * Returns the category name for this probability.
     *
     * @return string The category name
     */
    public function category(): string
    {
        return $this->category;
    }

    /**
     * Returns the log probability value.
     *
     * @return float The log probability
     */
    public function log(): float
    {
        return $this->log_probability;
    }

    /**
     * Converts the log probability to a decimal probability.
     *
     * @return float The decimal probability value between 0 and 1
     */
    public function decimal(): float
    {
        return exp($this->log_probability);
    }

    /**
     * Creates a new Probability instance from a decimal value.
     *
     * Converts the decimal probability to log space and returns a new instance.
     *
     * @param string $category The category name
     * @param float $decimal The decimal probability value (0-1)
     * @return self A new Probability instance with the converted value
     */
    public static function fromDecimal(string $category, float $decimal): self
    {
        return new self($category, log($decimal));
    }
}
