<?php

declare(strict_types=1);


use Sphamster\Bayes;
use Sphamster\Exception\JsonCorruptedException;
use Sphamster\Support\Probability;
use Sphamster\Support\Tokenizers\DefaultTokenizer;

it('trains and updates state correctly', function (): void {
    $bayes = new Bayes(new DefaultTokenizer());

    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $state = $bayes->getState();

    expect($state['totalDocuments'])->toBe(1)
        ->and(in_array("positive", $state['categories']))->toBeTrue();



    // I token presenti nel sample sono: ciao, mi, chiamo, pippo (ognuno 1 volta)
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
    $bayes = new Bayes(new DefaultTokenizer());

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



    // I token presenti nel sample sono: ciao, mi, chiamo, pippo (ognuno 1 volta)
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
    $bayes = new Bayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $bayes->train('Sweet, this is incredibly, amazing, perfect, great!!', 'positive');
    $bayes->train('terrible, shitty thing. Damn. Sucks!!', 'negative');

    // Testa la previsione per un sample tipico della categoria "saluto"
    $prediction = $bayes->predict('awesome, cool, amazing!! Yay.');
    expect($prediction)->toBe('positive');
});

it('returns probabilities as Probability objects', function (): void {
    $bayes = new Bayes(new DefaultTokenizer());
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
    $bayes = new Bayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');

    $json = $bayes->export();
    $newBayes = new Bayes(new DefaultTokenizer());
    $newBayes->import($json);

    expect($newBayes->getState())->toEqual($bayes->getState());
});

it('resets state correctly', function (): void {
    $bayes = new Bayes(new DefaultTokenizer());
    $bayes->train('amazing, awesome movie!! Yeah!! Oh boy.', 'positive');
    $bayes->reset();
    $state = $bayes->getState();

    expect($state['totalDocuments'])->toBe(0)
        ->and($state['vocabulary'])->toBeEmpty()
        ->and($state['categoriesState'])->toBeEmpty();
});

it('throws JsonCorruptedException on malformed JSON', function (): void {
    $bayes = new Bayes(new DefaultTokenizer());

    expect(fn (): Bayes => $bayes->import('{invalid json'))
        ->toThrow(JsonCorruptedException::class);
});
