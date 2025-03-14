<?php

declare(strict_types=1);


// tests/CategoryTest.php

use Sphamster\Support\Classification\Category;

beforeEach(function (): void {
    $this->category = new Category();
});

it('has an initial state', function (): void {
    expect($this->category->docCount())->toBe(0)
        ->and($this->category->getWordCount())->toBe(0)
        ->and($this->category->getWordFrequencyCount())->toEqual([]);
});

it('increments correctly docCount', function (): void {
    $this->category->incrementDocCount();
    expect($this->category->docCount())->toBe(1);

    $this->category->incrementDocCount();
    expect($this->category->docCount())->toBe(2);
});

it('assign correctly docCount', function (): void {
    $this->category->setDocCount(5);
    expect($this->category->docCount())->toBe(5);
});

it('adds correctly tokens frequency and updates wordCount', function (): void {
    $this->category->addWordFrequency('hello', 2);
    $this->category->addWordFrequency('world', 3);
    $this->category->addWordFrequency('hello', 1);

    expect($this->category->getWordFrequencyCount())->toEqual([
        'hello' => 3,
        'world' => 3,
    ])
        ->and($this->category->getWordCount())->toBe(6);
});

it('assign correctly wordCount and wordFrequencyCount', function (): void {
    $this->category->setWordCount(10);
    $this->category->setWordFrequencyCount(['foo' => 4, 'bar' => 6]);

    expect($this->category->getWordCount())->toBe(10)
        ->and($this->category->getWordFrequencyCount())->toEqual([
            'foo' => 4,
            'bar' => 6,
        ]);
});

it('resets correctly', function (): void {
    $this->category->incrementDocCount();
    $this->category->addWordFrequency('test', 5);

    expect($this->category->docCount())->toBeGreaterThan(0)
        ->and($this->category->getWordCount())->toBeGreaterThan(0);

    $this->category->reset();

    expect($this->category->docCount())->toBe(0)
        ->and($this->category->getWordCount())->toBe(0)
        ->and($this->category->getWordFrequencyCount())->toEqual([]);
});
