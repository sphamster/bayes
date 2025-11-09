<?php

declare(strict_types=1);


use Sphamster\SingleLabelBayes;
use Sphamster\Exception\JsonCorruptedException;
use Sphamster\Support\Probability;
use Sphamster\Support\Tokenizers\DefaultTokenizer;

it('trains and updates state correctly', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $state = $bayes->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and(in_array("positive", $state['categories']))->toBeTrue();

    // Tokens in the sample: amazing, awesome, movie, yeah, oh, boy (each appears once)
    $category = $state['categoriesState']['positive'];
    expect($category['docCount'])->toBe(1)
        ->and($category['wordFrequencyCount'])->toEqual([
            'amazing' => 1,
            'awesome' => 1,
            'movie' => 1,
            'yeah' => 1,
            'oh' => 1,
            'boy' => 1,
        ]);
});

it('trains on dataset and updates state correctly', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $reviews = [
        [
            'sample' => 'amazing, awesome movie!! Yeah!! Oh boy.',
            'label' => 'positive',
        ],
    ];
    $bayes->trainOn(data: $reviews);
    $state = $bayes->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and(in_array("positive", $state['categories']))->toBeTrue();

    // Tokens in the sample: amazing, awesome, movie, yeah, oh, boy (each appears once)
    $category = $state['categoriesState']['positive'];
    expect($category['docCount'])->toBe(1)
        ->and($category['wordFrequencyCount'])->toEqual([
            'amazing' => 1,
            'awesome' => 1,
            'movie' => 1,
            'yeah' => 1,
            'oh' => 1,
            'boy' => 1,
        ]);
});

it('predicts category correctly', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $bayes->train('Sweet, this is incredibly, amazing, perfect, great!!', 'positive');
    $bayes->train('terrible, shitty thing. Damn. Sucks!!', 'negative');

    // Test prediction for a typical positive category sample
    $prediction = $bayes->predict('awesome, cool, amazing!! Yay.');
    expect($prediction)->toBe('positive');
});

it('returns probabilities as Probability objects', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');

    $probabilities = $bayes->probabilities('awesome, cool, amazing!! Yay.');

    expect($probabilities)->toBeArray();
    foreach ($probabilities as $prob) {
        expect($prob)->toBeInstanceOf(Probability::class);
    }
});

it(/**
 * @throws JsonCorruptedException
 * @throws JsonException
 */ 'serializes to JSON and deserializes correctly', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');

    $json = $bayes->export();
    $newBayes = new SingleLabelBayes(new DefaultTokenizer());
    $newBayes->import($json);

    expect($newBayes->getState())->toEqual($bayes->getState());
});

it('resets state correctly', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $bayes->reset();
    $state = $bayes->getState();

    expect($state['totalDocuments'])->toBe(0)
        ->and($state['vocabulary'])->toBeEmpty()
        ->and($state['categoriesState'])->toBeEmpty();
});

it('throws JsonCorruptedException on malformed JSON', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    expect(fn (): SingleLabelBayes => $bayes->import('{invalid json'))
        ->toThrow(JsonCorruptedException::class);
});

it('throws JsonCorruptedException when totalDocuments is missing during import', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $data = [
        'categories' => [],
        'vocabulary' => [],
        'vocabularySize' => 0,
        'categoriesState' => [],
    ];

    $json = json_encode($data);

    expect(fn (): SingleLabelBayes => $bayes->import($json))
        ->toThrow(JsonCorruptedException::class);
});

it('throws JsonCorruptedException when vocabulary is missing during import', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $data = [
        'totalDocuments' => [],
        'categories' => [],
        'categoriesState' => [],
    ];

    $json = json_encode($data);

    expect(fn (): SingleLabelBayes => $bayes->import($json))
        ->toThrow(JsonCorruptedException::class);
});

it('throws JsonCorruptedException when categories is missing during import', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $data = [
        'totalDocuments' => [],
        'vocabulary' => [],
    ];

    $json = json_encode($data);

    expect(fn (): SingleLabelBayes => $bayes->import($json))
        ->toThrow(JsonCorruptedException::class);
});

it('normalizes probabilities to sum to 1.0', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('cat', 'animal');
    $bayes->train('dog', 'animal');
    $bayes->train('car', 'vehicle');

    $probabilities = $bayes->probabilities('cat');

    expect($probabilities)->toBeNormalized();
});

it('normalizes probabilities with multiple categories', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('apple', 'fruit');
    $bayes->train('banana', 'fruit');
    $bayes->train('carrot', 'vegetable');
    $bayes->train('lettuce', 'vegetable');
    $bayes->train('chicken', 'meat');
    $bayes->train('beef', 'meat');

    $probabilities = $bayes->probabilities('apple');

    expect($probabilities)->toBeNormalized();
});

it('maintains normalization after training', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    for ($i = 0; $i < 5; $i++) {
        $bayes->train('good', 'positive');
    }
    $bayes->train('bad', 'negative');

    $probabilities = $bayes->probabilities('good');

    expect($probabilities)->toBeNormalized();
});

it('handles empty training data array', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());
    $bayes->trainOn([]);

    expect($bayes->state()->totalDocuments())->toBe(0)
        ->and($bayes->state()->categories())->toBeEmpty();
});

it('returns tokenizer instance', function (): void {
    $tokenizer = new DefaultTokenizer();
    $bayes = new SingleLabelBayes($tokenizer);

    expect($bayes->tokenizer())->toBe($tokenizer);
});

