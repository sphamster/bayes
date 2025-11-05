<?php

declare(strict_types=1);

namespace Sphamster\Support\FrequencyTables;

class FrequencyTable
{
    /**
     * key: token, value: token frequency
     *
     * @var array<string, int>
     */
    protected array $frequencies = [];

    /**
     * Adds or increments a token's frequency count.
     *
     * @param string $token The token to add
     * @param int $count The count to add (default: 1)
     */
    public function add(string $token, int $count = 1): void
    {
        $this->frequencies[$token] = ($this->frequencies[$token] ?? 0) + $count;
    }

    /**
     * Adds multiple tokens to the frequency table.
     *
     * Each token's count is incremented by 1.
     *
     * @param array<int, string> $tokens Array of tokens to add
     */
    public function addTokens(array $tokens): void
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    /**
     * Returns the frequency count for a specific token.
     *
     * @param string $token The token to query
     * @return int The frequency count, or 0 if token not found
     */
    public function frequency(string $token): int
    {
        return $this->frequencies[$token] ?? 0;
    }

    /**
     * Returns all token frequencies as a map.
     *
     * @return array<string, int> Map of tokens to their frequency counts
     */
    public function frequencies(): array
    {
        return $this->frequencies;
    }

    /**
     * Returns the sum of all frequencies (total number of tokens added).
     *
     * @return int The total count of all tokens
     */
    public function totalCount(): int
    {
        return array_sum($this->frequencies);
    }
}
