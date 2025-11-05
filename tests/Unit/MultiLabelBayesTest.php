<?php

declare(strict_types=1);

use Sphamster\Exception\JsonCorruptedException;
use Sphamster\MultiLabelBayes;
use Sphamster\Support\Filters\AboveMeanFilter;
use Sphamster\Support\Filters\ThresholdFilter;
use Sphamster\Support\Filters\TopKFilter;
use Sphamster\Support\Probability;
use Sphamster\Support\Tokenizers\DefaultTokenizer;

it('trains with multiple labels and updates state correctly', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('AI helps medical research', ['technology', 'health']);

    $state = $classifier->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and($state['categories'])->toContain('technology')
        ->and($state['categories'])->toContain('health');

    // Both categories should have the same tokens
    $techCategory = $state['categoriesState']['technology'];
    $healthCategory = $state['categoriesState']['health'];

    expect($techCategory['docCount'])->toBe(1)
        ->and($healthCategory['docCount'])->toBe(1)
        ->and($techCategory['wordFrequencyCount'])->toHaveKey('ai')
        ->and($healthCategory['wordFrequencyCount'])->toHaveKey('ai');
});

it('increments totalDocuments only once per sample', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('Sample one', ['label1', 'label2', 'label3']);
    $classifier->train('Sample two', ['label1']);

    $state = $classifier->getState();

    // Should be 2 (one per sample), not 4 (not one per label)
    expect($state['totalDocuments'])->toBe(2);
});

it('trains on dataset with multiple labels per sample', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $data = [
        [
            'sample' => 'Tech stocks rise',
            'labels' => ['technology', 'business'],
        ],
        [
            'sample' => 'Medical breakthrough',
            'labels' => ['health', 'science'],
        ],
    ];

    $classifier->trainOn($data);

    $state = $classifier->getState();

    expect($state['totalDocuments'])->toBe(2)
        ->and($state['categories'])->toContain('technology')
        ->and($state['categories'])->toContain('business')
        ->and($state['categories'])->toContain('health')
        ->and($state['categories'])->toContain('science');
});

it('predicts with threshold filter', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('tech stocks rise', ['technology', 'business']);
    $classifier->train('medical breakthrough', ['health', 'science']);

    $predictions = $classifier->predict(
        'tech company health product',
        new ThresholdFilter(0.1)
    );

    expect($predictions)->toBeArray();
    foreach ($predictions as $prediction) {
        expect($prediction)->toBeInstanceOf(Probability::class);
    }
});

it('predicts with top-k filter', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('tech news', ['technology']);
    $classifier->train('health news', ['health']);
    $classifier->train('business news', ['business']);

    $predictions = $classifier->predict(
        'news article',
        new TopKFilter(2)
    );

    expect($predictions)->toBeArray()
        ->and(count($predictions))->toBeLessThanOrEqual(2);
});

it('predicts with above-mean filter', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('amazing tech product', ['technology', 'positive']);
    $classifier->train('terrible business decision', ['business', 'negative']);

    $predictions = $classifier->predict(
        'amazing business innovation',
        new AboveMeanFilter()
    );

    expect($predictions)->toBeArray();
    foreach ($predictions as $prediction) {
        expect($prediction)->toBeInstanceOf(Probability::class);
    }
});

it('exports and imports state correctly', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('tech and business news', ['technology', 'business']);
    $classifier->train('health and science update', ['health', 'science']);

    $json = $classifier->export();

    $newClassifier = new MultiLabelBayes(new DefaultTokenizer());
    $newClassifier->import($json);

    expect($newClassifier->getState())->toEqual($classifier->getState());
});

it('resets state correctly', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('sample text', ['label1', 'label2']);
    $classifier->reset();

    $state = $classifier->getState();

    expect($state['totalDocuments'])->toBe(0)
        ->and($state['vocabulary'])->toBeEmpty()
        ->and($state['categoriesState'])->toBeEmpty();
});

it('handles empty labels array gracefully', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('sample text', []);

    $state = $classifier->getState();

    // totalDocuments should still increment
    expect($state['totalDocuments'])->toBe(1)
        ->and($state['categories'])->toBeEmpty();
});

