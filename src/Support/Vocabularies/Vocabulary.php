<?php

declare(strict_types=1);

namespace Sphamster\Support\Vocabularies;

class Vocabulary
{
    /**
     * @var array<string, bool>
     */
    private array $tokens = [];

    public function add(string $token): void
    {
        if ( ! $this->exists($token)) {
            $this->tokens[$token] = true;
        }
    }

    public function exists(string $token): bool
    {
        return isset($this->tokens[$token]);
    }

    public function size(): int
    {
        return count($this->tokens);
    }

    /**
     * @return array<int, string>
     */
    public function tokens(): array
    {
        return array_keys($this->tokens);
    }

    public function reset(): void
    {
        $this->tokens = [];
    }
}
