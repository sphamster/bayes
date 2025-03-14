<?php

declare(strict_types=1);

namespace Sphamster\Contracts;

interface Tokenizer
{
    /**
     * @return array<int, string> L'array dei token.
     */
    public function tokenize(string $text): array;
}
