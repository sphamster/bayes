<?php

declare(strict_types=1);
// tests/FrequencyTableTest.php

use Sphamster\Support\FrequencyTables\FrequencyTable;

beforeEach(function (): void {
    $this->frequencyTable = new FrequencyTable();
});

it('starts with an empty frequency table', function (): void {
    expect($this->frequencyTable->frequencies())->toEqual([])
        ->and($this->frequencyTable->totalCount())->toBe(0);
});

it('adds a token correctly with default count', function (): void {
    $this->frequencyTable->add('hello');
    expect($this->frequencyTable->frequency('hello'))->toBe(1);

    // Adding the same token again
    $this->frequencyTable->add('hello');
    expect($this->frequencyTable->frequency('hello'))->toBe(2);
});

it('adds a token with a specified count', function (): void {
    $this->frequencyTable->add('world', 3);
    expect($this->frequencyTable->frequency('world'))->toBe(3);
});

it('adds multiple tokens via addTokens()', function (): void {
    $tokens = ['foo', 'bar', 'foo', 'baz'];
    $this->frequencyTable->addTokens($tokens);

    expect($this->frequencyTable->frequencies())->toEqual([
        'foo' => 2,
        'bar' => 1,
        'baz' => 1,
    ]);
});

it('calculates the total count correctly', function (): void {
    $this->frequencyTable->add('alpha', 2);
    $this->frequencyTable->add('beta', 3);
    $this->frequencyTable->add('gamma'); // default count 1

    expect($this->frequencyTable->totalCount())->toBe(6);
});
