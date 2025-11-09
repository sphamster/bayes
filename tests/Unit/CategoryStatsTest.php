<?php

declare(strict_types=1);

use Sphamster\Support\Classification\Category;
use Sphamster\Support\Statistics\CategoryStats;

it('returns category name correctly', function (): void {
    $category = new Category();
    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->name())->toBe('spam');
});

it('returns document count correctly', function (): void {
    $category = new Category();
    $category->docCount(42);

    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->docCount())->toBe(42);
});

it('returns word count correctly', function (): void {
    $category = new Category();
    $category->wordCount(250);

    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->wordCount())->toBe(250);
});

it('calculates percentage correctly', function (): void {
    $category = new Category();
    $category->docCount(30);

    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->percentage())->toBe(30.0);
});

it('calculates percentage with decimal precision', function (): void {
    $category = new Category();
    $category->docCount(33);

    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->percentage())->toBe(33.0);
});

it('calculates average document length correctly', function (): void {
    $category = new Category();
    $category->docCount(10);
    $category->wordCount(50);

    $stats = new CategoryStats('spam', $category, 100);

    expect($stats->averageDocLength())->toBe(5.0);
});

it('calculates average document length with decimals', function (): void {
    $category = new Category();
    $category->docCount(3);
    $category->wordCount(10);

    $stats = new CategoryStats('spam', $category, 100);

    $average = $stats->averageDocLength();
    expect($average)->toBeGreaterThan(3.32)
        ->and($average)->toBeLessThan(3.34);
});

it('returns top tokens sorted by frequency', function (): void {
    $category = new Category();
    $category->wordFrequencyCount([
        'amazing' => 10,
        'good' => 5,
        'awesome' => 15,
        'great' => 8,
    ]);

    $stats = new CategoryStats('positive', $category, 100);
    $top_tokens = $stats->topTokens(3);

    expect($top_tokens)->toBe([
        'awesome' => 15,
        'amazing' => 10,
        'great' => 8,
    ]);
});

it('returns all tokens when limit exceeds token count', function (): void {
    $category = new Category();
    $category->wordFrequencyCount([
        'good' => 5,
        'bad' => 3,
    ]);

    $stats = new CategoryStats('test', $category, 100);
    $top_tokens = $stats->topTokens(10);

    expect($top_tokens)->toBe([
        'good' => 5,
        'bad' => 3,
    ]);
});

it('returns empty array when no tokens exist', function (): void {
    $category = new Category();
    $category->wordFrequencyCount([]);

    $stats = new CategoryStats('empty', $category, 100);

    expect($stats->topTokens())->toBeEmpty();
});

it('returns unique token count correctly', function (): void {
    $category = new Category();
    $category->wordFrequencyCount([
        'token1' => 10,
        'token2' => 5,
        'token3' => 8,
    ]);

    $stats = new CategoryStats('test', $category, 100);

    expect($stats->uniqueTokenCount())->toBe(3);
});

it('returns zero unique token count for empty category', function (): void {
    $category = new Category();

    $stats = new CategoryStats('empty', $category, 100);

    expect($stats->uniqueTokenCount())->toBe(0);
});

it('handles empty category correctly', function (): void {
    $category = new Category();

    $stats = new CategoryStats('empty', $category, 100);

    expect($stats->docCount())->toBe(0)
        ->and($stats->wordCount())->toBe(0)
        ->and($stats->percentage())->toBe(0.0)
        ->and($stats->averageDocLength())->toBe(0.0)
        ->and($stats->topTokens())->toBeEmpty()
        ->and($stats->uniqueTokenCount())->toBe(0);
});

it('handles single document correctly', function (): void {
    $category = new Category();
    $category->docCount(1);
    $category->wordCount(5);
    $category->wordFrequencyCount(['word' => 5]);

    $stats = new CategoryStats('single', $category, 10);

    expect($stats->docCount())->toBe(1)
        ->and($stats->percentage())->toBe(10.0)
        ->and($stats->averageDocLength())->toBe(5.0);
});

it('handles zero total documents gracefully', function (): void {
    $category = new Category();
    $category->docCount(0);

    $stats = new CategoryStats('test', $category, 0);

    expect($stats->percentage())->toBe(0.0);
});

it('uses default limit of 10 for top tokens', function (): void {
    $category = new Category();
    $tokens = [];
    for ($i = 1; $i <= 15; $i++) {
        $tokens["token{$i}"] = $i;
    }
    $category->wordFrequencyCount($tokens);

    $stats = new CategoryStats('test', $category, 100);
    $top_tokens = $stats->topTokens();

    expect($top_tokens)->toHaveCount(10);
});
