<?php

declare(strict_types=1);

namespace Sphamster\Support\Statistics;

use Sphamster\Support\Classification\Category;

/**
 * Provides statistical information about a single category.
 *
 * This value object wraps a Category instance and calculates various
 * metrics such as document percentage, average document length, and
 * top tokens by frequency.
 */
class CategoryStats
{
    /**
     * Creates a new category statistics instance.
     *
     * @param string $category_name The name of the category
     * @param Category $category The category instance to analyze
     * @param int $total_documents The total number of documents across all categories
     */
    public function __construct(
        protected readonly string $category_name,
        protected readonly Category $category,
        protected readonly int $total_documents
    ) {
    }

    /**
     * Returns the category name.
     *
     * @return string The category name
     */
    public function name(): string
    {
        return $this->category_name;
    }

    /**
     * Returns the number of documents in this category.
     *
     * @return int The document count
     */
    public function docCount(): int
    {
        $doc_count = $this->category->docCount();
        assert(is_int($doc_count));

        return $doc_count;
    }

    /**
     * Returns the total number of words in this category.
     *
     * @return int The word count
     */
    public function wordCount(): int
    {
        $word_count = $this->category->wordCount();
        assert(is_int($word_count));

        return $word_count;
    }

    /**
     * Calculates the percentage of documents in this category.
     *
     * Returns the percentage of total documents that belong to this category.
     * If total documents is zero, returns 0.0.
     *
     * @return float The percentage (0.0 to 100.0)
     */
    public function percentage(): float
    {
        if ($this->total_documents === 0) {
            return 0.0;
        }

        $doc_count = $this->category->docCount();
        assert(is_int($doc_count));

        return ($doc_count / $this->total_documents) * 100.0;
    }

    /**
     * Calculates the average document length in words.
     *
     * Returns the average number of words per document in this category.
     * If document count is zero, returns 0.0.
     *
     * @return float The average document length
     */
    public function averageDocLength(): float
    {
        $doc_count = $this->category->docCount();
        assert(is_int($doc_count));

        if ($doc_count === 0) {
            return 0.0;
        }

        $word_count = $this->category->wordCount();
        assert(is_int($word_count));

        return $word_count / $doc_count;
    }

    /**
     * Returns the most frequent tokens in this category.
     *
     * Returns an array of tokens sorted by frequency in descending order,
     * limited to the specified number of tokens.
     *
     * @param int $limit The maximum number of tokens to return (default: 10)
     * @return array<string, int> Map of tokens to their frequencies, sorted by frequency descending
     */
    public function topTokens(int $limit = 10): array
    {
        $word_frequencies = $this->category->wordFrequencyCount();
        assert(is_array($word_frequencies));

        if ($word_frequencies === []) {
            return [];
        }

        // Sort by frequency descending, preserve keys
        arsort($word_frequencies);

        // Return top N tokens
        return array_slice($word_frequencies, 0, $limit, true);
    }

    /**
     * Returns the number of unique tokens in this category.
     *
     * @return int The count of unique tokens
     */
    public function uniqueTokenCount(): int
    {
        $word_frequencies = $this->category->wordFrequencyCount();
        assert(is_array($word_frequencies));

        return count($word_frequencies);
    }
}
