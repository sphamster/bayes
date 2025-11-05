<?php

declare(strict_types=1);

use Sphamster\Support\Filters\TopKFilter;
use Sphamster\Support\Probability;

it('returns top k probabilities', function (): void {
    $filter = new TopKFilter(2);

    $probabilities = [
        new Probability('high', log(0.6)),
        new Probability('medium', log(0.3)),
        new Probability('low', log(0.1)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2)
        ->and($filtered[0]->category())->toBe('high')
        ->and($filtered[1]->category())->toBe('medium');
});

it('returns all when k exceeds array length', function (): void {
    $filter = new TopKFilter(10);

    $probabilities = [
        new Probability('first', log(0.5)),
        new Probability('second', log(0.3)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2)
        ->and($filtered[0]->category())->toBe('first')
        ->and($filtered[1]->category())->toBe('second');
});

it('handles k = 0', function (): void {
    $filter = new TopKFilter(0);

    $probabilities = [
        new Probability('any', log(0.5)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('handles k = 1', function (): void {
    $filter = new TopKFilter(1);

    $probabilities = [
        new Probability('high', log(0.8)),
        new Probability('low', log(0.2)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('high');
});

it('sorts by log probability descending', function (): void {
    $filter = new TopKFilter(3);

    // Intentionally unsorted
    $probabilities = [
        new Probability('medium', log(0.3)),
        new Probability('high', log(0.8)),
        new Probability('low', log(0.1)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(3)
        ->and($filtered[0]->category())->toBe('high')
        ->and($filtered[1]->category())->toBe('medium')
        ->and($filtered[2]->category())->toBe('low');
});

it('handles empty probability array', function (): void {
    $filter = new TopKFilter(5);

    $filtered = $filter->filter([]);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('uses default k of 3 when not specified', function (): void {
    $filter = new TopKFilter();

    $probabilities = [
        new Probability('first', log(0.5)),
        new Probability('second', log(0.4)),
        new Probability('third', log(0.3)),
        new Probability('fourth', log(0.2)),
        new Probability('fifth', log(0.1)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(3)
        ->and($filtered[0]->category())->toBe('first')
        ->and($filtered[1]->category())->toBe('second')
        ->and($filtered[2]->category())->toBe('third');
});

it('handles tie in probabilities', function (): void {
    $filter = new TopKFilter(2);

    $probabilities = [
        new Probability('first', log(0.5)),
        new Probability('second', log(0.5)),  // Same probability
        new Probability('third', log(0.3)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2);
    // Both 'first' and 'second' have same probability, either order is acceptable
    $categories = [$filtered[0]->category(), $filtered[1]->category()];
    expect($categories)->toContain('first')
        ->and($categories)->toContain('second');
});
