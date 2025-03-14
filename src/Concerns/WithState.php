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
        $categoriesState = array_map(fn ($data): array => [
            'docCount' => $data->docCount(),
            'wordCount' => $data->getWordCount(),
            'wordFrequencyCount' => $data->getWordFrequencyCount(),
        ], $this->state->categories());

        return [
            'categories' => array_keys($this->state->categories()),
            'totalDocuments' => $this->state->totalDocuments(),
            'vocabulary' => $this->vocabulary->tokens(),
            'vocabularySize' => $this->vocabulary->size(),
            'categoriesState' => $categoriesState,
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

        /** @var array<string, mixed> $decoded_content  */
        $decoded_content = json_decode($content, true);

        $decoder->decode($decoded_content);

        return $this;
    }

}
