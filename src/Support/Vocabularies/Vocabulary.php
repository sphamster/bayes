<?php

declare(strict_types=1);

namespace Sphamster\Support\Vocabularies;

class Vocabulary
{
    /**
     * @var array<string, bool>
     */
    private array $tokens = [];

    /**
     * Adds a token to the vocabulary.
     *
     * If the token already exists, no action is taken (idempotent operation).
     *
     * @param string $token The token to add
     */
    public function add(string $token): void
    {
        $this->tokens[$token] = true;
    }

    /**
     * Checks if a token exists in the vocabulary.
     *
     * @param string $token The token to check
     * @return bool True if the token exists, false otherwise
     */
    public function exists(string $token): bool
    {
        return isset($this->tokens[$token]);
    }

    /**
     * Returns the number of unique tokens in the vocabulary.
     *
     * @return int The vocabulary size
     */
    public function size(): int
    {
        return count($this->tokens);
    }

    /**
     * Returns all tokens in the vocabulary as an array.
     *
     * @return array<int, string> Array of token strings
     */
    public function tokens(): array
    {
        return array_keys($this->tokens);
    }

    /**
     * Resets the vocabulary to its initial empty state.
     *
     * Removes all tokens from the vocabulary.
     */
    public function reset(): void
    {
        $this->tokens = [];
    }
}
