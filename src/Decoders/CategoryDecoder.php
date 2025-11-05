<?php

declare(strict_types=1);

namespace Sphamster\Decoders;

use Sphamster\Concerns\FromBayes;
use Sphamster\Contracts\Decoder;

class CategoryDecoder implements Decoder
{
    use FromBayes;

    /**
     * @param array<string, mixed> $data
     */
    public function decode(array $data): void
    {
        if (isset($data['categoriesState']) && is_array($data['categoriesState'])) {
            foreach ($data['categoriesState'] as $categoryKey => $categoryData) {
                if ( ! is_string($categoryKey)) {
                    continue;
                }
                if ( ! is_array($categoryData)) {
                    continue;
                }
                $category = $this->classifier->state()->category($categoryKey);

                $category->docCount($this->extractDocCount($categoryData));
                $category->wordCount($this->extractWordCount($categoryData));
                /** @phpstan-ignore-next-line argument.type */
                $category->wordFrequencyCount($this->extractFrequencyCount($categoryData));
            }
        }
    }

    /** @phpstan-ignore-next-line missingType.iterableValue */
    protected function extractDocCount(array $category): int
    {
        return (isset($category['docCount']) && is_numeric($category['docCount']))
            ? (int)$category['docCount']
            : 0;
    }

    /** @phpstan-ignore-next-line missingType.iterableValue */
    protected function extractWordCount(array $category): int
    {
        return (isset($category['wordCount']) && is_numeric($category['wordCount']))
            ? (int)$category['wordCount']
            : 0;
    }

    /**
     * @param array<string, mixed> $category
     * @return array<string, int>
     */
    protected function extractFrequencyCount(array $category): array
    {
        $result = [];
        if (isset($category['wordFrequencyCount']) && is_array($category['wordFrequencyCount'])) {
            foreach ($category['wordFrequencyCount'] as $key => $value) {
                if (is_numeric($value)) {
                    $result[(string)$key] = (int)$value;
                }
            }
        }
        return $result;
    }
}
