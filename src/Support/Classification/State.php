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

    /**
     * Gets or creates a category by name.
     *
     * If the category doesn't exist, it will be created automatically.
     *
     * @param string $category The category name
     * @return Category The category instance
     */
    public function category(string $category): Category
    {
        if ( ! isset($this->categories[$category])) {
            $this->categories[$category] = new Category();
        }

        return $this->categories[$category];
    }

    /**
     * Increments the total number of documents trained by one.
     */
    public function incrementTotalDocuments(): void
    {
        $this->total_documents++;
    }

    /**
     * Gets or sets the total number of documents trained across all categories.
     *
     * When called without parameters, returns the current total document count.
     * When called with a parameter, sets the total document count and returns $this for chaining.
     *
     * @param int|null $total_documents The total document count to set (optional)
     * @return int|static The total document count when getting, or $this when setting
     */
    public function totalDocuments(?int $total_documents = null): int|static
    {
        if ($total_documents !== null) {
            $this->total_documents = $total_documents;
            return $this;
        }
        return $this->total_documents;
    }

    /**
     * Returns all categories in this state.
     *
     * @return array<string, Category> Map of category names to Category objects
     */
    public function categories(): array
    {
        return $this->categories;
    }

    /**
     * Resets the state to its initial condition.
     *
     * Clears all categories and resets the total document count to zero.
     */
    public function reset(): void
    {
        $this->categories = [];
        $this->total_documents = 0;
    }
}
