<?php

declare(strict_types=1);

namespace Sphamster\Concerns;

use JsonException;
use Sphamster\Decoders\JsonDecoder;
use Sphamster\Exception\JsonCorruptedException;

trait WithState
{
    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        $categories_state = array_map(
            fn ($category): array => $category->toArray(),
            $this->state->categories()
        );

        return [
            'categories' => array_keys($this->state->categories()),
            'totalDocuments' => $this->state->totalDocuments(),
            'vocabulary' => $this->vocabulary->tokens(),
            'vocabularySize' => $this->vocabulary->size(),
            'categoriesState' => $categories_state,
        ];
    }

    /**
     * @throws JsonException
     */
    public function export(): string
    {
        return json_encode($this->getState(), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonCorruptedException
     */
    public function import(string $content): static
    {
        $decoder = (new JsonDecoder())->fromBayes($this);

        try {
            /** @var array<string, mixed> $decoded_content */
            $decoded_content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonCorruptedException(previous: $e);
        }

        $decoder->decode($decoded_content);

        return $this;
    }

    /**
     * Returns the current classification state.
     *
     * @return \Sphamster\Support\Classification\State The state instance containing categories and document counts
     */
    public function state(): \Sphamster\Support\Classification\State
    {
        return $this->state;
    }

    /**
     * Returns the vocabulary containing all unique tokens seen during training.
     *
     * @return \Sphamster\Support\Vocabularies\Vocabulary The vocabulary instance
     */
    public function vocabulary(): \Sphamster\Support\Vocabularies\Vocabulary
    {
        return $this->vocabulary;
    }

    /**
     * Resets the classifier to its initial state.
     *
     * Clears all training data including categories, document counts,
     * and vocabulary.
     *
     * @return static Returns $this for method chaining
     */
    public function reset(): static
    {
        $this->state = new \Sphamster\Support\Classification\State();
        $this->vocabulary = new \Sphamster\Support\Vocabularies\Vocabulary();

        return $this;
    }
}
