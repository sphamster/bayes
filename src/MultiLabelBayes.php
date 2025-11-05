<?php

declare(strict_types=1);

namespace Sphamster;

use Sphamster\Contracts\PredictionFilter;
use Sphamster\Support\Probability;

class MultiLabelBayes extends Bayes
{
    /**
     * Trains the classifier on a single text sample with multiple category labels.
     *
     * Tokenizes the sample once and applies the token frequencies to all
     * specified categories. Increments total document count only once per sample.
     *
     * @param string $sample The text content to train on
     * @param array<string> $labels Array of category labels for this sample
     * @return static Returns $this for method chaining
     */
    public function train(string $sample, array $labels): static
    {
        // Increment totalDocuments only once per sample
        $this->state->incrementTotalDocuments();

        // Tokenize once using base class method
        $frequency_table = $this->createFrequencyTableFromSample($sample);

        // Apply to all labels
        foreach ($labels as $label) {
            $category = $this->state->category($label);
            $category->incrementDocCount();

            foreach ($frequency_table->frequencies() as $token => $frequency) {
                $this->vocabulary->add($token);
                $category->addWordFrequency($token, $frequency);
            }
        }

        return $this;
    }

    /**
     * Trains the model on an array of samples with multiple labels.
     *
     * Each sample must be an associative array with at least two keys:
     * - The sample key (default: 'sample') containing the text.
     * - The labels key (default: 'labels') containing an array of categories.
     *
     * @param  array<int, array<string, mixed>>  $data
     */
    public function trainOn(
        array $data,
        string $sample_key = 'sample',
        string $labels_key = 'labels'
    ): static {
        foreach ($data as $sample) {
            $sample_value = isset($sample[$sample_key]) && is_string($sample[$sample_key]) ? $sample[$sample_key] : '';
            $labels_value = isset($sample[$labels_key]) && is_array($sample[$labels_key]) ? $sample[$labels_key] : [];

            // Filter to ensure only strings are passed
            $labels_value = array_filter($labels_value, 'is_string');

            $this->train($sample_value, $labels_value);
        }

        return $this;
    }

    /**
     * Predicts categories for the given text using a filtering strategy.
     *
     * Calculates probabilities for all categories and applies the provided
     * filter to determine which categories to return (e.g., threshold-based,
     * top-K, above-mean).
     *
     * @param string $text The text to classify
     * @param PredictionFilter $filter The filtering strategy to apply
     * @return array<Probability> Filtered array of Probability objects
     */
    public function predict(string $text, PredictionFilter $filter): array
    {
        $probabilities = $this->probabilities($text);

        return $filter->filter($probabilities);
    }
}
