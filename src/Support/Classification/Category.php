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

    /**
     * Increments the document count for this category by one.
     */
    public function incrementDocCount(): void
    {
        $this->doc_count++;
    }

    /**
     * Gets or sets the document count for this category.
     *
     * When called without parameters, returns the current document count.
     * When called with a parameter, sets the document count and returns $this for chaining.
     *
     * @param int|null $doc_count The document count to set (optional)
     * @return int|static The document count when getting, or $this when setting
     */
    public function docCount(?int $doc_count = null): int|static
    {
        if ($doc_count !== null) {
            $this->doc_count = $doc_count;
            return $this;
        }
        return $this->doc_count;
    }

    /**
     * Gets or sets the total word count for this category.
     *
     * When called without parameters, returns the current word count.
     * When called with a parameter, sets the word count and returns $this for chaining.
     *
     * @param int|null $word_count The word count to set (optional)
     * @return int|static The word count when getting, or $this when setting
     */
    public function wordCount(?int $word_count = null): int|static
    {
        if ($word_count !== null) {
            $this->word_count = $word_count;
            return $this;
        }
        return $this->word_count;
    }

    /**
     * Gets or sets the word frequency count array for this category.
     *
     * When called without parameters, returns the current word frequency count map.
     * When called with a parameter, sets the word frequency count and returns $this for chaining.
     *
     * @param array<string, int>|null $word_frequency_count Map of tokens to their frequencies (optional)
     * @return array<string, int>|static The word frequency count when getting, or $this when setting
     */
    public function wordFrequencyCount(?array $word_frequency_count = null): array|static
    {
        if ($word_frequency_count !== null) {
            $this->word_frequency_count = $word_frequency_count;
            return $this;
        }
        return $this->word_frequency_count;
    }

    /**
     * Adds or increments the frequency of a token in this category.
     *
     * If the token already exists, its count is incremented. Otherwise,
     * it is added with the given count. Updates the total word count.
     *
     * @param string $token The token to add or increment
     * @param int $count The frequency count to add
     */
    public function addWordFrequency(string $token, int $count): void
    {
        if ( ! isset($this->word_frequency_count[$token])) {
            $this->word_frequency_count[$token] = $count;
        } else {
            $this->word_frequency_count[$token] += $count;
        }
        $this->word_count += $count;
    }

    /**
     * Resets this category to its initial state.
     *
     * Clears all document counts, word counts, and word frequencies.
     */
    public function reset(): void
    {
        $this->doc_count = 0;
        $this->word_count = 0;
        $this->word_frequency_count = [];
    }

    /**
     * Exports the category state as an array.
     *
     * Returns an associative array representation suitable for serialization.
     *
     * @return array{docCount: int, wordCount: int, wordFrequencyCount: array<string, int>}
     */
    public function toArray(): array
    {
        return [
            'docCount' => $this->doc_count,
            'wordCount' => $this->word_count,
            'wordFrequencyCount' => $this->word_frequency_count,
        ];
    }
}
