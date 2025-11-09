<?php

declare(strict_types=1);

use Sphamster\Support\Classification\State;
use Sphamster\Support\Statistics\CategoryStats;
use Sphamster\Support\Statistics\TrainingStats;
use Sphamster\Support\Vocabularies\Vocabulary;

it('returns total documents correctly', function (): void {
    $state = new State();
    $state->totalDocuments(100);
    $vocabulary = new Vocabulary();

    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->totalDocuments())->toBe(100);
});

it('returns vocabulary size correctly', function (): void {
    $state = new State();
    $vocabulary = new Vocabulary();
    $vocabulary->add('token1');
    $vocabulary->add('token2');
    $vocabulary->add('token3');

    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->vocabularySize())->toBe(3);
});

it('returns number of categories correctly', function (): void {
    $state = new State();
    $state->category('spam');
    $state->category('ham');
    $state->category('neutral');
    $vocabulary = new Vocabulary();

    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->numCategories())->toBe(3);
});

it('calculates class balance ratio correctly', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(60);

    $ham = $state->category('ham');
    $ham->docCount(20);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    // 60 / 20 = 3.0
    expect($stats->classBalanceRatio())->toBe(3.0);
});

it('detects balanced classes correctly', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(55);

    $ham = $state->category('ham');
    $ham->docCount(45);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    // 55 / 45 = 1.22... which is <= 2.0 (default threshold)
    expect($stats->isBalanced())->toBeTrue();
});

it('detects imbalanced classes correctly', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(80);

    $ham = $state->category('ham');
    $ham->docCount(20);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    // 80 / 20 = 4.0 which is > 2.0 (default threshold)
    expect($stats->isBalanced())->toBeFalse();
});

it('accepts custom threshold for balance detection', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(75);

    $ham = $state->category('ham');
    $ham->docCount(25);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    // 75 / 25 = 3.0
    expect($stats->isBalanced(2.0))->toBeFalse()
        ->and($stats->isBalanced(3.0))->toBeTrue()
        ->and($stats->isBalanced(5.0))->toBeTrue();
});

it('aggregates most common tokens across all categories', function (): void {
    $state = new State();
    $state->totalDocuments(10);

    $spam = $state->category('spam');
    $spam->wordFrequencyCount([
        'buy' => 10,
        'now' => 5,
        'free' => 8,
    ]);

    $ham = $state->category('ham');
    $ham->wordFrequencyCount([
        'hello' => 3,
        'meeting' => 7,
        'buy' => 2,  // Overlaps with spam
    ]);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    $common_tokens = $stats->mostCommonTokens(3);

    // buy: 10+2=12, free: 8, meeting: 7
    expect($common_tokens)->toBe([
        'buy' => 12,
        'free' => 8,
        'meeting' => 7,
    ]);
});

it('returns most common tokens with default limit of 10', function (): void {
    $state = new State();
    $state->totalDocuments(10);

    $category = $state->category('test');
    $tokens = [];
    for ($i = 1; $i <= 15; $i++) {
        $tokens["token{$i}"] = 16 - $i; // Descending frequencies
    }
    $category->wordFrequencyCount($tokens);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    $common_tokens = $stats->mostCommonTokens();

    expect($common_tokens)->toHaveCount(10)
        ->and(array_keys($common_tokens))->toContain('token1')
        ->and(array_keys($common_tokens))->not->toContain('token11');
});

it('returns empty array for most common tokens when no categories exist', function (): void {
    $state = new State();
    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->mostCommonTokens())->toBeEmpty();
});

it('returns category stats for specific category', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(30);
    $spam->wordCount(150);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    $category_stats = $stats->categoryStats('spam');

    expect($category_stats)->toBeInstanceOf(CategoryStats::class)
        ->and($category_stats->name())->toBe('spam')
        ->and($category_stats->docCount())->toBe(30)
        ->and($category_stats->percentage())->toBe(30.0);
});

it('returns all category stats', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $state->category('spam')->docCount(40);
    $state->category('ham')->docCount(35);
    $state->category('neutral')->docCount(25);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    $all_stats = $stats->allCategoryStats();

    expect($all_stats)->toHaveCount(3)
        ->and($all_stats[0])->toBeInstanceOf(CategoryStats::class)
        ->and($all_stats[1])->toBeInstanceOf(CategoryStats::class)
        ->and($all_stats[2])->toBeInstanceOf(CategoryStats::class);

    $names = array_map(fn ($s): string => $s->name(), $all_stats);
    expect($names)->toContain('spam', 'ham', 'neutral');
});

it('generates human-readable text report', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $spam = $state->category('spam');
    $spam->docCount(60);
    $spam->wordCount(300);
    $spam->wordFrequencyCount(['buy' => 50, 'free' => 30]);

    $ham = $state->category('ham');
    $ham->docCount(40);
    $ham->wordCount(200);
    $ham->wordFrequencyCount(['hello' => 40, 'meeting' => 20]);

    $vocabulary = new Vocabulary();
    $vocabulary->add('buy');
    $vocabulary->add('free');
    $vocabulary->add('hello');
    $vocabulary->add('meeting');

    $stats = new TrainingStats($state, $vocabulary);
    $report = $stats->toText();

    expect($report)->toBeString()
        ->and($report)->toContain('Training Statistics')
        ->and($report)->toContain('Total Documents: 100')
        ->and($report)->toContain('Vocabulary Size: 4')
        ->and($report)->toContain('Number of Categories: 2')
        ->and($report)->toContain('spam')
        ->and($report)->toContain('ham')
        ->and($report)->toContain('60.0%')
        ->and($report)->toContain('40.0%');
});

it('handles empty state correctly', function (): void {
    $state = new State();
    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->totalDocuments())->toBe(0)
        ->and($stats->vocabularySize())->toBe(0)
        ->and($stats->numCategories())->toBe(0)
        ->and($stats->classBalanceRatio())->toBe(0.0)
        ->and($stats->isBalanced())->toBeTrue()
        ->and($stats->mostCommonTokens())->toBeEmpty()
        ->and($stats->allCategoryStats())->toBeEmpty();
});

it('handles single category correctly', function (): void {
    $state = new State();
    $state->totalDocuments(50);

    $spam = $state->category('spam');
    $spam->docCount(50);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->numCategories())->toBe(1)
        ->and($stats->classBalanceRatio())->toBe(1.0)
        ->and($stats->isBalanced())->toBeTrue();
});

it('handles perfectly balanced classes', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $state->category('spam')->docCount(50);
    $state->category('ham')->docCount(50);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->classBalanceRatio())->toBe(1.0)
        ->and($stats->isBalanced())->toBeTrue();
});

it('handles highly imbalanced classes', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $state->category('spam')->docCount(95);
    $state->category('ham')->docCount(5);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    expect($stats->classBalanceRatio())->toBe(19.0)
        ->and($stats->isBalanced())->toBeFalse();
});

it('handles category with zero documents when calculating balance ratio', function (): void {
    $state = new State();
    $state->totalDocuments(100);

    $state->category('spam')->docCount(100);
    $state->category('ham')->docCount(0);

    $vocabulary = new Vocabulary();
    $stats = new TrainingStats($state, $vocabulary);

    // When min is 0, should return infinity or max value
    expect($stats->classBalanceRatio())->toBeInfinite();
});
