<?php

declare(strict_types=1);

namespace Sphamster\Support\Classification;

class Category
{
    protected int $doc_count = 0;

    protected int $word_count = 0;

    /**
     * @var array<string, int>
     */
    private array $word_frequency_count = [];

    public function incrementDocCount(): void
    {
        $this->doc_count++;
    }

    public function docCount(): int
    {
        return $this->doc_count;
    }

    public function setDocCount(int $docCount): void
    {
        $this->doc_count = $docCount;
    }

    public function setWordCount(int $word_count): void
    {
        $this->word_count = $word_count;
    }

    /**
     * @param array<string, int> $word_frequency_count
     */
    public function setWordFrequencyCount(array $word_frequency_count): void
    {
        $this->word_frequency_count = $word_frequency_count;
    }

    public function addWordFrequency(string $token, int $count): void
    {
        if ( ! isset($this->word_frequency_count[$token])) {
            $this->word_frequency_count[$token] = $count;
        } else {
            $this->word_frequency_count[$token] += $count;
        }
        $this->word_count += $count;
    }

    public function getWordCount(): int
    {
        return $this->word_count;
    }

    /**
     * @return array<string, int>
     */
    public function getWordFrequencyCount(): array
    {
        return $this->word_frequency_count;
    }

    public function reset(): void
    {
        $this->doc_count = 0;
        $this->word_count = 0;
        $this->word_frequency_count = [];
    }
}
