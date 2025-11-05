<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

interface Tokenizer
{
    /**
     * Tokenizes input text into an array of string tokens.
     *
     * @param string $text The text to tokenize
     * @return array<int, string> The array of tokens
     */
    public function tokenize(string $text): array;
}