it('returns empty array when calculating probabilities on untrained classifier', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $probabilities = $bayes->probabilities('some text');

    expect($probabilities)->toBeEmpty();
});

it('returns null when predicting on untrained classifier', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $prediction = $bayes->predict('some text');

    expect($prediction)->toBeNull();
});

it('handles import with non-string category keys gracefully', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    // Create JSON with integer category key (malformed data)
    $data = [
        'totalDocuments' => 1,
        'categories' => ['positive'],
        'vocabulary' => ['good', 'bad'],
        'vocabularySize' => 2,
        'categoriesState' => [
            123 => [ // Integer key instead of string
                'docCount' => 1,
                'wordCount' => 2,
                'wordFrequencyCount' => ['good' => 1, 'bad' => 1]
            ]
        ]
    ];

    $json = json_encode($data);
    $bayes->import($json);

    // Should skip the malformed category and have empty state
    expect($bayes->state()->categories())->toBeEmpty();
});

it('handles import with non-array category data gracefully', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    // Create JSON with string category data (malformed data)
    $data = [
        'totalDocuments' => 1,
        'categories' => ['positive'],
        'vocabulary' => ['good', 'bad'],
        'vocabularySize' => 2,
        'categoriesState' => [
            'positive' => 'this should be an array' // String instead of array
        ]
    ];

    $json = json_encode($data);
    $bayes->import($json);

    // Should skip the malformed category data
    $category = $bayes->state()->category('positive');
    expect($category->docCount())->toBe(0);
});

it('handles import with non-string vocabulary tokens gracefully', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    // Create JSON with mixed vocabulary (some non-string tokens)
    $data = [
        'totalDocuments' => 1,
        'categories' => ['positive'],
        'vocabulary' => ['good', 123, null, 'bad'], // Mixed types
        'vocabularySize' => 4,
        'categoriesState' => [
            'positive' => [
                'docCount' => 1,
                'wordCount' => 2,
                'wordFrequencyCount' => ['good' => 1, 'bad' => 1]
            ]
        ]
    ];

    $json = json_encode($data);
    $bayes->import($json);

    // Should only import string tokens
    expect($bayes->vocabulary()->exists('good'))->toBeTrue()
        ->and($bayes->vocabulary()->exists('bad'))->toBeTrue()
        ->and($bayes->vocabulary()->size())->toBe(2); // Only 2 string tokens
});

it('throws JsonCorruptedException when vocabulary is not an array', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    // Create JSON with vocabulary as string instead of array
    $data = [
        'totalDocuments' => 1,
        'categories' => ['positive'],
        'vocabulary' => 'this should be an array', // String instead of array
        'vocabularySize' => 0,
        'categoriesState' => []
    ];

    $json = json_encode($data);

    expect(fn (): SingleLabelBayes => $bayes->import($json))
        ->toThrow(JsonCorruptedException::class);
});

it('returns empty array when state has documents but no categories', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    // Create a state with totalDocuments > 0 but no categories (edge case)
    $data = [
        'totalDocuments' => 1,
        'categories' => [],
        'vocabulary' => ['good', 'bad'],
        'vocabularySize' => 2,
        'categoriesState' => []
    ];

    $json = json_encode($data);
    $bayes->import($json);

    // Should return empty array since there are no categories
    $probabilities = $bayes->probabilities('some text');
    expect($probabilities)->toBeEmpty();
});

it('returns training statistics', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('amazing awesome movie', 'positive');
    $bayes->train('great film', 'positive');
    $bayes->train('terrible bad', 'negative');

    $stats = $bayes->getTrainingStats();

    expect($stats)->toBeInstanceOf(Sphamster\Support\Statistics\TrainingStats::class)
        ->and($stats->totalDocuments())->toBe(3)
        ->and($stats->numCategories())->toBe(2)
        ->and($stats->vocabularySize())->toBeGreaterThan(0);
});

it('returns top tokens for specific category', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('buy buy buy now free', 'spam');
    $bayes->train('buy now', 'spam');

    $top_tokens = $bayes->getTopTokens('spam', 2);

    expect($top_tokens)->toBeArray()
        ->and($top_tokens)->toHaveKey('buy')
        ->and($top_tokens['buy'])->toBe(4); // 'buy' appears 4 times total
});

it('returns most common tokens across all categories', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('good good good', 'positive');
    $bayes->train('bad bad', 'negative');
    $bayes->train('good', 'positive');

    $common_tokens = $bayes->getMostCommonTokens(2);

    expect($common_tokens)->toBeArray()
        ->and($common_tokens)->toHaveKey('good')
        ->and($common_tokens['good'])->toBe(4); // 'good' appears 4 times total
});

it('returns empty training statistics for untrained classifier', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $stats = $bayes->getTrainingStats();

    expect($stats->totalDocuments())->toBe(0)
        ->and($stats->numCategories())->toBe(0)
        ->and($stats->vocabularySize())->toBe(0);
});

it('returns empty array for top tokens on non-existent category', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $bayes->train('hello world', 'greeting');

    $top_tokens = $bayes->getTopTokens('nonexistent');

    expect($top_tokens)->toBeEmpty();
});

it('returns empty array for most common tokens on untrained classifier', function (): void {
    $bayes = new SingleLabelBayes(new DefaultTokenizer());

    $common_tokens = $bayes->getMostCommonTokens();

    expect($common_tokens)->toBeEmpty();
});
