<?php

declare(strict_types=1);
// tests/ClassificationStateTest.php

use Sphamster\Support\Classification\Category;
use Sphamster\Support\Classification\State;

beforeEach(function (): void {
    $this->state = new State();
});

it('creates a new category if not exists', function (): void {
    $cat = $this->state->category('test');
    expect($cat)->toBeInstanceOf(Category::class);
});

it('returns the same category instance when requested multiple times', function (): void {
    $cat1 = $this->state->category('test');
    $cat2 = $this->state->category('test');
    expect($cat1)->toBe($cat2);
});

it('increments total documents correctly', function (): void {
    expect($this->state->totalDocuments())->toBe(0);
    $this->state->incrementTotalDocuments();
    expect($this->state->totalDocuments())->toBe(1);
    $this->state->incrementTotalDocuments();
    expect($this->state->totalDocuments())->toBe(2);
});

it('sets total documents correctly', function (): void {
    $this->state->totalDocuments(10);
    expect($this->state->totalDocuments())->toBe(10);
});

it('returns all categories', function (): void {
    $this->state->category('cat1');
    $this->state->category('cat2');
    $categories = $this->state->categories();
    expect($categories)->toHaveCount(2)
        ->and(array_keys($categories))->toEqual(['cat1', 'cat2']);
});

it('resets the state', function (): void {
    $this->state->category('cat1');
    $this->state->incrementTotalDocuments();
    $this->state->reset();
    expect($this->state->totalDocuments())->toBe(0)
        ->and($this->state->categories())->toBeEmpty();
});
