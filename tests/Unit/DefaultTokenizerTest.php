<?php

declare(strict_types=1);
// tests/DefaultTokenizerTest.php

use Sphamster\Support\Tokenizers\DefaultTokenizer;

beforeEach(function (): void {
    $this->tokenizer = new DefaultTokenizer();
});

it('tokenizes a simple sentence converting to lowercase', function (): void {
    $tokens = $this->tokenizer->tokenize("Hello World!");
    expect($tokens)->toEqual(['hello', 'world']);
});

it('returns an empty array when given an empty string', function (): void {
    $tokens = $this->tokenizer->tokenize("");
    expect($tokens)->toEqual([]);
});

it('extracts only alphabetical tokens and ignores numbers and punctuation', function (): void {
    $tokens = $this->tokenizer->tokenize("123 abc! def? ghi, jkl.");
    expect($tokens)->toEqual(['abc', 'def', 'ghi', 'jkl']);
});

it('converts all tokens to lowercase regardless of input case', function (): void {
    $tokens = $this->tokenizer->tokenize("HeLLo WorLD");
    expect($tokens)->toEqual(['hello', 'world']);
});
