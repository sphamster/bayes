<?php

declare(strict_types=1);

namespace Sphamster\Support\Tokenizers;

use Sphamster\Contracts\Tokenizer;

class DefaultTokenizer implements Tokenizer
{
    public function tokenize(string $text): array
    {
        $text = mb_strtolower($text);

        preg_match_all('/[[:alpha:]]+/u', $text, $matches);

        return $matches[0];
    }
}
