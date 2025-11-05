<?php

declare(strict_types=1);

use Sphamster\Support\Filters\ThresholdFilter;
use Sphamster\Support\Probability;

it('filters probabilities above threshold', function (): void {
    $filter = new ThresholdFilter(0.3);

    $probabilities = [
        new Probability('high', log(0.5)),      // 50% - above threshold
        new Probability('medium', log(0.35)),   // 35% - above threshold
        new Probability('low', log(0.15)),      // 15% - below threshold
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(2)
        ->and($filtered[0]->category())->toBe('high')
        ->and($filtered[1]->category())->toBe('medium');
});

it('returns empty array when no probabilities meet threshold', function (): void {
    $filter = new ThresholdFilter(0.8);

    $probabilities = [
        new Probability('low1', log(0.1)),
        new Probability('low2', log(0.2)),
        new Probability('low3', log(0.3)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('works with threshold of 0.0', function (): void {
    $filter = new ThresholdFilter(0.0);

    $probabilities = [
        new Probability('any', log(0.001)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('any');
});

it('works with threshold of 1.0', function (): void {
    $filter = new ThresholdFilter(1.0);

    $probabilities = [
        new Probability('high', log(0.99)),
        new Probability('perfect', log(1.0)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('perfect');
});

it('handles empty probability array', function (): void {
    $filter = new ThresholdFilter(0.5);

    $filtered = $filter->filter([]);

    expect($filtered)->toBeArray()
        ->and($filtered)->toBeEmpty();
});

it('uses default threshold of 0.3 when not specified', function (): void {
    $filter = new ThresholdFilter();

    $probabilities = [
        new Probability('above', log(0.4)),
        new Probability('below', log(0.2)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('above');
});

it('handles probabilities exactly at threshold', function (): void {
    $filter = new ThresholdFilter(0.3);

    $probabilities = [
        new Probability('exact', log(0.3)),
        new Probability('below', log(0.29)),
    ];

    $filtered = $filter->filter($probabilities);

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]->category())->toBe('exact');
});