it('handles single label like Bayes', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('positive sentiment', ['positive']);

    $state = $classifier->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and($state['categories'])->toContain('positive')
        ->and(count($state['categories']))->toBe(1);
});

it('shares vocabulary across all labels', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('tech business', ['technology', 'business']);

    $state = $classifier->getState();
    $vocabulary = $state['vocabulary'];

    // Vocabulary should contain tokens only once, not duplicated per label
    expect($vocabulary)->toContain('tech')
        ->and($vocabulary)->toContain('business')
        ->and(count($vocabulary))->toBe(2); // Not 4 (2 tokens * 2 labels)
});

it('calculates probabilities correctly for multi-label training', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('technology news', ['technology']);
    $classifier->train('technology update', ['technology']);
    $classifier->train('business news', ['business']);

    // 'technology' has seen 'news' once, 'business' has also seen 'news' once
    // But 'technology' should have higher probability because it has 2 docs

    $predictions = $classifier->predict(
        'technology',
        new TopKFilter(2)
    );

    expect($predictions)->toHaveCount(2)
        ->and($predictions[0]->category())->toBe('technology'); // Should be first (higher prior)
});

it('throws JsonCorruptedException on malformed JSON', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    expect(fn (): MultiLabelBayes => $classifier->import('{invalid json'))
        ->toThrow(JsonCorruptedException::class);
});

it('throws JsonCorruptedException when totalDocuments is missing during import', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $data = [
        'categories' => [],
        'vocabulary' => [],
        'vocabularySize' => 0,
        'categoriesState' => [],
    ];

    $json = json_encode($data);

    expect(fn (): MultiLabelBayes => $classifier->import($json))
        ->toThrow(JsonCorruptedException::class);
});

it('supports custom tokenizer', function (): void {
    $customTokenizer = new class () implements Sphamster\Contracts\Tokenizer {
        public function tokenize(string $text): array
        {
            return explode(' ', mb_strtolower($text));
        }
    };

    $classifier = new MultiLabelBayes($customTokenizer);

    $classifier->train('HELLO WORLD', ['greeting', 'english']);

    $state = $classifier->getState();

    expect($state['vocabulary'])->toContain('hello')
        ->and($state['vocabulary'])->toContain('world');
});

it('handles trainOn with custom keys', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $data = [
        [
            'text' => 'tech news',
            'categories' => ['technology', 'news'],
        ],
    ];

    $classifier->trainOn($data, sample_key: 'text', labels_key: 'categories');

    $state = $classifier->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and($state['categories'])->toContain('technology')
        ->and($state['categories'])->toContain('news');
});

it('normalizes probabilities to sum to 1.0', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('cat', ['animal']);
    $classifier->train('dog', ['animal']);
    $classifier->train('car', ['vehicle']);

    $probabilities = $classifier->probabilities('cat');

    expect($probabilities)->toBeNormalized();
});

it('normalizes probabilities with multi-label training', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('laptop computer', ['electronics', 'computers']);
    $classifier->train('phone mobile', ['electronics', 'mobile']);
    $classifier->train('tablet device', ['electronics', 'mobile']);

    $probabilities = $classifier->probabilities('laptop');

    expect($probabilities)->toBeNormalized();
});

it('maintains normalization with imbalanced training', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    for ($i = 0; $i < 5; $i++) {
        $classifier->train('good', ['positive']);
    }
    $classifier->train('bad', ['negative']);

    $probabilities = $classifier->probabilities('good');

    expect($probabilities)->toBeNormalized();
});

it('preserves normalized probabilities after export/import', function (): void {
    $classifier = new MultiLabelBayes(new DefaultTokenizer());

    $classifier->train('apple', ['fruit']);
    $classifier->train('banana', ['fruit']);
    $classifier->train('carrot', ['vegetable']);

    $json = $classifier->export();

    $restored = new MultiLabelBayes(new DefaultTokenizer());
    $restored->import($json);

    $probabilities = $restored->probabilities('apple');

    expect($probabilities)->toBeNormalized();
});
