<?php

declare(strict_types=1);

namespace Sphamster\Support\Classification;

class State
{
    /**
     * @var array<string, Category>
     */
    protected array $categories = [];

    private int $total_documents = 0;

    public function category(string $category): Category
    {
        if ( ! isset($this->categories[$category])) {
            $this->categories[$category] = new Category();
        }

        return $this->categories[$category];
    }

    public function incrementTotalDocuments(): void
    {
        $this->total_documents++;
    }

    public function totalDocuments(): int
    {
        return $this->total_documents;
    }

    public function setTotalDocuments(int $total_documents): void
    {
        $this->total_documents = $total_documents;
    }

    /**
     * @return array<string, Category>
     *
     */
    public function categories(): array
    {
        return $this->categories;
    }

    public function reset(): void
    {
        $this->categories = [];
        $this->total_documents = 0;
    }
}
