<?php

declare(strict_types=1);

namespace Sphamster;

use Sphamster\Concerns\WithState;
use Sphamster\Contracts\Exportable;
use Sphamster\Contracts\Importable;
use Sphamster\Contracts\Tokenizer;
use Sphamster\Support\Classification\State;
use Sphamster\Support\FrequencyTables\FrequencyTable;
use Sphamster\Support\Probability;
use Sphamster\Support\Statistics\TrainingStats;
use Sphamster\Support\Tokenizers\DefaultTokenizer;
use Sphamster\Support\Vocabularies\Vocabulary;

abstract class Bayes implements Exportable, Importable
{
    use WithState;

    protected readonly Tokenizer $tokenizer;

    /**
     * Creates a new classifier instance.
     *
     * @param Tokenizer|null $tokenizer The tokenizer to use for text processing (default: DefaultTokenizer)
     * @param State $state The initial state (default: new empty State)
     * @param Vocabulary $vocabulary The initial vocabulary (default: new empty Vocabulary)
     */
    public function __construct(
        ?Tokenizer $tokenizer = null,
        protected State      $state = new State(),
        protected Vocabulary $vocabulary = new Vocabulary()
    ) {
        $this->tokenizer = $tokenizer ?? new DefaultTokenizer();
    }

    /**
     * Returns the tokenizer instance used by this classifier.
     *
     * @return Tokenizer The tokenizer instance
     */
    public function tokenizer(): Tokenizer
    {
        return $this->tokenizer;
    }

    /**
     * Calculates normalized probabilities for all categories.
     *
     * Uses log-sum-exp trick for numerical stability and Laplace smoothing
     * for unseen tokens. Returns empty array if no training data exists.
     *
     * @param string $sample The text to calculate probabilities for
     * @return array<Probability> Array of Probability objects, one per category
     */
    public function probabilities(string $sample): array
    {
        // Return empty array if no categories have been trained
        if ($this->state->totalDocuments() === 0) {
            return [];
        }

        $probabilities = [];
        $tokens = $this->tokenizer->tokenize($sample);

        $frequency_table = new FrequencyTable();
        $frequency_table->addTokens($tokens);
        $frequencies = $frequency_table->frequencies();

        // Calculate unnormalized log probabilities
        $log_probabilities = [];
        foreach ($this->state->categories() as $category_key => $category) {
            $doc_count = $category->docCount();
            $total_documents = $this->state->totalDocuments();
            assert(is_int($doc_count) && is_int($total_documents));
            $log_probability = log($doc_count / $total_documents);

            foreach ($frequencies as $token => $frequency) {
                $token_probability = $this->tokenProbability($token, $category_key);
                $log_probability += $frequency * log($token_probability);
            }

            $log_probabilities[$category_key] = $log_probability;
        }

        // If no log probabilities were calculated, return empty array
        if ($log_probabilities === []) {
            return [];
        }

        // Normalize probabilities using log-sum-exp trick for numerical stability
        // log P(C|X) = log P(C,X) - log(Σ exp(log P(C',X)))
        $max_log_probability = max($log_probabilities);
        $sum_exp = 0.0;
        foreach ($log_probabilities as $log_probability) {
            $sum_exp += exp($log_probability - $max_log_probability);
        }
        $log_sum_exp = $max_log_probability + log($sum_exp);

        // Create normalized Probability objects
        foreach ($log_probabilities as $category_key => $log_probability) {
            $normalized_log_probability = $log_probability - $log_sum_exp;
            $probabilities[] = new Probability($category_key, $normalized_log_probability);
        }

        return $probabilities;
    }

    /**
     * Calculates the probability of a token appearing in a given category.
     *
     * Uses Laplace smoothing (add-one smoothing) to handle tokens not seen
     * during training, preventing zero probabilities.
     *
     * @param string $token The token to calculate probability for
     * @param string $category_name The category name to calculate within
     * @return float The smoothed probability value between 0 and 1
     */
    private function tokenProbability(string $token, string $category_name): float
    {
        $category = $this->state->category($category_name);
        /** @var array<string, int> $word_frequency_count */
        $word_frequency_count = $category->wordFrequencyCount();
        $token_frequency = $word_frequency_count[$token] ?? 0;
        /** @var int $word_count */
        $word_count = $category->wordCount();

        return ($token_frequency + 1) / ($word_count + $this->vocabulary->size());
    }

    /**
     * Creates a frequency table from a text sample.
     *
     * Tokenizes the sample using the configured tokenizer and builds
     * a frequency table counting token occurrences.
     *
     * @param string $sample The text to tokenize and analyze
     * @return FrequencyTable The frequency table containing token counts
     */
    protected function createFrequencyTableFromSample(string $sample): FrequencyTable
    {
        $tokens = $this->tokenizer->tokenize($sample);
        $frequency_table = new FrequencyTable();
        $frequency_table->addTokens($tokens);

        return $frequency_table;
    }

    /**
     * Returns comprehensive training statistics for this classifier.
     *
     * Provides detailed information about the training data including:
     * - Global metrics (total documents, vocabulary size, categories)
     * - Class balance analysis
     * - Most common tokens across all categories
     * - Per-category statistics
     *
     * @return TrainingStats The training statistics instance
     */
    public function getTrainingStats(): TrainingStats
    {
        return new TrainingStats($this->state, $this->vocabulary);
    }

    /**
     * Returns the most frequent tokens in a specific category.
     *
     * This is a convenience method that provides quick access to top tokens
     * for a single category without needing to create a full TrainingStats instance.
     *
     * @param string $category The category name to analyze
     * @param int $limit The maximum number of tokens to return (default: 10)
     * @return array<string, int> Map of tokens to their frequencies, sorted by frequency descending
     */
    public function getTopTokens(string $category, int $limit = 10): array
    {
        $stats = $this->getTrainingStats();
        return $stats->categoryStats($category)->topTokens($limit);
    }

    /**
     * Returns the most common tokens across all categories.
     *
     * This is a convenience method that aggregates token frequencies from all
     * categories and returns the most frequently occurring tokens overall.
     *
     * @param int $limit The maximum number of tokens to return (default: 10)
     * @return array<string, int> Map of tokens to their total frequencies, sorted by frequency descending
     */
    public function getMostCommonTokens(int $limit = 10): array
    {
        $stats = $this->getTrainingStats();
        return $stats->mostCommonTokens($limit);
    }
}
