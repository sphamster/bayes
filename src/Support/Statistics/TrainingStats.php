<?php

declare(strict_types=1);

namespace Sphamster\Support\Statistics;

use Sphamster\Support\Classification\State;
use Sphamster\Support\Vocabularies\Vocabulary;

/**
 * Provides comprehensive statistical information about classifier training data.
 *
 * This value object analyzes the training state and provides metrics including:
 * - Global statistics (total documents, vocabulary size, category count)
 * - Class balance metrics (balance ratio, imbalance detection)
 * - Token frequency analysis (most common tokens across all categories)
 * - Per-category statistics (via CategoryStats instances)
 * - Human-readable text reports
 */
class TrainingStats
{
    /**
     * Creates a new training statistics instance.
     *
     * @param State $state The training state to analyze
     * @param Vocabulary $vocabulary The vocabulary to analyze
     */
    public function __construct(
        protected readonly State $state,
        protected readonly Vocabulary $vocabulary
    ) {
    }

    /**
     * Returns the total number of documents trained across all categories.
     *
     * @return int The total document count
     */
    public function totalDocuments(): int
    {
        $total_documents = $this->state->totalDocuments();
        assert(is_int($total_documents));

        return $total_documents;
    }

    /**
     * Returns the size of the vocabulary (number of unique tokens).
     *
     * @return int The vocabulary size
     */
    public function vocabularySize(): int
    {
        return $this->vocabulary->size();
    }

    /**
     * Returns the number of categories in the training data.
     *
     * @return int The category count
     */
    public function numCategories(): int
    {
        return count($this->state->categories());
    }

    /**
     * Calculates the class balance ratio.
     *
     * Returns the ratio of the largest category to the smallest category
     * by document count. A ratio of 1.0 indicates perfect balance.
     * Returns INF if the minimum category has 0 documents.
     * Returns 0.0 if there are no categories.
     *
     * @return float The balance ratio (max/min document counts)
     */
    public function classBalanceRatio(): float
    {
        $categories = $this->state->categories();

        if ($categories === []) {
            return 0.0;
        }

        $doc_counts = [];
        foreach ($categories as $category) {
            $doc_count = $category->docCount();
            assert(is_int($doc_count));
            $doc_counts[] = $doc_count;
        }

        $max = max($doc_counts);
        $min = min($doc_counts);

        if ($min === 0) {
            return INF;
        }

        return $max / $min;
    }

    /**
     * Determines if the classes are balanced within a given threshold.
     *
     * Returns true if the class balance ratio is less than or equal to
     * the threshold. A threshold of 2.0 means the largest category can
     * have at most twice as many documents as the smallest category.
     *
     * @param float $threshold The maximum acceptable balance ratio (default: 2.0)
     * @return bool True if classes are balanced, false otherwise
     */
    public function isBalanced(float $threshold = 2.0): bool
    {
        $ratio = $this->classBalanceRatio();

        // Consider empty state as balanced
        if ($ratio === 0.0) {
            return true;
        }

        return $ratio <= $threshold;
    }

    /**
     * Returns the most common tokens across all categories.
     *
     * Aggregates token frequencies from all categories and returns the
     * top tokens sorted by total frequency in descending order.
     *
     * @param int $limit The maximum number of tokens to return (default: 10)
     * @return array<string, int> Map of tokens to their total frequencies, sorted by frequency descending
     */
    public function mostCommonTokens(int $limit = 10): array
    {
        $aggregated_frequencies = [];

        foreach ($this->state->categories() as $category) {
            $word_frequencies = $category->wordFrequencyCount();
            assert(is_array($word_frequencies));

            foreach ($word_frequencies as $token => $frequency) {
                if ( ! isset($aggregated_frequencies[$token])) {
                    $aggregated_frequencies[$token] = 0;
                }
                $aggregated_frequencies[$token] += $frequency;
            }
        }

        if ($aggregated_frequencies === []) {
            return [];
        }

        // Sort by frequency descending, preserve keys
        arsort($aggregated_frequencies);

        // Return top N tokens
        return array_slice($aggregated_frequencies, 0, $limit, true);
    }

    /**
     * Returns statistics for a specific category.
     *
     * @param string $category_name The name of the category
     * @return CategoryStats The category statistics instance
     */
    public function categoryStats(string $category_name): CategoryStats
    {
        $category = $this->state->category($category_name);
        return new CategoryStats($category_name, $category, $this->totalDocuments());
    }

    /**
     * Returns statistics for all categories.
     *
     * @return array<int, CategoryStats> Array of CategoryStats instances
     */
    public function allCategoryStats(): array
    {
        $all_stats = [];
        $total_documents = $this->totalDocuments();

        foreach ($this->state->categories() as $category_name => $category) {
            $all_stats[] = new CategoryStats($category_name, $category, $total_documents);
        }

        return $all_stats;
    }

    /**
     * Generates a human-readable text report of training statistics.
     *
     * Returns a formatted multi-line string containing:
     * - Global statistics (documents, vocabulary, categories, balance)
     * - Per-category statistics (document count, percentage, average length)
     * - Most common tokens across all categories
     *
     * @return string The formatted text report
     */
    public function toText(): string
    {
        $lines = [];
        $lines[] = 'Training Statistics';
        $lines[] = str_repeat('=', 50);
        $lines[] = sprintf('Total Documents: %d', $this->totalDocuments());
        $lines[] = sprintf('Vocabulary Size: %d', $this->vocabularySize());
        $lines[] = sprintf('Number of Categories: %d', $this->numCategories());
        $lines[] = sprintf('Class Balance Ratio: %.2f', $this->classBalanceRatio());
        $lines[] = sprintf('Is Balanced: %s', $this->isBalanced() ? 'Yes' : 'No');
        $lines[] = '';

        // Category details
        if ($this->numCategories() > 0) {
            $lines[] = 'Categories:';
            $lines[] = str_repeat('-', 50);

            foreach ($this->allCategoryStats() as $category_stats) {
                $lines[] = sprintf(
                    '  %s (%.1f%%)',
                    $category_stats->name(),
                    $category_stats->percentage()
                );
                $lines[] = sprintf('    Documents: %d', $category_stats->docCount());
                $lines[] = sprintf('    Average Length: %.2f words', $category_stats->averageDocLength());
                $lines[] = '';
            }
        }

        // Most common tokens
        $most_common = $this->mostCommonTokens(10);
        if ($most_common !== []) {
            $lines[] = 'Most Common Tokens:';
            $lines[] = str_repeat('-', 50);

            $rank = 1;
            foreach ($most_common as $token => $frequency) {
                $lines[] = sprintf('  %d. %s: %d', $rank++, $token, $frequency);
            }
        }

        return implode("\n", $lines);
    }
}
