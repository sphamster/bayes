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
use Sphamster\Support\Vocabularies\Vocabulary;

class Bayes implements Exportable, Importable
{
    use WithState;

    public function __construct(
        protected Tokenizer  $tokenizer,
        protected State      $state = new State(),
        protected Vocabulary $vocabulary = new Vocabulary()
    ) {
    }

    public function tokenizer(): Tokenizer
    {
        return $this->tokenizer;
    }

    public function state(): State
    {
        return $this->state;
    }

    public function vocabulary(): Vocabulary
    {
        return $this->vocabulary;
    }

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
            $sampleValue = isset($sample[$sample_key]) && is_string($sample[$sample_key]) ? $sample[$sample_key] : '';
            $labelValue  = isset($sample[$label_key])  && is_string($sample[$label_key]) ? $sample[$label_key] : '';
            $this->train($sampleValue, $labelValue);
        }

        return $this;
    }

    public function train(string $sample, string $label): static
    {
        $this->state->incrementTotalDocuments();

        $category = $this->state->category($label);
        $category->incrementDocCount();

        $tokens = $this->tokenizer->tokenize($sample);
        $frequencyTable = new FrequencyTable();
        $frequencyTable->addTokens($tokens);

        foreach ($frequencyTable->frequencies() as $token => $frequency) {
            $this->vocabulary->add($token);
            $category->addWordFrequency($token, $frequency);
        }

        return $this;
    }

    public function predict(string $text): ?string
    {
        if ($this->state->totalDocuments() === 0) {
            return null;
        }

        $probabilities = $this->probabilities($text);
        $chosen = null;
        $maxLog = -INF;

        foreach ($probabilities as $probability) {
            if ($probability->log() > $maxLog) {
                $maxLog = $probability->log();
                $chosen = $probability->category();
            }
        }

        return $chosen;
    }

    /**
     * @return array<Probability>
     */
    public function probabilities(string $sample): array
    {
        $probabilities = [];
        $tokens = $this->tokenizer->tokenize($sample);

        $frequencyTable = new FrequencyTable();
        $frequencyTable->addTokens($tokens);
        $freqs = $frequencyTable->frequencies();

        foreach ($this->state->categories() as $category_key => $category) {
            $logProbability = log($category->docCount() / $this->state->totalDocuments());

            foreach ($freqs as $token => $frequency) {
                $tokenProb = $this->tokenProbability($token, $category_key);
                $logProbability += $frequency * log($tokenProb);
            }

            $probabilities[] = new Probability($category_key, $logProbability);
        }

        return $probabilities;
    }

    private function tokenProbability(string $token, string $category): float
    {
        $category = $this->state->category($category);
        $tokenFrequency = $category->getWordFrequencyCount()[$token] ?? 0;

        return ($tokenFrequency + 1) / ($category->getWordCount() + $this->vocabulary->size());
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        $categoriesState = array_map(
            fn ($data): array => [
                'docCount' => $data->docCount(),
                'wordCount' => $data->getWordCount(),
                'wordFrequencyCount' => $data->getWordFrequencyCount(),
            ],
            $this->state->categories()
        );

        return [
            'categories' => array_keys($this->state->categories()),
            'totalDocuments' => $this->state->totalDocuments(),
            'vocabulary' => $this->vocabulary->tokens(),
            'vocabularySize' => $this->vocabulary->size(),
            'categoriesState' => $categoriesState,
        ];
    }

    public function reset(): static
    {
        $this->state = new State();
        $this->vocabulary = new Vocabulary();

        return $this;
    }
}
