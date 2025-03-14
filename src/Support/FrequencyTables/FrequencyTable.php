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

    public function add(string $token, int $count = 1): void
    {
        $this->frequencies[$token] = ($this->frequencies[$token] ?? 0) + $count;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function addTokens(array $tokens): void
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    public function frequency(string $token): int
    {
        return $this->frequencies[$token] ?? 0;
    }

    /**
     * @return array<string, int>
     */
    public function frequencies(): array
    {
        return $this->frequencies;
    }

    /**
     * Sum of all frequences (number of added token)
     */
    public function totalCount(): int
    {
        return array_sum($this->frequencies);
    }
}
