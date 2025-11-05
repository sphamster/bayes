<?php

declare(strict_types=1);

namespace Sphamster;

class SingleLabelBayes extends Bayes
{
    /**
     * Trains the model on an array of samples.
     *
     * Each sample must be an associative array with at least two keys:
     * - The sample key (default: 'sample') containing the text.
     * - The label key (default: 'label') containing the category.
     *
     * @param array<int, array<string, mixed>> $data
     */
    public function trainOn(
        array $data,
        string $sample_key = 'sample',
        string $label_key = 'label'
    ): static {
        foreach ($data as $sample) {
            $sample_value = isset($sample[$sample_key]) && is_string($sample[$sample_key]) ? $sample[$sample_key] : '';
            $label_value  = isset($sample[$label_key])  && is_string($sample[$label_key]) ? $sample[$label_key] : '';
            $this->train($sample_value, $label_value);
        }

        return $this;
    }

    /**
     * Trains the classifier on a single text sample with a category label.
     *
     * Tokenizes the sample, updates vocabulary, and adds word frequencies
     * to the specified category. Increments total document count.
     *
     * @param string $sample The text content to train on
     * @param string $label The category label for this sample
     * @return static Returns $this for method chaining
     */
    public function train(string $sample, string $label): static
    {
        $this->state->incrementTotalDocuments();

        $category = $this->state->category($label);
        $category->incrementDocCount();

        $frequency_table = $this->createFrequencyTableFromSample($sample);

        foreach ($frequency_table->frequencies() as $token => $frequency) {
            $this->vocabulary->add($token);
            $category->addWordFrequency($token, $frequency);
        }

        return $this;
    }

    /**
     * Predicts the most likely category for the given text.
     *
     * Returns null if no documents have been trained yet.
     *
     * @param string $text The text to classify
     * @return string|null The predicted category name, or null if no training data exists
     */
    public function predict(string $text): ?string
    {
        if ($this->state->totalDocuments() === 0) {
            return null;
        }

        $probabilities = $this->probabilities($text);
        $chosen = null;
        $max_log = -INF;

        foreach ($probabilities as $probability) {
            if ($probability->log() > $max_log) {
                $max_log = $probability->log();
                $chosen = $probability->category();
            }
        }

        return $chosen;
    }
}
