<?php

declare(strict_types=1);

use Sphamster\Support\Filters\AboveMeanFilter;
use Sphamster\Support\Probability;

it('filters probabilities above mean', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('high', log(0.6)),    // Above mean
        new Probability('medium', log(0.3)),  // At mean
        new Probability('low', log(0.1)),     // Below mean
    ];

    // Mean of log probabilities: (log(0.6) + log(0.3) + log(0.1)) / 3 ≈ -1.897
    // log(0.6) ≈ -0.511 (above mean)
    // log(0.3) ≈ -1.204 (above mean)
    // log(0.1) ≈ -2.303 (below mean)

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2)
        ->and($filtered[0]->category())->toBe('high')
        ->and($filtered[1]->category())->toBe('medium');
});

it('handles empty probability array', function (): void {
    $filter = new AboveMeanFilter();

    $filtered = $filter->filter([]);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('works when all probabilities equal mean', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('same1', log(0.5)),
        new Probability('same2', log(0.5)),
        new Probability('same3', log(0.5)),
    ];

    // All have same log probability, none are strictly above mean
    $filtered = $filter->filter($probabilities);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('works when one probability much higher than others', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('very_high', log(0.9)),
        new Probability('low1', log(0.1)),
        new Probability('low2', log(0.1)),
        new Probability('low3', log(0.1)),
    ];

    // Mean will be pulled down by three low probabilities
    // Only 'very_high' should be above mean
    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('very_high');
});

it('handles single probability', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('only', log(0.5)),
    ];

    // Single probability equals its own mean, not above it
    $filtered = $filter->filter($probabilities);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('filters correctly with two probabilities', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('high', log(0.8)),
        new Probability('low', log(0.2)),
    ];

    // Mean of log(0.8) and log(0.2)
    // Only 'high' should be above mean
    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('high');
});

it('preserves order of probabilities above mean', function (): void {
    $filter = new AboveMeanFilter();

    $probabilities = [
        new Probability('first', log(0.7)),
        new Probability('second', log(0.6)),
        new Probability('third', log(0.1)),
    ];

    // Mean: (log(0.7) + log(0.6) + log(0.1)) / 3
    // Both 'first' and 'second' should be above mean, in original order

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2)
        ->and($filtered[0]->category())->toBe('first')
        ->and($filtered[1]->category())->toBe('second');
});
